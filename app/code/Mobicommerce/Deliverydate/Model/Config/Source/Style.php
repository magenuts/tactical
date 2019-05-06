<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\Deliverydate\Model\Config\Source;

class Style implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'as_is',
                'label' => __('As is')
            ),
            array(
                'value' => 'notice',
                'label' => __('Magento Notice')
            ),
        );
    }
}
