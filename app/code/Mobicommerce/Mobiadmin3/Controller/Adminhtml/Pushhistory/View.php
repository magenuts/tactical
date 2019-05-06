<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Pushhistory;

use Magento\Backend\App\Action\Context;

class View extends \Magento\Backend\App\Action
{
    protected $_resultPageFactory;
    protected $_model;
    protected $_coreRegistry = null;

    public function __construct(
        Context $context, 
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Mobicommerce\Mobiadmin3\Model\Pushhistory $model) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_model = $model;
    }

    public function execute()
    {
        $push = $this->_initPushNotification();
        $resultRedirect = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Mobicommerce_Mobiadmin3::push_notification_history');
        if ($push) {
            $resultRedirect->getConfig()->getTitle()->prepend((__('Push Notification Detail')));
            return $resultRedirect;
        }
        
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }

    protected function _initPushNotification()
    {
        $id = $this->getRequest()->getParam('id');
        $push = $this->_model->load($id);
        if(!$push->getId()) {
            $this->messageManager->addError(__('This notification no longer exists.'));
            return false;
        }
        
        $this->_coreRegistry->register('mobicommerce_pushnotification', $push);
        return $push;
    }
}