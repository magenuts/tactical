<?php

namespace Mobicommerce\Mobiadmin3\Model\ResourceModel\Devicetokens;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'md_id';

	protected function _construct()
	{
		$this->_init('Mobicommerce\Mobiadmin3\Model\Devicetokens', 'Mobicommerce\Mobiadmin3\Model\ResourceModel\Devicetokens');
	}

	protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['customer_entity' => $this->getTable('customer_entity')],
            'main_table.md_userid = customer_entity.entity_id',
            ['email']
            );
    }
}