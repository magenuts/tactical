<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Labelsmessages;

class Edit extends \Magento\Backend\Block\Widget\Form\Container {
	
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
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
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
        $this->_controller = 'adminhtml_labelsmessages';
        parent::_construct();
    }
    
    protected function _preparelayout()
    {
        $this->buttonList->add(
            'save_and_edit_button',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            100
        );
        
        return parent::_prepareLayout();
    }
    
    /**
     * Return save url for edit form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/labelsmessages', ['_current' => true, 'back' => null]);
    }
    
	public function getHeaderText()
    {
        return __('Define Labels or local messages according to your audience');
    }
}