<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Specificcustomer\Grid\Renderer;

class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {	
    
    protected $backendSession;

    public function __construct(
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->backendSession = $backendSession;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
		$specifcustids = $this->backendSession->getData('checked_specificcustomers');
		$specifcust_id = $row->getEntityId();
		$selected ="";
		if (@array_key_exists($specifcust_id, $specifcustids)){
            $selected ="Checked";
		}
		return '<input type="checkbox" onchange="saveSpecificcustomer(this)" class="" '.$selected.' name="specificcustomer[]" value="'.$row->getEntityId().'"/>';
	}
}