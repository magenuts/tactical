<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Ui\Component\Listing\Column;

class Year implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Mobicommerce\Deliverydate\Model\ResourceModel\Holidays\Collection
     */
    protected $collection;

    /**
     * Year options constructor.
     *
     * @param \Mobicommerce\Deliverydate\Model\ResourceModel\Holidays\CollectionFactory $holidayCollectionFactory
     */
    public function __construct(
        \Mobicommerce\Deliverydate\Model\ResourceModel\Holidays\CollectionFactory $holidayCollectionFactory
    ) {
        $this->collection = $holidayCollectionFactory->create();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->collection->getYearsAsArray('year');
    }
}
