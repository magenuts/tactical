<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https+//www.Mobicommerce.com)
 * @package Mobicommerce_GroceryService
 */

namespace Mobicommerce\GroceryService\Observer;

use \Mobicommerce\GroceryService\Helper\Data as GroceryServiceHelper;
use \Magento\Framework\Event\ObserverInterface;

class ModifyCheckoutCartService implements ObserverInterface
{
    /**
     * @var \Mobicommerce\GroceryService\Helper\Data
     */
    protected $groceryServiceHelper;
    /*
     * @param GroceryServiceHelper $groceryServiceHelper
     */
    public function __construct(
        GroceryServiceHelper $groceryServiceHelper
    ) {
        $this->groceryServiceHelper = $groceryServiceHelper;        
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->groceryServiceHelper->isModuleEnable())
        {
            $responseData = $observer->getResponseData();
            
            $cart_array = isset($responseData['data']['cart_details']) ? $responseData['data']['cart_details'] : '';

            $cart_array = $this->groceryServiceHelper->arrangeCartItems($cart_array);

            $responseData['data']['cart_details'] = $cart_array;

            $observer->setResponseData($responseData);
        }
    }
}
