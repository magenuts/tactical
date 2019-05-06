<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Product\Grid\Renderer;

class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer 
{
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
		$productids = $this->backendSession->getData('checked_products');
		$prod_id = $row->getEntityId();
		$selected = "";
		if (@array_key_exists($prod_id, $productids)){
            $selected ="Checked";
		}
		return '<input type="checkbox" onchange="saveProduct(this)" class="" '.$selected.' value="'.$prod_id.'"/>';
	}
}