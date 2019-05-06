<?php

namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Devicetokens;

class Index extends \Magento\Backend\App\Action
{
	protected $_resultPageFactory;

	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	)
	{
		parent::__construct($context);
		$this->_resultPageFactory = $resultPageFactory;
	}

	public function execute()
	{
		$resultPage = $this->_resultPageFactory->create();
		$resultPage->getConfig()->getTitle()->prepend((__('Device Tokens')));
		$resultPage->setActiveMenu('Mobicommerce_Mobiadmin3::devicetokens');
		return $resultPage;
	}
}