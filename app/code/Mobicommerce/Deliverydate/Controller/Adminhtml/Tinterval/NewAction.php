<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Controller\Adminhtml\Tinterval;

class NewAction extends \Mobicommerce\Deliverydate\Controller\Adminhtml\Tinterval
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
