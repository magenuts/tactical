<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https+//www.Mobicommerce.com)
 * @package Mobicommerce_GroceryService
 */

namespace Mobicommerce\GroceryService\Observer;

use \Mobicommerce\GroceryService\Helper\Data as GroceryServiceHelper;
use \Magento\Framework\Event\ObserverInterface;

class ModifyProductDetailService implements ObserverInterface
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

            $product_data = $responseData['data']['product_details'];
            
            $product_data = $this->groceryServiceHelper->arrangeProductDetailOption($product_data);
        
            $responseData['data']['product_details'] = $product_data;
            
            $observer->setResponseData($responseData);
        }
    }
}
