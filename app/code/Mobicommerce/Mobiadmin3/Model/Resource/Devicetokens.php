<?php
namespace Mobicommerce\Mobiadmin3\Model\Resource;

class Devicetokens extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    protected function _construct()
    {
        $this->_init('mobicommerce_devicetokens', 'md_id');
    }
}