<?php
namespace Mobicommerce\Mobiadmin3\Model\Resource;

class Widget extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	
    protected function _construct()
    {
        $this->_init('mobicommerce_widget3', 'widget_id');
    }
}