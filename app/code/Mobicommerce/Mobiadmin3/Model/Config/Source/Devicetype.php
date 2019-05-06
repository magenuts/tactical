<?php

namespace Mobicommerce\Mobiadmin3\Model\Config\Source;

class Devicetype implements \Magento\Framework\Option\ArrayInterface
{ 
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'both', 'label' => 'Both'],
            ['value' => 'android', 'label' => 'Android'],
            ['value' => 'ios', 'label' => 'iOS'],
        ];
    }
}