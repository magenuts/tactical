<?php

namespace Mobicommerce\Mobiadmin3\Model\Config\Source;

class Mobiversiontype implements \Magento\Framework\Option\ArrayInterface
{ 
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '001', 'label' => 'Professional'],
            ['value' => '002', 'label' => 'Enterprise']
        ];
    }
}