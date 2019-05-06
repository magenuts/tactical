<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit\Tab;

class Overview extends \Magento\Backend\Block\Widget\Form {
	
    protected $registry;
   
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry
    )
    {
        $this->registry = $registry;
        parent::__construct($context);
        $this->setTemplate('mobiadmin3/application/edit/tab/overview.phtml');
    }
    
    public function getRegistry()
    {
        return $this->registry;
    }
}