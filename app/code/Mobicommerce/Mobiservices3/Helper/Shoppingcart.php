<?php
namespace Mobicommerce\Mobiservices3\Helper;

class Shoppingcart extends \Magento\Framework\App\Helper\AbstractHelper {

    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    public function getCoreHelper($helperPath)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection
          $productCollection */
        $helper = $objectManager->create($helperPath);
        /** Apply filters here */
        return $helper;
    }

    public function formatOptionsCart($options)
    {        
        $data = [];
        foreach ($options as $option) {
            $optionVal = 0;
            if(isset($option['option_value']) && $option['option_value']){
                $optionVal = $option['option_value'];
            } elseif(isset($option['option_value_id']) && $option['option_value_id']) {
                $optionVal = $option['option_value_id'];
            }
            
            $data[] = [
                // change for grocery
                'option_id'       => isset($option['option_id']) ? $option['option_id'] : 0,
                'option_value_id' => $optionVal,
                // change for grocery - upto here
                'option_title'    => $option['label'],
                'option_value'    => $option['value'],
                'option_price'    => isset($option['price']) == true ? $option['price'] : 0,
            ];
        }
        
        return $data;
    }

    public function getOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item) {
        return array_merge(
            $this->getBundleOptions($item), 
            $this->formatOptionsCart($this->getCoreHelper('Magento\Catalog\Helper\Product\Configuration')->getCustomOptions($item))
            );
    }

    /**
     * it is for magento < 1.5.0.0
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return options
     */
    public function getUsedProductOption(\Magento\Quote\Model\Quote\Item $item) {
        $typeId = $item->getProduct()->getTypeId();
        switch ($typeId) {
            case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE:
                return $this->getConfigurableOptions($item);
                break;
            case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
                return $this->getGroupedOptions($item);
                break;
        }

        return $this->getCustomOptions($item);
    }

    public function getConfigurableOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item)
    {
        $product = $item->getProduct();
        $typeId = $product->getTypeId();
        $attributes = $product->getTypeInstance(true)
            ->getSelectedAttributesInfo($product);
        return array_merge($attributes, $this->getCustomOptions($item));
    }

    public function getGroupedOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item)
    {
        $product = $item->getProduct();
        $typeId = $product->getTypeId();
        $options = [];
        /**
         * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
         */
        $typeInstance = $product->getTypeInstance(true);
        $associatedProducts = $typeInstance->getAssociatedProducts($product);

        if ($associatedProducts) {
            foreach ($associatedProducts as $associatedProduct) {
                $qty = $item->getOptionByCode('associated_product_' . $associatedProduct->getId());
                $option = [
                    'label' => $associatedProduct->getName(),
                    'value' => ($qty && $qty->getValue()) ? $qty->getValue() : 0
                ];

                $options[] = $option;
            }
        }

        $options = array_merge($options, $this->getCustomOptions($item));
        $isUnConfigured = true;
        foreach ($options as &$option) {
            if ($option['value']) {
                $isUnConfigured = false;
                break;
            }
        }
        return $isUnConfigured ? [] : $options;
    }

    public function getCustomOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item) {
        $options = [];
        $product = $item->getProduct();
        
        if ($optionIds = $item->getOptionByCode('option_ids')) {
            $options = [];
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {

                    $quoteItemOption = $item->getOptionByCode('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                            ->setOption($option)
                            ->setQuoteItemOption($quoteItemOption);

                    $options[] = [
                        'label'       => $option->getTitle(),
                        'value'       => $group->getFormattedOptionValue($quoteItemOption->getValue()),
                        'print_value' => $group->getPrintableOptionValue($quoteItemOption->getValue()),
                        'option_id'   => $option->getId(),
                        'option_type' => $option->getType(),
                        'custom_view' => $group->isCustomizedView(),
                        // change for grocery
                        'option_value_id'   => $quoteItemOption->getValue(),
                        // change for grocery - upto here
                    ];
                }
            }
        }
        if ($addOptions = $item->getOptionByCode('additional_options')) {
            $options = array_merge($options, unserialize($addOptions->getValue()));
        }
        return $options;
        //return $this->formatOptionsCart($options);
    }

    public function getBundleOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item) {
        $options = [];
        $product = $item->getProduct();
        /**
         * @var \Magento\Bundle\Model\Product\Type
         */
        $typeInstance = $product->getTypeInstance(true);
            
        // get bundle options
        $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = $optionsQuoteItemOption ? json_decode($optionsQuoteItemOption->getValue()) : [];
        if ($bundleOptionsIds) {
            /**
             * @var Mage_Bundle_Model_Mysql4_Option_Collection
             */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);

            // get and add bundle selections collection
            $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');
            $selectionsCollection = $typeInstance->getSelectionsByIds(
                json_decode($selectionsQuoteItemOption->getValue()), $product
            );
            
            $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
            foreach ($bundleOptions as $bundleOption) {
                if ($bundleOption->getSelections()) {
                    $bundleSelections = $bundleOption->getSelections();
                    $option = [];
                    foreach ($bundleSelections as $bundleSelection) {
                        $check = [];
                        $qty = $this->getCoreHelper('Magento\Bundle\Helper\Catalog\Product\Configuration')->getSelectionQty($product, $bundleSelection->getSelectionId()) * 1;
                        if ($qty) {
                            $check[] = $qty . ' x ' . $bundleSelection->getName();
                            $option[] = [
                                'option_title' => $bundleOption->getTitle(),
                                'option_value' => $qty . ' x ' . $bundleSelection->getName(),
                                'option_price' => $this->getCoreHelper('Magento\Bundle\Helper\Catalog\Product\Configuration')->getSelectionFinalPrice($item, $bundleSelection),
                            ];
                        }
                    }
                    
                    if ($check)
                        $options[] = $option;
                    
                }
            }
        }

        return $options;
    }

    public function getDownloadableOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item) {
        $options = [];
        $product = $item->getProduct();
        /**
         * @var \Magento\Bundle\Model\Product\Type
         */
        $typeInstance = $product->getTypeInstance(true);

        // get bundle options
        $optionsQuoteItemOption = $item->getOptionByCode('downloadable_link_ids');
        $downlodableOptionsIds = $optionsQuoteItemOption ? $optionsQuoteItemOption->getValue() : [];
        if(!empty($downlodableOptionsIds)){
            $downlodableOptionsIds = explode(",", $downlodableOptionsIds);
        }
        $optionsCollection = $typeInstance->getLinks($product);
        $option = [];
        foreach ($optionsCollection as $_item) {
            if (in_array($_item->getId(), $downlodableOptionsIds)) {
                $option[] = $_item->getTitle();                
            }
        }
        if(!empty($option)){
            $options[] = [
                'option_title' => "Links",
                'option_value' => implode(", ", $option),
                ];
        }
        return array_merge(
            $options,
            $this->formatOptionsCart($this->getCoreHelper('Magento\Catalog\Helper\Product\Configuration')->getCustomOptions($item))
            );
    }
}