<?php

namespace Bargency\Forms\Controls\MultiUpload;

use Nette\Forms\Controls\UploadControl;
use Nette\Forms\IControl;
use Nette\Http\FileUpload;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
final class Validators
{

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
