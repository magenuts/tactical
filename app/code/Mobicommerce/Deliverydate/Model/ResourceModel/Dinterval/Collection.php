<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Model\ResourceModel\Dinterval;

class Collection extends \Mobicommerce\Deliverydate\Model\ResourceModel\DateCollectionAbstract
{
    protected function _construct()
    {
        $this->_init('Mobicommerce\Deliverydate\Model\Dinterval', 'Mobicommerce\Deliverydate\Model\ResourceModel\Dinterval');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param int $currentStoreId
     *
     * @return \Mobicommerce\Deliverydate\Model\Dinterval[]
     */
    public function filterByStore($currentStoreId)
    {
        $dintervals = [];

        foreach ($this as $item) {
            $storeIds = trim($item->getData('store_ids'), ',');
            $storeIds = explode(',', $storeIds);
            if (!in_array($currentStoreId, $storeIds) && !in_array(0, $storeIds)) {
                continue;
            }
            $dintervals[] = $item;
        }

        return $dintervals;
    }
}
