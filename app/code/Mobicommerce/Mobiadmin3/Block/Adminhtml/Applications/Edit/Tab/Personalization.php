<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit\Tab;

class Personalization extends \Magento\Backend\Block\Widget\Form\Generic {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Mobicommerce\Mobiadmin3\Helper\Data
     */
    protected $mobiadmin3Helper;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;
    protected $_directoryList;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Mobicommerce\Mobiadmin3\Helper\Data $mobiadmin3Helper,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $data = [];
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->mobiadmin3Helper = $mobiadmin3Helper;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_directoryList = $directoryList;
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
       	$form = $this->formFactory->create();
       	$this->setForm($form);
       	$applicationData = $this->registry->registry('application_data');
	   	$appcode = $applicationData->getAppCode();

        $themeName = $this->mobiadmin3Helper->getThemeName($appcode);
        $file_personalizer_parent = $this->_directoryList->getPath('media').'/mobi_assets/v/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/theme_files/'.$themeName.'/personalizer'.'/'.'personalizer.xml';
        $file_personalizer_child = $this->_directoryList->getPath('media').'/mobi_commerce/'.$appcode.'/personalizer/'.'personalizer.xml';

	   	$fieldset = $form->addFieldset('personalization_android', ['legend'=> __('Android')]);
        $fieldset->addField(
            'personalization_helptext',
            'note',
            [
                'name' => 'personalization_helptext',
                'label' => '',
                'text' => 'Personalize color scheme of the mobile app. You have to restart the app to get reflection of the new color scheme in the app.',
                'bold' => true
            ]
        );

        if(file_exists($file_personalizer_parent) && file_exists($file_personalizer_child))
        {
            $code_personalizer_parent = simplexml_load_file($file_personalizer_parent) or die("Error: Cannot create object");
            $code_personalizer_child = simplexml_load_file($file_personalizer_child);
            foreach($code_personalizer_parent as $option => $value)
            {
                if($value->group == 'android')
                {
                    $current_value = (string) isset($code_personalizer_child->$option->current_value) ? $code_personalizer_child->$option->current_value : '';

                    $options = [];
                    foreach ($value->options->option as $value_options) {
                        $_key = (string)$value_options->value;
                        $_value = (string)$value_options->label;

                        $options[$_key] = $_value;
                    }
                    
                    $fieldset->addField(
                        'personalizer_'.$option,
                        'select',
                        [
                            'name' => 'personalizer['.$option.']',
                            'label' => __($value->title),
                            'options' => $options,
                            'value' => $current_value
                        ]
                    );
                }
            }

            $fieldset = $form->addFieldset('personalization_ios', ['legend'=> __('iOS')]);

            foreach($code_personalizer_parent as $option => $value)
            {
                if($value->group == 'ios')
                {
                    $current_value = (string) isset($code_personalizer_child->$option->current_value) ? $code_personalizer_child->$option->current_value : '';

                    $options = [];
                    foreach ($value->options->option as $value_options) {
                        $_key = (string)$value_options->value;
                        $_value = (string)$value_options->label;

                        $options[$_key] = $_value;
                    }
                    
                    $fieldset->addField(
                        'personalizer_'.$option,
                        'select',
                        [
                            'name' => 'personalizer['.$option.']',
                            'label' => __($value->title),
                            'options' => $options,
                            'value' => $current_value
                        ]
                    );
                }
            }
        }

       	return parent::_prepareForm();
   	}
}