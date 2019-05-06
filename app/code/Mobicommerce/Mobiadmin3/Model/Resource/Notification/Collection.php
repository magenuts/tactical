<?php
namespace Mobicommerce\Mobiadmin3\Model\Resource\Notification;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected function _construct()
    {
        $this->_init('mobicommerce_notification');
    }
}