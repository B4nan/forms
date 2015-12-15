<?php

namespace Bargency\Forms\Controls;

use Bargency\Forms\Controls\MultiUpload\Uploader;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Forms\Controls\UploadControl;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;
use Nette\Utils\Random;

/**
 * MultiUpload control, uses Plupload
 *
 * @author Martin Adámek <adamek@bargency.com>
 */
class MultiUpload extends UploadControl
{

	/** @var string */
	const TEMP_DIR = 'multi-upload';

	/** @vat int minimal upload session lifetime */
	const MAX_LIFETIME = 1800; // 30 min

	/** @var int */
	public $maxFiles;

	/** @var int */
	public $maxFileSize;

	/** @var bool */
	public $useDefaultTemplate = TRUE;

	/** @var string */
	private $token;

	/** @var Uploader */
	private static $uploader;

	/**
	 * @param Container $container
	 */
	public static function register(Container $container)
	{
		$tempPath = $container->parameters['tempDir'] . '/' . self::TEMP_DIR;
		FileSystem::createDir($tempPath);

		/** @var Uploader $uploader */
		self::$uploader = $uploader = $container->getByType(Uploader::class);
		$uploader->setTempDir($tempPath);
		$application = $container->getService('application');
		$application->onStartup[] = function() use ($uploader) {
			$uploader->handleUploads();
		};
		$application->onShutdown[] = function() use ($uploader, $container) {
			if (! $container->parameters['productionMode'] || rand(1, 100) < 5) {
				$uploader->cleanTempDir();
			}
		};
	}

	/**
	 * @param string $label Label
	 * @param int $maxFiles
	 */
	public function __construct($label = NULL, $maxFiles = 999)
	{
		parent::__construct($label);

		$this->monitor(\Nette\Forms\Form::class);
		$this->maxFiles = $maxFiles;
		$this->control = Html::el("div");
		$this->maxFileSize = $this->parseIniSize(ini_get('upload_max_filesize'));
		$this->token = Random::generate(20);
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
		$data = [
			'id' => strtr($this->htmlId . '-box', '-', '_'),
			'uploadlink' => self::$uploader->baseUrl . "?multiupload=1",
			'sizelimit' => $this->maxFileSize,
			'maxfiles' => $this->maxFiles,
			'token' => $this->token,
		];

		// Create control
		$control = Html::el('div')
		               ->class('multi-upload')
		               ->data($data)
		               ->id($this->htmlId);

		$tokenField = Html::el('input')
				->type('hidden')
				->name($this->htmlName . '[token]')
				->value($this->token);
		$control->add($tokenField);

		if ($this->useDefaultTemplate) {
			$id = strtr($this->htmlId . '-box', '-', '_');
			$div = Html::el('div', [
				'class' => 'mfuplupload',
				'id' => $id,
			]);
			$div->add(Html::el('ul', [ 'id' => $id . '_filelist' ]));
			$div->add(Html::el('a', [
				'id' => $id . '_pickfiles',
				'href' => '#',
			])->setText($this->translate('Select files')));
			$html = (string) $div;
		} else {
			$html = '';
		}
		$id = $this->htmlId . "-plupload";

		$container = Html::el("div");
		$container->setHtml($html);
		$container->id = $id;
		$control->add($container);

		return $control;
	}

	/**
	 * @return array
	 */
	public function getValue()
	{
		$data = $this->form->getHttpData();
		$this->token = $data[$this->htmlName]['token'];
		if (isset($data['file_tokes'])) {
			$fileTokens = array_keys($data['file_tokes']);
		} else {
			$fileTokens = [];
		}
		return self::$uploader->restoreUploadQueue($this->token)->getUploads($fileTokens);
	}

	/**
	 * Destructors: makes fast cleanup
	 */
	public function __destruct()
	{
		if ($this->form->isSubmitted()) {
			self::$uploader->cleanTempDir();
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

}
