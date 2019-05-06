<?php
namespace Mobicommerce\Mobiservices3\Helper;

class Product extends \Magento\Framework\App\Helper\AbstractHelper {
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    public function addDiscount(&$product)
    {
        if(!empty($product['price']) && !empty($product['special_price'])){
            if($product['price'] != $product['special_price']){
                $difference = $product['price'] - $product['special_price'];
                $product['discount'] = ceil((100 * $difference) / $product['price']) . '%';
            }
        }
    }
}