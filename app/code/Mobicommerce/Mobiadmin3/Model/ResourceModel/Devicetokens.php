<?php

namespace Mobicommerce\Mobiadmin3\Model\ResourceModel;

class Devicetokens extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init('mobicommerce_devicetokens', 'md_id');
	}
}