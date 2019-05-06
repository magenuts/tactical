<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.Mobicommerce.com)
 * @package Mobicommerce_DeliveryLocation
 */

namespace Mobicommerce\DeliveryLocation\Observer;

use \Mobicommerce\DeliveryLocation\Helper\Data as DeliveryLocationHelper;
use \Magento\Framework\Event\ObserverInterface;
use Mobicommerce\DeliveryLocation\Model\DeliveryErrorMessage;

class ValidateDeliveryLocation implements ObserverInterface
{
    /**
     * @var \Mobicommerce\DeliveryLocation\Helper\Data
     */
    protected $deliveryLocationHelper;
    
    public function __construct(
        DeliveryLocationHelper $deliveryLocationHelper,
        DeliveryErrorMessage $deliveryErrorMessage
    ) {
        $this->deliveryLocationHelper = $deliveryLocationHelper;

        $this->deliveryErrorMessage = $deliveryErrorMessage;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        $responseData = $observer->getResponseData();
        
        $deliveryAddress = $observer->getController()->getRequest()->getParam("shipping");
        
        if(!$this->deliveryLocationHelper->validateAddress($deliveryAddress))
        {
            $error_message = $this->deliveryLocationHelper->getErrorMessage($data);
            $responseData =$this->deliveryErrorMessage->errorStatus($error_message); 
            $observer->setResponseData($responseData);
        }   
    }
}
