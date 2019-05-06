<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Ui\Component\Listing\Column;

class TypeDay implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @var \Mobicommerce\Deliverydate\Helper\Data
     */
    protected $mobiHelper;


    public function __construct(
        \Mobicommerce\Deliverydate\Helper\Data $mobiHelper
    )
    {
        $this->mobiHelper = $mobiHelper;

    }

    public function toOptionArray()
    {
        $typeDays = $this->mobiHelper->getTypeDay();
        $options = [];
        foreach ($typeDays as $value => $typeDay) {
            $options[] = ['value' => $value, 'label' => $typeDay];
        }

        return $options;
    }
}
