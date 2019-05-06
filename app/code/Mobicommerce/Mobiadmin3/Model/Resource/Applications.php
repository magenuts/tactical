<?php
namespace Mobicommerce\Mobiadmin3\Model\Resource;

class Applications extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	
    protected function _construct()
    {
        $this->_init('mobicommerce_applications3', 'id');
    }
}