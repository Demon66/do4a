<?php

/**
 * Model for attachments.
 *
 * @package XenForo_Attachment
 */
class CMF_BbCodes_Model_Attachment extends XFCP_CMF_BbCodes_Model_Attachment
{

	public static $slideImageQuality = 85;

	/**
	 * Gets the specified attachment by it's ID. Includes some data info.
	 *
	 * @param integer $attachmentId
	 *
	 * @return array|false
	 */
	public function getAttachmentById($attachmentId)
	{
		//TODO verify this method 
		if (XenForo_Application::isRegistered('attachment_filters') && ($filters = XenForo_Application::get('attachment_filters')) && !empty($filters[$attachmentId]))
		{
			$attachment = parent::getAttachmentById($attachmentId);
			if ($attachment['thumbnail_width'])
			{
				$attachment['live_filter'] = $filters[$attachmentId];
			}
			else
			{
				return $attachment;
			}
		}
		return parent::getAttachmentById($attachmentId);
	}

	/**
	 * Gets the full path to this attachment's data.
	 *
	 * @param array $data Attachment data info
	 *
	 * @return string
	 */
	public function getAttachmentDataFilePath(array $data, $internalDataPath = null)
	{
		if (!empty($data['live_filter']))
		{
			return (
				!empty(CMF_BbCodes_Listener::$validFilters[$data['live_filter']])
					&&
					!empty(CMF_BbCodes_Listener::$validFilters[$data['live_filter']][2])
			) ?
				XenForo_Helper_File::getExternalDataPath()
					. '/attachments/' . $data['live_filter'] . '/' . floor($data['data_id'] / 1000)
					. "/$data[data_id]-$data[file_hash].jpg"
				:
				XenForo_Helper_File::getInternalDataPath()
					. '/attachments/' . $data['live_filter'] . '/' . floor($data['data_id'] / 1000)
					. "/$data[data_id]-$data[file_hash].data";
		}
		else
		{
			return parent::getAttachmentDataFilePath($data);
		}
	}

	public function getAttachmentOriginalDataFilePath(array $data)
	{
		return parent::getAttachmentDataFilePath($data);
	}

	public function checkFilterAttachmentData($attachment)
	{
		$filePathFilter = $this->getAttachmentDataFilePath($attachment);
		$filePath = $this->getAttachmentOriginalDataFilePath($attachment);
		if (empty($attachment['live_filter']) || empty(CMF_BbCodes_Listener::$validFilters[$attachment['live_filter']]))
		{
			return false;
		}

		if (($filePathFilter != $filePath) && !file_exists($filePathFilter) && file_exists($filePath) && is_readable($filePath) && ($imageInfo = getimagesize($filePath)))
		{
			$width = $imageInfo[0];
			$height = $imageInfo[1];
			$imageType = $imageInfo[2];

			if (in_array($imageType, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
			{
				$newSize = CMF_BbCodes_Listener::$validFilters[$attachment['live_filter']];
				$newWidth = ($newSize[0]) ? $newSize[0] : $width;
				$newHeight = ($newSize[1]) ? $newSize[1] : $height;

				$newTempFile = tempnam(XenForo_Helper_File::getTempDir(), 'xf');
				$image = XenForo_Image_Abstract::createFromFile($filePath, $imageType);
				if (!$image)
				{
					throw new XenForo_Exception(new XenForo_Phrase('image_could_be_processed_try_another_contact_owner'), true);
				}

				$image->thumbnail($newWidth, $newHeight);
//				$image->output($outputType, $filePathFilter, self::$imageQuality);
				$image->output($imageType, $newTempFile, self::$slideImageQuality);
				unset($image);

				$directory = dirname($filePathFilter);

				if (XenForo_Helper_File::createDirectory($directory, true) && is_writable($directory))
				{
					if (file_exists($filePathFilter))
					{
						unlink($filePathFilter);
					}
					$success = rename($newTempFile, $filePathFilter);
					if ($success)
					{
						XenForo_Helper_File::makeWritableByFtpUser($filePathFilter);
					}
					return $success;
				}
				else
				{
					throw new XenForo_Exception(new XenForo_Phrase('image_could_be_processed_try_another_contact_owner'), true);
				}
			}
		}
		return false;
	}
}