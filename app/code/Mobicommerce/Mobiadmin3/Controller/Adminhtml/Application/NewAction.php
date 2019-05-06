<?php

namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Application;

class NewAction extends \Magento\Backend\App\Action
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
		$resultPage->getConfig()->getTitle()->prepend((__('Create New Application')));
		$resultPage->setActiveMenu('Mobicommerce_Mobiadmin3::applicationNew');
		return $resultPage;
	}
}