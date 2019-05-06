<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit\Tab;

class Googleanalytics extends \Magento\Backend\Block\Widget\Form\Generic {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory
     */
    protected $mobiadmin3ResourceAppsettingCollectionFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory $mobiadmin3ResourceAppsettingCollectionFactory,
        \Magento\Framework\Data\FormFactory $formFactory
    ) {
        $data = [];
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->mobiadmin3ResourceAppsettingCollectionFactory = $mobiadmin3ResourceAppsettingCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
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

        $analytics_data = [];
        $collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
        $collection->addFieldToFilter('app_code', $appcode)->addFieldToFilter('setting_code','googleanalytics');
        if($collection->getSize() > 0){
            $analytics_data = @unserialize($collection->getFirstItem()->getValue());
        }

	   	$fieldset = $form->addFieldset('googleanalytics_android', ['legend'=> __('Android')]);

	   	$fieldset->addField(
            'analyticsSettings_android_status',
            'select',
            [
                'name' => 'analyticsSettings[android][status]',
                'label' => __('Enable Android Analytics'),
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                    ],
                'value' => $analytics_data['android']['status']
            ]
        );

        $fieldset->addField(
            'analyticsSettings_android_code',
            'text',
            [
                'name' => 'analyticsSettings[android][code]',
                'label' => __('Android Analytics Code'),
                'title' => __('Enable Android Analytics'),
                'value' => $analytics_data['android']['code']
            ]
        );

        $fieldset = $form->addFieldset('googleanalytics_ios', ['legend'=> __('iOS')]);

        $fieldset->addField(
            'analyticsSettings_ios_status',
            'select',
            [
                'name' => 'analyticsSettings[ios][status]',
                'label' => __('Enable iOS Analytics'),
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                    ],
                'value' => $analytics_data['ios']['status']
            ]
        );

        $fieldset->addField(
            'analyticsSettings_ios_code',
            'text',
            [
                'name' => 'analyticsSettings[ios][code]',
                'label' => __('iOS Analytics Code'),
                'title' => __('Enable iOS Analytics'),
                'value' => $analytics_data['ios']['code']
            ]
        );
        /*
        $this->setChild('form_after', $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Widget\Form\Element\Dependence::class)
            ->addFieldMap('analyticsSettings_android_status', 'ga_android_select_field')
            ->addFieldMap('analyticsSettings_android_code', 'ga_android_field_to_hide')
            ->addFieldMap('analyticsSettings_ios_status', 'ga_ios_select_field')
            ->addFieldMap('analyticsSettings_ios_code', 'ga_ios_field_to_hide')
            ->addFieldDependence('ga_android_field_to_hide', 'ga_android_select_field', 1)
            ->addFieldDependence('ga_ios_field_to_hide', 'ga_ios_select_field', 1)
        );
        */
       	return parent::_prepareForm();
   	}
}