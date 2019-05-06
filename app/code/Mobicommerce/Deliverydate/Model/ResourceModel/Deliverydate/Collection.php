<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Model\ResourceModel\Deliverydate;

class Collection extends \Mobicommerce\Deliverydate\Model\ResourceModel\DateCollectionAbstract
{
    protected function _construct()
    {
        $this->_init('Mobicommerce\Deliverydate\Model\Deliverydate', 'Mobicommerce\Deliverydate\Model\ResourceModel\Deliverydate');
    }

    public function getOlderThan($start)
    {
        $this->getSelect()
            ->where('`date` <> \'0000-00-00\'')
            ->where('`date` <> \'1970-01-01\'')
            ->where('`date` >= ?', $start)
            ->where('`active` = \'1\'');

        return $this;
    }

    public function joinTinterval()
    {
        $this->getSelect()
            ->joinLeft(
                ['ti' => $this->getTable('mobicommerce_mobideliverydate_tinterval')],
                'main_table.tinterval_id = ti.tinterval_id',
                ['qty_order' => 'COUNT(main_table.deliverydate_id)']
            )
            ->where('ti.quota > 0 AND main_table.date is not null')
            ->group('ti.tinterval_id')
            ->group('main_table.date')
            ->having('qty_order >= MAX(ti.quota)');

        return $this;
    }
}
