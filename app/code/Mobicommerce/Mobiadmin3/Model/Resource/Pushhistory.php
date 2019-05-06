<?php
namespace Mobicommerce\Mobiadmin3\Model\Resource;


class Pushhistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	
    protected function _construct()
    {
        $this->_init('mobicommerce_pushhistory', 'id');
    }
}