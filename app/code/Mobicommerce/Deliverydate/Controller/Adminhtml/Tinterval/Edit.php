<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Controller\Adminhtml\Tinterval;

class Edit extends \Mobicommerce\Deliverydate\Controller\Adminhtml\Tinterval
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Mobicommerce\Deliverydate\Model\Tinterval');

        if ($id) {
            $this->resourceModel->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                return $this->_redirect('mobicommerce_deliverydate/*');
            }
        }
        // set entered data if was error when we do save
        $data = $this->session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->_coreRegistry->register('current_mobicommerce_deliverydate_tinterval', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mobicommerce_Deliverydate::deliverydate_tinterval');

        $title = $model->getId() ? __('Edit Time Interval') : __('New Time Interval');
        $resultPage->getConfig()->getTitle()->prepend($title);
        $resultPage->addBreadcrumb($title, $title);

        return $resultPage;

    }
}
