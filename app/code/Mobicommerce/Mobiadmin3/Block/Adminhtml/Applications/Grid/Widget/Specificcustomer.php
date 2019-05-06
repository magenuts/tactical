<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Grid\Widget;
class Specificcustomer extends \Magento\Backend\Block\Widget\Grid\Container {

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
        $this->_controller = 'adminhtml_widget_specificcustomer';
        parent::_construct();
        //$this->_removeButton('add');
    }
}