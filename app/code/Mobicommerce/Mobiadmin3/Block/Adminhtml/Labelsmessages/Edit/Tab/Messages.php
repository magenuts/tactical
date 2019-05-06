<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Labelsmessages\Edit\Tab;

class Messages extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
 {

    /**
     * @var \Mobicommerce\Mobiadmin3\Helper\Data
     */
    protected $mobiadmin3Helper;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Mobicommerce\Mobiadmin3\Helper\Data $mobiadmin3Helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory
    ) {
        $data = [];
        $this->formFactory = $formFactory;
        $this->mobiadmin3Helper = $mobiadmin3Helper;
        parent::__construct($context, $registry, $formFactory,$data);
    }
    
    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }
 
    protected function _prepareForm()
    {
	    $form = $this->formFactory->create(['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]);
        
        $fieldset2 = $form->addFieldset('messages_data', ['legend' => __('Message')]);
        $locale = $this->getRequest()->getParam('lang_code', null);
        if($locale==null)
            $locale = "en_US";
        
        $labels = $this->mobiadmin3Helper->getLanguageData($locale);
        
		foreach ($labels as $key => $label) {
			if($label['mm_type'] == 'message') {
				$fieldset2->addField('message-label'.$key, 'text', [
					'label'      => __($label['mm_label']),
					'required'   => false,
					'name'       => "language_data[".$key."]",
					'value'      => isset($childlabels[$key]['mm_text']) ? $childlabels[$key]['mm_text'] : $label['mm_text'],
					'maxlength'  => $label['mm_maxlength'],
					]);
			}
		}
        $this->setForm($form);
	}

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel()
    {
        return __('Messages');
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        return __('Messages');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden()
    {
        return false;
    }
}