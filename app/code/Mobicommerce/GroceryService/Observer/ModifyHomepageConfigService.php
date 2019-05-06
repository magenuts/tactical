<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https+//www.Mobicommerce.com)
 * @package Mobicommerce_GroceryService
 */

namespace Mobicommerce\GroceryService\Observer;

use \Mobicommerce\GroceryService\Helper\Data as GroceryServiceHelper;
use \Magento\Framework\Event\ObserverInterface;

class ModifyHomepageConfigService implements ObserverInterface
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
            
            if(isset($responseData['data']['homedata']['widgets']))
            {
                $widgets = $responseData['data']['homedata']['widgets'] ;   
            }
            else if($responseData['data']['widgets'])
            {
                $widgets = $responseData['data']['widgets'] ;    
            }
            
            if(count($widgets))
            {
                foreach ($widgets as $key => &$widget) 
                {
                    if($widget['widget_code'] == 'widget_product_slider')
                    {
                        if(isset($widget['widget_data']['products']))
                        {
                            $products = $widget['widget_data']['products'];
                            $widget['widget_data']['products'] = $this->groceryServiceHelper->arrangeProductListing($products);
                        }                        
                    }                    
                }
                
                if(isset($responseData['data']['homedata']['widgets']))
                {
                    $responseData['data']['homedata']['widgets']  = $widgets;   
                }
                else if($responseData['data']['widgets'])
                {
                    $responseData['data']['widgets']  = $widgets;    
                }
            }

            $cart_details = !isset($responseData['data']['cart_details'])  ? '' : $responseData['data']['cart_details'];
            
            if($cart_details)
            {
                $cart_details = $this->groceryServiceHelper->arrangeCartItems($cart_details);
                $responseData['data']['cart_details'] = $cart_details;
            }
            
            $wishlist = isset($responseData['data']['wishlist']) ? $responseData['data']['wishlist'] : '';
            
            if($wishlist)
            {
                $wishlist = $this->groceryServiceHelper->arrangeProductListing($wishlist);
                $responseData['data']['wishlist'] = $wishlist;
            }

            $observer->setResponseData($responseData);
        }
    }
}
