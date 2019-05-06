<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Category\Grid\Renderer;

class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {    
    protected $backendSession;

    public function __construct(
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->backendSession = $backendSession;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $catids = $this->backendSession->getData('checked_categories');
        
		$cat_id = $row->getEntityId();
		$checked ="";
		if (@array_key_exists($cat_id, $catids)){
            $checked = "checked";
		}
		return '<input type="checkbox"  onchange="saveCategory(this)" '.$checked.' value="'.$cat_id.'"/>';
    }
}