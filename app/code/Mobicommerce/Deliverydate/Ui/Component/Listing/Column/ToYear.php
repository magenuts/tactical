<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Ui\Component\Listing\Column;

class ToYear implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Mobicommerce\Deliverydate\Model\ResourceModel\Dinterval\Collection
     */
    protected $collection;

    /**
     * ToYear constructor.
     *
     * @param \Mobicommerce\Deliverydate\Model\ResourceModel\Dinterval\CollectionFactory $dIntervalCollectionFactory
     */
    public function __construct(
        \Mobicommerce\Deliverydate\Model\ResourceModel\Dinterval\CollectionFactory $dIntervalCollectionFactory
    ) {
        $this->collection = $dIntervalCollectionFactory->create();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->collection->getYearsAsArray('to_year');
    }
}
