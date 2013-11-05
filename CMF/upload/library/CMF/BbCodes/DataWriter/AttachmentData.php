<?php
class CMF_BbCodes_DataWriter_AttachmentData extends XFCP_CMF_BbCodes_DataWriter_AttachmentData
{
	protected function _postDelete()
	{
		$data = $this->getMergedData();
		/** @var $attachmentModel CMF_BbCodes_Model_Attachment */
		$attachmentModel = $this->_getAttachmentModel();

		foreach (CMF_BbCodes_Listener::$validFilters as $filter => $sizes)
		{
			$data['live_filter']=$filter;
			$file = $attachmentModel->getAttachmentDataFilePath($data);
			if (file_exists($file) && is_writable($file))
			{
				unlink($file);
			}
		}
		parent::_postDelete();
	}
}