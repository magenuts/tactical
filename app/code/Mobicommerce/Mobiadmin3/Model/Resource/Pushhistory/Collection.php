<?php
namespace Mobicommerce\Mobiadmin3\Model\Resource\Pushhistory;

//class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
        

    protected function _construct()
    {
            //$this->_init('mobicommerce_pushhistory');
            $this->_init('Mobicommerce\Mobiadmin3\Model\Pushhistory','Mobicommerce\Mobiadmin3\Model\Resource\Pushhistory');
    }
}