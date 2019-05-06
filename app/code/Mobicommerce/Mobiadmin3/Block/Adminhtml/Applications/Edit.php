<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications;

class Edit extends \Magento\Backend\Block\Widget\Form\Container {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    protected $_storeManager;
    protected $_coreRegistry = null;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->registry = $registry;
        $this->_storeManager = $storeManager;
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
        $this->_controller = 'adminhtml_applications';
        parent::_construct();
        $this->removeButton('reset');
        $this->removeButton('delete');
    }
    
    /**
     * Return save url for edit form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/edit', ['_current' => true, 'back' => null]);
    }
    
	public function getHeaderText()
    {
        if( $this->registry->registry('application_data') && $this->registry->registry('application_data')->getId()){
            return $this->__('Edit App Settings - ').$this->htmlEscape(
            $this->registry->registry('application_data')->getAppName()).'<br />';
        }
        else{
            return 'Add a application';
        }
    }
    
    public function getRegistry()
    {
        return $this->registry;
    }
        
    public function getStoreManager()
    {
        return $this->_storeManager;
    }
    
	protected function _prepareLayout() 
	{
		parent::_prepareLayout();
	}
}