<?php
namespace Mobicommerce\Mobiadmin3\Model\Resource;

class Licence extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	
    protected function _construct()
    {
        $this->_init('mobicommerce_licence', 'ml_id');
    }
}