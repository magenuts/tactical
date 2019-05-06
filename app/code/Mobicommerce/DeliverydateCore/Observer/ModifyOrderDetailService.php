<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https+//www.Mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\DeliverydateCore\Observer;

use \Mobicommerce\DeliverydateCore\Helper\Data as DeliverydateCoreHelper;
use \Magento\Framework\Event\ObserverInterface;

class ModifyOrderDetailService implements ObserverInterface
{
    /**
     * @var \Mobicommerce\GroceryService\Helper\Data
     */
    protected $deliverydateCoreHelper;
    /*
     * @param GroceryServiceHelper $deliverydateHelper
     */
    public function __construct(
        DeliverydateCoreHelper $deliverydateCoreHelper
    ) {
        $this->deliverydateCoreHelper = $deliverydateCoreHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->deliverydateCoreHelper->isModuleEnable())
        {            
            $responseData = $observer->getResponseData();
            $deliveryDate = $this->deliverydateCoreHelper->getOrderDeliveryParameters($responseData['data']['order_details']['order_id']);
            $responseData['data']['order_details']['deliveryDate'] = $deliveryDate;
            $observer->setResponseData($responseData);
        }
    }
}
