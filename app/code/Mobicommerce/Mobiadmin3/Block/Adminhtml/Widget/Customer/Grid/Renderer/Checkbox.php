<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Customer\Grid\Renderer;
class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {	

	/**
	* @var \Magento\Framework\Registry
	*/
	protected $registry;
	protected $backendSession;

	public function __construct(
		\Magento\Framework\Registry $registry,
		\Magento\Backend\Model\Session $backendSession
	) {
		$this->registry = $registry;
		$this->backendSession = $backendSession;
	}

	public function render(\Magento\Framework\DataObject $row)
	{
		$custids = $this->backendSession->getData('checked_customers');
		$cust_id = $row->getCustomerGroupId();
		$selected ="";
		if (@array_key_exists($cust_id, $custids)){   
			$selected ="Checked";
		}
		return '<input type="checkbox" onchange="saveCustomer(this)" class="" '.$selected.' name="customer_groupId[]" value="'.$row->getCustomerGroupId().'"/>';
	}
}