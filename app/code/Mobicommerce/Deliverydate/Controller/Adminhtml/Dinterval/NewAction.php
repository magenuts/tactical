<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Controller\Adminhtml\Dinterval;

class NewAction extends \Mobicommerce\Deliverydate\Controller\Adminhtml\Dinterval
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
