<?php
namespace Mobicommerce\Mobiadmin3\Model\Resource\Appsetting;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected function _construct()
    {
        $this->_init('Mobicommerce\Mobiadmin3\Model\Appsetting','Mobicommerce\Mobiadmin3\Model\Resource\Appsetting');
    }
}