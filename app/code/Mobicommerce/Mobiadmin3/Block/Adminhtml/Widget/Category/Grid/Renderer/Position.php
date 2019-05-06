<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Category\Grid\Renderer;

class Position extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {
    protected $backendSession;

    public function __construct(
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->backendSession = $backendSession;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $categories = $this->backendSession->getData('checked_categories');
		$cate_id = $row->getEntityId();
   
        $cat_pos = @$categories[$cate_id];
		if(empty($cat_pos)){
			$cat_pos = 0;
		}

		return '<input type="text" onchange="savePosition(this)" class="input-text category-pos" style="width:50%;" data-categoryid ="'.$cate_id.'" value="'.$cat_pos.'"/>';
	}
}