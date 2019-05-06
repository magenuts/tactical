<?php

namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Pushhistory;

class View extends \Magento\Backend\Block\Widget\Form\Container
{
	protected $_coreRegistry;

    protected function _construct()
    {
        $this->_controller = 'adminhtml_pushhistory';
        $this->_blockGroup = 'Mobicommerce_Mobiadmin3';
        $this->_mode = 'view';
        parent::_construct();

        $this->removeButton('delete');
        $this->removeButton('reset');
        $this->removeButton('save');
    }

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function getNotificationDetail()
    {
    	return $this->_coreRegistry->registry('mobicommerce_pushnotification');
    }

    public function getDeviceType($type)
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$object = $objectManager->create('Mobicommerce\Mobiadmin3\Model\Config\Source\Devicetype');
    	$options = $object->toOptionArray();
    	foreach($options as $_option){
    		if($_option['value'] == $type) {
    			return $_option['label'];
    		}
    	}
    }

    public function getSendToType($type)
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$object = $objectManager->create('Mobicommerce\Mobiadmin3\Model\Config\Source\Sendto');
    	$options = $object->toOptionArray();
    	foreach($options as $_option){
    		if($_option['value'] == $type) {
    			return $_option['label'];
    		}
    	}
    }
}