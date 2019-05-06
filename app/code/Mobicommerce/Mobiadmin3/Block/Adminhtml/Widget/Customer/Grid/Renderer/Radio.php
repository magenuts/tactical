<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Customer\Grid\Renderer;
class Radio extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {	
	
    public function render(\Magento\Framework\DataObject $row)
    {
    	$custids = Mage::getModel('core/session')->getData('checked_customers');
		$cust_id = $row->getCustomerGroupId();
		$selected ="";
		if (array_key_exists($cust_id, $custids)){
            $selected ="Checked";
		}
		return '<input type="radio" class=""  value="'.$row->getCustomerGroupId().'" '.$selected.' name="radiochecked" onclick="saveCustomer(this);" />';
	}
}