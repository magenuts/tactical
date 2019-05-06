<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https+//www.Mobicommerce.com)
 * @package Mobicommerce_GroceryService
 */

namespace Mobicommerce\GroceryService\Observer;

use \Mobicommerce\GroceryService\Helper\Data as GroceryServiceHelper;
use \Magento\Framework\Event\ObserverInterface;

class ModifyProductListingService implements ObserverInterface
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

            $products = [];
            if(isset($responseData['data']['products']))
            {
                $products = $responseData['data']['products'] ;
            }
            else if($responseData['data']['wishlist'])
            {
                $products = $responseData['data']['wishlist'] ;
            }
            
            if(count($products))
            {
                $products = $this->groceryServiceHelper->arrangeProductListing($products);    

                if(isset($responseData['data']['products']))
                {
                    $responseData['data']['products'] = $products;
                }
                else if($responseData['data']['wishlist'])
                {
                    $responseData['data']['wishlist'] = $products;
                }
            }

            $observer->setResponseData($responseData);
        }
    }
}
