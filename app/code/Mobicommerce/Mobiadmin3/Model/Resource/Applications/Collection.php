<?php
namespace Mobicommerce\Mobiadmin3\Model\Resource\Applications;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected function _construct()
    {
        $this->_init('Mobicommerce\Mobiadmin3\Model\Applications','Mobicommerce\Mobiadmin3\Model\Resource\Applications');
    }
}