<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\Deliverydate\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class Dinterval extends AbstractDb
{

    protected function _construct()
    {
        $this->_init('mobicommerce_mobideliverydate_dinterval', 'dinterval_id');
    }
}
