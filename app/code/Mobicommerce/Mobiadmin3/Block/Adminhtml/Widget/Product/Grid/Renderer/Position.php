<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Product\Grid\Renderer;

class Position extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

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
        $prod_id = $row->getEntityId();
		$products = $this->backendSession->getData('checked_products');
        $prod_pos = @$products[$prod_id];
		if(empty($prod_pos)){
			$prod_pos = 0;
		}
		return '<input type="text" onchange="savePosition(this)" class="input-text product-position" value="'.$prod_pos.'" style="width:50%;" data-productid ="'.$prod_id.'" />';
	}
}