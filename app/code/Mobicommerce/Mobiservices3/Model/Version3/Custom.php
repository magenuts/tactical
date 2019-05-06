<?php
namespace Mobicommerce\Mobiservices3\Model\Version3;

class Custom extends \Mobicommerce\Mobiservices3\Model\AbstractModel {

    const ROUNDUP_CART_VALUES = false;
    private $customModules;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,      
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager
    )
    {
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        
        parent::__construct($context, $registry, $storeManager, $eventManager);
        $this->customModules = [
            //'Mage_Newsletter',
            //'Mirasvit_Rewards'
            ];
    }

	public function getCustomCheckoutFields()
    {
        //$customFields = [];
        $customFields = array(
            "shipping_method" => array(
                array(
                    "code"              => "cgv",
                    "type"              => "checkbox",
                    "name"              => "I agree to the terms of service and will adhere to them unconditionally.",
                    "required"          => true,
                    "validation"        => "",
                    "error_message"     => "Please agreee to terms and conditions",
                    "params" => array(
                        "default_value" => "1",
                        "url"           => "https://212.129.48.226/terms-and-conditions",
                        "text" => "I have read and agree to the terms and conditions"
                        )
                    )
                ),
            );
        return $customFields;
    }

    public function getCustomProductDetailFields($_product, $productInfo)
    {
        $autoOutputFields = $this->getAdditionalData($_product);
        $outputFields = $autoOutputFields;
        if(empty($outputFields))
            $outputFields = null;
        
        $productInfo['customAttributes'] = $outputFields;
        return $productInfo;
    }

    public function getAdditionalData($product)
    {
        $data = [];
        $excludeAttr = [];
        $attributes = $product->getAttributes();
        foreach($attributes as $attribute){
            if($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)){
                $value = $attribute->getFrontend()->getValue($product);

                if(!$product->hasData($attribute->getAttributeCode())) {
                    continue;
                    $value =__('N/A');
                } elseif((string)$value == '') {
                    continue;
                    $value = __('No');
                } elseif($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->storeManager->getStore()->convertPrice($value, true);
                }

                if(is_string($value) && strlen($value)) {
                    $data[] = [
                        'name'     => $attribute->getStoreLabel(),
                        'relateTo' => 'description',
                        'value'    => $value,
                        'code'     => $attribute->getAttributeCode()
                    ];
                }
            }
        }
        return $data;
    }

    /**
     * Coded by Yash
     * Date: 18-12-2014
     * For calculating simple and compund interest for installment options
     */
    public function _processPayuapiInstallmentOptionsString($str = null, $price = 0, $interest_type = 'simple', $force_yearly_interest = false)
    {
        $str = trim($str);
        if(empty($str))
            return null;

        /**
         * Formula for simple interest
         * A = P(1 + rt)
         * P = principal amount
         * r = rate of interest
         * t = time(in tems of years)
         * calculating months to years
         * 2 months = 2 / 12 = 0.17 years
         */
        $return_options = [];
        $installment_options = explode(';', $str);
        foreach($installment_options as $ioption){
            $month_interest_pair = explode('=', $ioption);
            if(count($month_interest_pair) == 2){
                $month_count = $month_interest_pair[0];
                $interest_rate = $month_interest_pair[1];
                if($interest_type == 'simple'){
                    $t = 1;
                    if($force_yearly_interest)
                        $t = ceil($month_count / 12);
                    else
                        $t = round($month_count / 12, 2);

                    $r = $interest_rate / 100;
                    $total_amount = $price * (1 + ($r * $t));
                    $return_options[] = [
                        'month_count'        => $month_count,
                        'installment_amount' => round($total_amount / $month_count),
                        'total_amount'       => round($total_amount),
                        'exact_total_amount' => round($total_amount, 2)
                        ];
                }
                else if($interest_type == 'compound'){

                }
            }
        }
        return $return_options;
    }
}