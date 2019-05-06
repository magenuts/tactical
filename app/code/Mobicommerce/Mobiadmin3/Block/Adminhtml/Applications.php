<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml;

class Applications extends \Magento\Backend\Block\Widget\Grid\Container {

    protected $urlBuilder;
	public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $data = [])
	{
        $this->urlBuilder = $urlBuilder;
		$this->_headerText = __('Manage Mobile Apps');
        $this->_addButtonLabel = __('Create New Mobile App');
		parent::__construct($context,$data);
        $this->removeButton('add');
        
		$this->buttonList->add(
            'savewidget',
            [
                'label' => __('Create New Mobile App'),
                'class' => 'primary',
                'onclick'=>"setLocation('" . $this->getUrl('*/*/add', ['page_key' => 'collection']) . "')",
                
            ],
            300
        );
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
        $this->_controller = 'adminhtml_applications';
        parent::_construct();
    }
   
    protected function _prepareLayout() 
	{
		parent::_prepareLayout();
	}   
}