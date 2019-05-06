<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Deeplink\Cms\Grid\Renderer;

class Radio extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
    	$link_type_value = $this->request->getParam('link_type_value');
        $radiochecked = "";
    	if(!empty($link_type_value)){
            if($link_type_value == $row->getPageId()){
                $radiochecked = 'checked="checked"';
            }
    	}
		return '<input type="radio"  value="'.$row->getPageId().'" name="radiochecked" '.$radiochecked.'/>';
	}
}