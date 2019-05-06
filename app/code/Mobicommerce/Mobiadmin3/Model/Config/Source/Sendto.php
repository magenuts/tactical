<?php

namespace Mobicommerce\Mobiadmin3\Model\Config\Source;

class Sendto implements \Magento\Framework\Option\ArrayInterface
{ 
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'all', 'label' => 'All'],
            ['value' => 'customer_group', 'label' => 'Customer Group'],
            ['value' => 'specific_customer', 'label' => 'Specific Customer'],
        ];
    }
}