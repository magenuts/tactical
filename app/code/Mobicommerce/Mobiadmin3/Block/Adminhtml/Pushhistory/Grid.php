<?php

namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Pushhistory;

class Grid extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_pushhistory';
        $this->_blockGroup = 'Mobicommerce_Mobiadmin3';
        parent::_construct();
    }
}