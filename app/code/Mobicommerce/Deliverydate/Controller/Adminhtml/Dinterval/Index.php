<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Controller\Adminhtml\Dinterval;

class Index extends \Mobicommerce\Deliverydate\Controller\Adminhtml\Dinterval
{

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page */
        return $this->_initAction();
    }
}
