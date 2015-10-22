<?php

namespace Bargency\Forms\Controls;

use Bargency\Forms\Controls\MultiUpload\MFUModel;
use Bargency\Forms\Controls\MultiUpload\Plupload;
use Bargency\Forms\Controls\MultiUpload\MFUQueue;
use Latte\Engine;
use Latte\Macros\CoreMacros;
use Nette\Application\UI\Form;
use Nette\Application\UI\ITemplateFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Container;
use Nette\Forms\Controls\UploadControl;
use Nette\Forms\IControl;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;
use Nette\Http\FileUpload;
use Nette\Application\UI\ITemplate;
use Nette\NotSupportedException;
use Nette\InvalidStateException;

/**
 * MultiUpload
 *
 * @author Martin Adámek <adamek@bargency.com>
 */
class MultiUpload extends UploadControl
{

	/** @var string */
	const TEMP_DIR = 'multi-upload';

	/** @vat int minimal upload session lifetime */
	const MIN_LIFETIME = 3600; // 1 hour

	/** @var Plupload */
	protected static $interface;

	/** @var ITranslator */
	protected static $translator;

	/** @var ITranslator */
	protected static $request;

	/** @var MFUModel */
	protected static $model;

	/** @var ITemplateFactory */
	private static $templateFactory;

	/** @var string */
	public $token;

	/** @var int */
	public $maxFiles;

	/** @var int */
	public $maxFileSize;

	/** @var bool */
	public $useDefaultTemplate = TRUE;

	/**
	 * @param Container $container
	 */
	public static function register(Container $container)
	{
		$tempPath = $container->parameters['tempDir'] . '/' . self::TEMP_DIR;
		FileSystem::createDir($tempPath);

		$application = $container->getService('application');
		self::$request = $request = $container->getService('httpRequest');
		if ($container->getByType(ITranslator::class, FALSE)) {
			self::$translator = $container->getByType(ITranslator::class);
		}
		self::$model = $model = $container->getService('mfuModel');
		self::$interface = $interface = new MultiUpload\Plupload($request, $model, $tempPath);
		$maxInputTime = (int) ini_get('max_input_time') + 5;
		$lifeTime = max(self::MIN_LIFETIME, $maxInputTime);
		$model->setLifeTime($lifeTime);

		$application->onStartup[] = function() use ($interface) {
			$interface->handleUploads();
		};
		$application->onShutdown[] = function() use ($model, $container) {
			if (!$container->parameters['productionMode'] || rand(1, 100) < 5) {
				$model->cleanup();
			}
		};

		self::$templateFactory = $container->getByType(ITemplateFactory::class);
	}

	/**
	 * Constructor
	 * @param string $label Label
	 */
	public function __construct($label = NULL, $maxFiles = 999)
	{
		parent::__construct($label);

		$this->monitor('Nette\Forms\Form');
		$this->maxFiles = $maxFiles;
		$this->control = Html::el("div");
		$this->maxFileSize = $this->parseIniSize(ini_get('upload_max_filesize'));
	}

	/**
	 * @param mixed $control
	 */
	protected function attached($control)
	{
		if ($control instanceof Form) {
			$control->elementPrototype->enctype = 'multipart/form-data';
			$control->elementPrototype->method = Form::POST;
		}
	}

	/**
	 * Generates control
	 * @return Html
	 */
	public function getControl()
	{
		$token = $this->getToken();
		$data = array(
			'id' => strtr($this->htmlId . '-box', '-', '_'),
			'uploadlink' => self::$request->url->baseUrl . "?token=$token",
			'sizelimit' => $this->maxFileSize,
			'maxfiles' => $this->maxFiles,
		);

		// Create control
		$control = Html::el('div')
		               ->class('multi-upload')
		               ->data($data)
		               ->id($this->htmlId);

		$tokenField = Html::el('input')
		                  ->type('hidden')
		                  ->name($this->htmlName . '[token]')
		                  ->value($token);
		$control->add($tokenField);

		$html = self::$interface->render($this);
		$id = $this->htmlId . "-plupload";

		$container = Html::el("div");
		$container->setHtml($html);
		$container->id = $id;
		$control->add($container);

		return $control;
	}

