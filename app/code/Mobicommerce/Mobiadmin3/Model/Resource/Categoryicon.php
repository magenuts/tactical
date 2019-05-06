<?php
namespace Mobicommerce\Mobiadmin3\Model\Resource;

class Categoryicon extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	
    protected function _construct()
    {
        $this->_init('mobicommerce_category_icon3', 'mci_id');
    }
}