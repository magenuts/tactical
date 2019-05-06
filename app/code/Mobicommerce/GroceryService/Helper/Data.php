<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.Mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\GroceryService\Helper;

use \Magento\Framework\App\Helper\Context;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use \Magento\Catalog\Model\ProductFactory;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_MODULE_ENABLE = "general/enable";
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollectionFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;    
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productFactory;
    /*
     * @param GroceryServiceHelper $groceryServiceHelper
     * @param ProductCollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        Context $context,
        ProductCollectionFactory $productCollectionFactory,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManagerInterface
    ) {
        parent::__construct($context);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productFactory = $productFactory;
        $this->storeManagerInterface = $storeManagerInterface;
    }

    /**
     * Get config value for Store
     *
     * @param string  $path
     * @param null|string|bool|int|Store $store
     *
     * @return mixed
     */
    public function getStoreScopeValue($path,$explode = false, $store = null)
    {
        $data = $this->scopeConfig->getValue(
            'grocery_service/' . $path,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return $data;
    }
    /**
     * @return bool
     */
    public function isModuleEnable()
    {
        return (bool)$this->getStoreScopeValue(Self::CONFIG_MODULE_ENABLE);
    }    
    /**
     * @return bool
     */
    public function arrangeProductListing($products)
    {
        if(count($products))
        {
            foreach ($products as $key => $value) 
            {
                $productIds[] = $value["product_id"];
            }
            
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addIdFilter($productIds);;

            foreach ($productCollection as $key => $product) 
            {
                $_products[$key] = $product;
            }
            
            $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            foreach ($products as $key => &$product_data) 
            {
                $product_data = $this->arrangeProductDetailOption($product_data,$_products[$product_data['product_id']]);
            }
        }
        return $products;
    }
    /**
     * @return bool
     */
    public function arrangeProductDetailOption($product_data=[],$_product = null)
    {
        $product_data["grocery_type"] = $product_data["type"];
        $product_id = $product_data['product_id'];

        switch ($product_data['type']) {
             case 'simple':
                if(!$_product)
                {
                    $_product = $this->productFactory->create()->load($product_id);
                }
                
                $_base_price = $product_data['special_price'];
                
                if(!$_base_price) 
                {
                    $_base_price = $product_data['price'];
                }

                $store_id = $this->storeManagerInterface->getStore()->getId();

                $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();

                $customOptions = $_objectManager->create('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($_product);            

                $tmpI = 0;

                $options = [        
                    "product_options"          => [],
                    "product_super_attributes" => [],
                    "super_group"              => [],
                    "link"                     => [],
                    "sample_links"             => [],
                    "bundle"                   => [],
                    "virtual"                  => []
                ];

                if($customOptions->getSize())
                {
                    $product_data["grocery_type"] = "simple_with_options";
                    foreach ($customOptions as $key => $option) 
                    {
                        $option_id = $option->getId();
                        $options["product_options"][$tmpI] = $option->getData();
                        if($option->getType() == 'drop_down') {
                            foreach ($option->getValues() as $oValue) 
                            {
                                $value_id = $oValue->getId();
                                $opt_data = $oValue->getData();
                                $opt_data['combination_id'] = "{$product_id}_{$option_id}_{$value_id}";

                                $_price = 0;
                                $_option_price = 0;
                                if($opt_data['price_type'] == 'fixed')
                                {
                                    $_option_price = $_base_price + $opt_data['price'];
                                }
                                else if($opt_data['price_type'] == 'percent')
                                {
                                    $_option_price = $_base_price + ($_base_price * $opt_data['price'] / 100);
                                }

                                $_price_array[] = $opt_data['price'] = $_option_price;

                                $options["product_options"][$tmpI]['options'][] = $opt_data;
                            }
                            $tmpI++;
                        }
                        else {
                            $product_data["grocery_type"] = "simple_with_multipleoptions";
                            break;
                        }
                    }
                    
                    if($product_data["grocery_type"] == 'simple_with_options')
                    {
                        $product_data['price'] = min($_price_array);
                        $product_data['special_price'] = $product_data['price'];
                    }
                }
                if(in_array($product_data["grocery_type"], array('simple_with_options', 'simple')))
                {
                    $product_data["options"] = $options;
                    $product_data["combination_id"] = $product_id;
                }
                break;

            default:
                break;
        }

        return $product_data;
    }

    /**
     * @return bool
     */
    public function arrangeCartItems($cart_array)
    {
        $grocery_items = array();
        
        if(isset($cart_array['items']) && $cart_array['items'])
        {
            foreach($cart_array['items'] as $_item_key => $_item)
            {
                // because for other then simple products, flow should be as it is
                if(!in_array($_item['product_type'], array('simple'))) {
                    $grocery_items['nonsimple_'.uniqid()] = $_item;
                    continue;
                }
                if(count($_item['options']) > 1) {
                    $_item['product_type'] = 'simple_with_multipleoptions';
                    $grocery_items['nonsimple_'.uniqid()] = $_item;
                    continue;
                }

                if(!array_key_exists($_item['product_id'], $grocery_items))
                {
                    $grocery_items[$_item['product_id']] = $_item;
                    $grocery_items[$_item['product_id']]['grocery_options'] = array();
                }

                $_grocery_option = array();
                // simple product with option
                if($_item['options'])
                {
                    $_grocery_option['grocery_type'] = 'simple_with_options';
                    $_grocery_option['option_details'] = $_item['options'][0];
                    $_grocery_option['combination_id'] = $_item['product_id'].'_'.$_item['options'][0]['option_id'].'_'.$_item['options'][0]['option_value_id'];
                }
                // simple product without option
                else
                {
                    $_grocery_option['grocery_type'] = 'simple';
                    $_grocery_option['combination_id'] = $_item['product_id'];
                }
                
                $_grocery_option['item_id']            = $_item['item_id'];
                $_grocery_option['hasError']           = $_item['hasError'];
                $_grocery_option['errorDescription']   = $_item['errorDescription'];
                $_grocery_option['max_qty']            = $_item['max_qty'];
                $_grocery_option['price']              = $_item['price'];
                $_grocery_option['price_incl_tax']     = $_item['price_incl_tax'];
                $_grocery_option['qty']                = $_item['qty'];
                $_grocery_option['qty_increments']     = isset($_item['qty_increments']) ? $_item['qty_increments'] : 1;
                $_grocery_option['row_total']          = $_item['row_total'];
                $_grocery_option['row_total_incl_tax'] = $_item['row_total_incl_tax'];

                $grocery_items[$_item['product_id']]['grocery_options'][] = $_grocery_option;
            }
        }
        
        $cart_array['grocery_items'] = array_values($grocery_items);
        
        return $cart_array;
    }

    /**
     * @return bool
     */
    public function arrangeOrderItems($order)
    {
        $grocery_items = array();
        foreach($order['order_items'] as $_item) {
            // because for other then simple products, flow should be as it is
            if(!in_array($_item['product_type'], array('simple'))) {
                $grocery_items['nonsimple_'.uniqid()] = $_item;
                continue;
            }
            if(count($_item['options']) > 1) {
                $_item['product_type'] = 'simple_with_multipleoptions';
                $grocery_items['nonsimple_'.uniqid()] = $_item;
                continue;
            }

            if(!array_key_exists($_item['product_id'], $grocery_items)) {
                $grocery_items[$_item['product_id']] = $_item;
                $grocery_items[$_item['product_id']]['grocery_options'] = array();
            }

            $grocery_items[$_item['product_id']]['grocery_options'][] = $_item;
        }

        $grocery_items = array_values($grocery_items);
        $order['grocery_items'] = $grocery_items;

        return $order;
    }
}
