<?php
namespace Mobicommerce\Mobiadmin3\Model\Resource\Categorywidget;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
	
    protected function _construct()
    {
        $this->_init('Mobicommerce\Mobiadmin3\Model\Categorywidget','Mobicommerce\Mobiadmin3\Model\Resource\Categorywidget');
    }
}