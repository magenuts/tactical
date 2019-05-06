<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Controller\Adminhtml\Deliverydate;

class Edit extends \Mobicommerce\Deliverydate\Controller\Adminhtml\Deliverydate
{

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $model = $this->model->create();
        $this->resourceModel->load($model, $orderId, 'order_id');
        $orderModel = $this->orderFactory->create();
        $this->orderResource->load($orderModel, $orderId);

        $incrementId = $orderModel->getIncrementId();

        // set entered data if was error when we do save
        $data = $this->session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->coreRegistry->register('current_mobicommerce_deliverydate', $model);
        $this->coreRegistry->register('current_order', $orderModel);

        $resultPage = $this->resultPageFactory->create();

        $title = __('Edit Delivery Date For The Order #%1', $incrementId);
        $resultPage->getConfig()->getTitle()->prepend($title);
        $resultPage->addBreadcrumb($title, $title);

        return $resultPage;
    }
}
