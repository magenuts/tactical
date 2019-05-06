<?php
namespace Mobicommerce\Mobiadmin3\Model\Resource;

class Notification extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	
    protected function _construct()
    {
        $this->_init('mobicommerce_notification', 'id');
    }
}