	/**
	 * Loads and process STANDARD http request.
	 */
	public function loadHttpData()
	{
		$name = strtr(str_replace(']', '', $this->getHtmlName()), '.', '_');
		$data = $this->form->httpData;
		if (isset($data[$name])) {
			if (isset($data[$name]["token"])) {
				$this->token = $data[$name]["token"];
			} else {
				throw new InvalidStateException("Token has not been received! Without token MultiUpload can't identify which files has been received.");
			}
		}
	}

	/**
	 * @param mixed $value
	 * @return UploadControl|void
	 */
	public function setValue($value)
	{
		if ($value !== NULL) {
			throw new NotSupportedException('Value of MultiUpload component cannot be directly set.');
		}
	}

	/**
	 * @return array
	 */
	public function getValue()
	{
		$data = $this->form->getHttpData();
		if (isset($data['file_tokes'])) {
			$fileTokens = array_keys($data['file_tokes']);
		} else {
			$fileTokens = array();
		}
		return $this->getQueue()->getFiles($fileTokens, $this->maxFiles);
	}

	/**
	 * Returns token
	 * @param bool $need
	 * @return string
	 */
	public function getToken($need = TRUE)
	{
		// Load token from request
		if (!$this->token) {
			$this->loadHttpData();
		}

		// If upload do not start, generate queueID
		if (!$this->token and !$this->form->isSubmitted()) {
			$this->token = uniqid(rand());
		}

		if (!$this->token && $need) {
			throw new InvalidStateException("Can't get a token!");
		}

		return $this->token;
	}

	/**
	 * Getts queue model
	 * @return MFUQueue
	 */
	public function getQueue()
	{
		return self::$model->getQueue($this->getToken());
	}

	/**
	 * Destructors: makes fast cleanup
	 */
	public function __destruct()
	{
		if ($this->form->isSubmitted()) {
			$this->getQueue()->delete();
		}
	}

	/**
	 * @param string $value
	 * @return int
	 */
	public function parseIniSize($value)
	{
		$units = array('k' => 1024, 'm' => 1048576, 'g' => 1073741824);
		$unit = strtolower(substr($value, -1));

		if (is_numeric($unit) || !isset($units[$unit])) {
			return $value;
		}

		return ((int) $value) * $units[$unit];
	}

	/**
	 * @param null $file
	 * @return ITemplate
	 */
	public static function createTemplate($file = null)
	{
		/** @var Template $template */
		$template = self::$templateFactory->createTemplate();
		$template->setFile($file);
		$template->setTranslator(self::$translator);

		return $template;
	}

	/**
	 * Filled validator: has been any file uploaded?
	 *
	 * @param IControl $control
	 * @return bool
	 * @internal param $IFormControl
	 */
	public static function validateFilled(IControl $control)
	{
		$files = $control->value;
		return (count($files) > 0);
	}

	/**
	 * FileSize validator: is file size in limit?
	 *
	 * @param UploadControl $control
	 * @param $limit
	 * @return bool
	 * @internal param $MultiUpload
	 * @internal param file $int size limit
	 */
	public static function validateFileSize(UploadControl $control, $limit)
	{
		$files = $control->getValue();
		$size = 0;
		foreach ($files AS $file) {
			$size += $file->getSize();
		}
		return $size <= $limit;
	}

	/**
	 * MimeType validator: has file specified mime type?
	 *
	 * @param UploadControl $control
	 * @param $mimeType
	 * @return bool
	 * @internal param $FileUpload
	 * @internal param array|string $mime type
	 */
	public static function validateMimeType(UploadControl $control, $mimeType)
	{
		return (bool) count(array_filter(
			$control->getValue(), function ($file) use ($mimeType) {
				if ($file instanceof FileUpload) {
					$type = strtolower($file->getContentType());
					$mimeTypes = is_array($mimeType) ? $mimeType : explode(',', $mimeType);
					if (in_array($type, $mimeTypes, TRUE)) {
						return TRUE;
					}
					if (in_array(preg_replace('#/.*#', '/*', $type), $mimeTypes, TRUE)) {
						return TRUE;
					}
				}
				return FALSE;
			}
		));
	}

}
