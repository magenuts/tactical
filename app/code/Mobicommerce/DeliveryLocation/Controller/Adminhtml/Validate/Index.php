<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.Mobicommerce.com)
 * @package Mobicommerce_DeliveryLocation
 */
namespace Mobicommerce\DeliveryLocation\Controller\Adminhtml\Validate;

use \Mobicommerce\DeliveryLocation\Helper\Data as DeliveryLocationHelper;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\App\Request\Http;
use \Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Mobicommerce\DeliveryLocation\Helper\Data
     */
    private $deliveryLocationHelper;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;
    /**
    *
    * @param Context $context
    * @param Http $request
    * @param DeliveryLocationHelper $deliveryLocationHelper
    */
    public function __construct(
        Context $context,
        Http $request,
        DeliveryLocationHelper $deliveryLocationHelper,
        array $data = []
    ) {
        $this->deliveryLocationHelper = $deliveryLocationHelper;

        $this->request = $request;

        parent::__construct($context);         
    }    

    public function execute()
    {
        $data=$this->getRequest()->getParams();
        
        $response['status'] = true;

        if($this->deliveryLocationHelper->isAdminValidationEnable())
        {
            if($data['shipping_same_as_billing']=="on")
            {
                $address = $data['order']['billing_address'];
            }
            else
            {
                $address = $data['order']['shipping_address'];
            }
            $response['status'] = $this->deliveryLocationHelper->validateAddress($address);
            $response['message'] = $this->deliveryLocationHelper->getErrorMessage();
        }       
        
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        
        $resultJson->setData($response);

        return $resultJson;
    }
  
}