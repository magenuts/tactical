<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Grid\Widget\Deeplink;

class Category extends \Magento\Backend\Block\Widget\Grid\Container {
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,        
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
    
    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Mobicommerce_Mobiadmin3';
        $this->_controller = 'adminhtml_widget_deeplink_category';
        parent::_construct();
        //$this->_removeButton('add');
    }
}