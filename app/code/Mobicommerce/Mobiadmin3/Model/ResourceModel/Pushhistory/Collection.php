<?php

namespace Mobicommerce\Mobiadmin3\Model\ResourceModel\Pushhistory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';

	protected function _construct()
	{
		$this->_init('Mobicommerce\Mobiadmin3\Model\Pushhistory', 'Mobicommerce\Mobiadmin3\Model\ResourceModel\Pushhistory');
	}
}