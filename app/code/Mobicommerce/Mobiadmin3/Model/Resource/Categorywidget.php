<?php
namespace Mobicommerce\Mobiadmin3\Model\Resource;

class Categorywidget extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	
    protected function _construct()
    {
        $this->_init('mobicommerce_category_widget3', 'widget_id');
    }
}