<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Model\ResourceModel\Holidays;

class Collection extends \Mobicommerce\Deliverydate\Model\ResourceModel\DateCollectionAbstract
{
    protected function _construct()
    {
        $this->_init('Mobicommerce\Deliverydate\Model\Holidays', 'Mobicommerce\Deliverydate\Model\ResourceModel\Holidays');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
