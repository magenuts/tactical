<?php

namespace Mobicommerce\Mobiadmin3\Model\Config\Source;

class Mobilicensetype implements \Magento\Framework\Option\ArrayInterface
{ 
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'demo', 'label' => 'Demo Version'],
            ['value' => 'live', 'label' => 'Live Version']
        ];
    }
}