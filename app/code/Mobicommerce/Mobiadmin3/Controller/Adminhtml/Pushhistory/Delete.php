<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Pushhistory;

use Magento\Backend\App\Action\Context;

class Delete extends \Magento\Backend\App\Action
{
    protected $_model;
 
    /**
     * @param Action\Context $context
     */
    public function __construct(Context $context, \Mobicommerce\Mobiadmin3\Model\Pushhistory $model) {
        parent::__construct($context);
        $this->_model = $model;
    }
 
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->_model;
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('Push Notification deleted'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/view', ['id' => $id]);
            }
        }
        $this->messageManager->addError(__('Push Notification does not exist'));
        return $resultRedirect->setPath('*/*/');
    }
}