<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Ui\Component\Listing\Column;

class Store implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $options;
    protected $store;

    public function __construct(
        \Magento\Store\Model\ResourceModel\Store\Collection $store
    )
    {
        $this->store = $store;
    }

    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = $this->store->toOptionArray();
        }

        $this->options[] = array(
            'value' => 0,
            'label' => __('All Store Views')
        );

        return $this->options;
    }
}
