<?php

class CMF_BbCodes_ControllerPublic_Attachment extends XFCP_CMF_BbCodes_ControllerPublic_Attachment
{
	protected static $_attachmentCache = array();

	public function actionFilter()
	{
		$attachmentId = $this->_input->filterSingle('attachment_id', XenForo_Input::UINT);
		$attachment = $this->_getAttachmentOrError($attachmentId, $this->_input->filterSingle('live_filter', XenForo_Input::STRING));

		if ($attachment['thumbnail_width']) //This is image
		{
			/** @var $attachmentModel CMF_BbCodes_Model_Attachment */
			$attachmentModel = $this->_getAttachmentModel();
			$attachmentModel->checkFilterAttachmentData($attachment);
		}
		return $this->responseReroute(__CLASS__, 'Index');
	}

	/**
	 * Gets the specified attachment or throws an error.
	 *
	 * @param integer $attachmentId
	 * @param boolean|string $filter Filter name
	 *
	 * @return array
	 */
	protected function _getAttachmentOrError($attachmentId, $filter = false)
	{
		if (!empty(self::$_attachmentCache[$attachmentId]))
		{
			$attachment = self::$_attachmentCache[$attachmentId];
		}
		else
		{
			$attachment = parent::_getAttachmentOrError($attachmentId);
		}

		if ($filter)
		{
			if ($attachment['thumbnail_width'])
			{
				$attachment['live_filter'] = strval($filter);
			}
			self::$_attachmentCache[$attachmentId] = $attachment;

		}
		else
		{
			unset(self::$_attachmentCache[$attachmentId]);
		}

		return $attachment;
	}
}
