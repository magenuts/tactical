<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Plugin\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult as GridSearchResult;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Add Data, Filters, Sorting functional for Order/Shipment/Invoice Adminhtml Grids for Delivery Date fields
 */
class SearchResult
{
    /**
     * key - grid column name
     * value - sql column name
     */
    const DELIVERY_COLUMN = [
            'mobicommerce_deliverydate_date'    => 'mobideliverydate.date',
            'mobicommerce_deliverydate_time'    => 'mobideliverydate.time',
            'mobicommerce_deliverydate_comment' => 'mobideliverydate.comment'
        ];

    /**
     * @var \Mobicommerce\Deliverydate\Model\ResourceModel\Deliverydate
     */
    private $deliverydateResource;

    /**
     * @var \Mobicommerce\Deliverydate\Helper\Data
     */
    private $helper;

    /**
     * SearchResult constructor.
     *
     * @param \Mobicommerce\Deliverydate\Model\ResourceModel\Deliverydate $deliverydateResource
     * @param \Mobicommerce\Deliverydate\Helper\Data                      $helper
     */
    public function __construct(
        \Mobicommerce\Deliverydate\Model\ResourceModel\Deliverydate $deliverydateResource,
        \Mobicommerce\Deliverydate\Helper\Data $helper
    ) {
        $this->deliverydateResource = $deliverydateResource;
        $this->helper = $helper;
    }

    /**
     * @param GridSearchResult             $collection
     * @param \Magento\Framework\DB\Select $select
     *
     * @return \Magento\Framework\DB\Select
     */
    public function afterGetSelect(
        GridSearchResult $collection,
        $select
    ) {
        if ((string)$select && !array_key_exists('mobideliverydate', $select->getPart('from'))) {
            $select->joinLeft(
                ['mobideliverydate' => $this->deliverydateResource->getMainTable()],
                'main_table.entity_id = mobideliverydate.order_id',
                self::DELIVERY_COLUMN
            );
        }

        return $select;
    }

    /**
     * Prepare items delivery date to format for Grid
     *
     * @param GridSearchResult              $collection
     * @param \Magento\Framework\DataObject $item
     *
     * @return array
     */
    public function beforeAddItem(
        GridSearchResult $collection,
        \Magento\Framework\DataObject $item
    ) {
        $date = $item->getDataByKey('mobicommerce_deliverydate_date');
        if ($date) {
            if ($date == '0000-00-00') {
                $item->setData('mobicommerce_deliverydate_date');
                return [$item];
            }
            $date = $this->helper->convertDateOutput($date);
            $item->setData('mobicommerce_deliverydate_date', $date);
        }
        return [$item];
    }

    /**
     * @param GridSearchResult $collection
     * @param string           $field
     * @param string|null      $condition
     *
     * @return array
     */
    public function beforeAddFieldToFilter(
        GridSearchResult $collection,
        $field,
        $condition = null
    ) {
        if (array_key_exists($field, self::DELIVERY_COLUMN)) {
            $field = self::DELIVERY_COLUMN[$field];
        }
        if ($field == OrderInterface::INCREMENT_ID) {
            $field = 'main_table.' . OrderInterface::INCREMENT_ID;
        }

        return [$field, $condition];
    }
}
