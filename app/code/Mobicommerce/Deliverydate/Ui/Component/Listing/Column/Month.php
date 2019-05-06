<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Ui\Component\Listing\Column;

class Month implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Mobicommerce\Deliverydate\Helper\Data
     */
    private $mobiHelper;

    /**
     * @var bool
     */
    private $eachMonthAvailable;

    /**
     * Month Options constructor.
     *
     * @param \Mobicommerce\Deliverydate\Helper\Data $mobiHelper
     * @param bool                             $eachMonthAvailable
     */
    public function __construct(
        \Mobicommerce\Deliverydate\Helper\Data $mobiHelper,
        $eachMonthAvailable = false
    ) {
        $this->mobiHelper = $mobiHelper;
        $this->eachMonthAvailable = $eachMonthAvailable;
    }

    public function toOptionArray()
    {
        $months = $this->mobiHelper->getMonths($this->eachMonthAvailable);
        $options = [];
        foreach ($months as $value => $month) {
            $options[] = ['value' => $value, 'label' => $month];
        }

        return $options;
    }
}
