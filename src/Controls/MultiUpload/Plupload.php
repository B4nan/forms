<?php

namespace Bargency\Forms\Controls\MultiUpload;

use Bargency\Forms\Controls\MultiUpload,
	Nette\Http\FileUpload,
	Nette\Object;

/**
 * Plupload interface
 *
 * @author Martin Adámek <adamek@bargency.com>
 */
class Plupload extends Object
{

	/** @var HttpRequest */
	private $request;

	/** @var Model */
	private $model;

	/** @var string */
	private $tempPath;

	/**
	 * @param HttpRequest $request
	 * @param Model $model
	 * @param string $tempPath
	 */
	public function __construct($request, $model, $tempPath)
	{
		$this->request = $request;
		$this->model = $model;
		$this->tempPath = $tempPath;
	}

	/**
	 * Handles uploaded files
	 * forwards it to model
	 */
	public function handleUploads()
	{
		$token = $this->request->getQuery('token');
		if (empty($token)) {
			return;
		}

		$fileToken = $this->request->getQuery("file");

		// Make sure file is not cached (as it happens for example on iOS devices)
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", FALSE);
		header("Pragma: no-cache");

		// Settings
		$queueModel = $this->model->getQueue($token);
		$this->tempPath = $this->tempPath;
		$maxFileAge = MultiUpload::MIN_LIFETIME; // Temp file age in seconds
		@set_time_limit($maxFileAge);

		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
		$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
		$fileNameOriginal = $fileName;
		$fileName = sha1($token . $chunks . $fileNameOriginal);
		$filePath = $this->tempPath . '/' . $fileName;

		// Clean the fileName for security reasons
		$fileName = preg_replace('/[^\w\._]+/', '', $fileName);

		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists($this->tempPath . '/' . $fileName)) {
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);

			$count = 1;
			while (file_exists($this->tempPath . '/' . $fileName_a . '_' . $count . $fileName_b)) {
				$count++;
			}

			$fileName = $fileName_a . '_' . $count . $fileName_b;
		}

		// Create target dir
		if (!file_exists($this->tempPath)) {
			@mkdir($this->tempPath);
		}

		// Remove old temp files
		if (is_dir($this->tempPath) && ($dir = opendir($this->tempPath))) {
			while (($file = readdir($dir)) !== FALSE) {
				$filePathTemp = $this->tempPath . '/' . $file;
				// Remove temp files if they are older than the max age
				if (preg_match('/\-uploadTmp$/', $file) && (filemtime($filePathTemp) < time() - $maxFileAge)) {
					@unlink($filePathTemp);
				}
			}

			closedir($dir);
		} else {
			exit('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
		}

		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"])) {
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
		}

		if (isset($_SERVER["CONTENT_TYPE"])) {
			$contentType = $_SERVER["CONTENT_TYPE"];
		}

		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== FALSE) {
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
				$tmpPath = "$filePath-uploadTmp";
				move_uploaded_file($_FILES['file']['tmp_name'], $tmpPath); // Open base restriction bugfix
				// Open temp file
				$out = fopen($filePath, $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($tmpPath, "rb");

					if ($in) {
						while ($buff = fread($in, 4096)) {
							fwrite($out, $buff);
						}
					} else {
						exit('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					}
					fclose($in);
					fclose($out);
					@unlink($tmpPath);
				} else {
					exit('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
				}
			} else {
				exit('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
			}
		} else {
			// Open temp file
			$out = fopen($filePath, $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");

				if ($in) {
					while ($buff = fread($in, 4096)) {
						fwrite($out, $buff);
					}
				} else {
					exit('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
				}

				fclose($in);
				fclose($out);
			} else {
				exit('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			}
		}

		if ($chunk == 0) {
			$queueModel->addFileManually($fileName, $fileToken, $chunk + 1,$chunks);
		}
		$file = NULL;
		if ((int) $chunks === ($chunk + 1) || (!$chunk && !$chunks)) {
			$file = new FileUpload(array(
				'name' => $fileNameOriginal,
				'type' => '',
				'size' => filesize($filePath),
				'tmp_name' => $filePath,
				'error' => UPLOAD_ERR_OK,
			));
		}
		if ($file || $chunk > 0) {
			$queueModel->updateFile($fileName, $chunk + 1, $file);
		}

		// Return JSON-RPC response
		exit('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}

	/**
	 * Renders interface to <div>
	 */
	public function render(MultiUpload $upload)
	{
		if ($upload->useDefaultTemplate) {
			$template = $upload::createTemplate(__DIR__ . "/html.latte");
			$template->id = strtr($upload->htmlId . '-box', '-', '_');
			$template->htmlId = $upload->htmlId;
			return (string) $template;
		}
	}

}
