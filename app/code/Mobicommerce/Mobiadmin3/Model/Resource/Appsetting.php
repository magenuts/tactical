<?php
namespace Mobicommerce\Mobiadmin3\Model\Resource;


class Appsetting extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	
    protected function _construct()
    {
        $this->_init('mobicommerce_applications_settings3', 'id');
    }
}