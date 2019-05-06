<?php

namespace Mobicommerce\Mobiadmin3\Model\ResourceModel;

class Pushhistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init('mobicommerce_pushhistory', 'id');
	}
}