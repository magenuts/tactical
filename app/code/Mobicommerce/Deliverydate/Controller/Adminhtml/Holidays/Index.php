<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\Deliverydate\Controller\Adminhtml\Holidays;


class Index extends \Mobicommerce\Deliverydate\Controller\Adminhtml\Holidays
{

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();

        return $resultPage;
    }
}
