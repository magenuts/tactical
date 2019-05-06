<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit\Tab;

class Cms extends \Magento\Backend\Block\Widget\Form\Generic {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory
     */
    protected $mobiadmin3ResourceAppsettingCollectionFactory;

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
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory $mobiadmin3ResourceAppsettingCollectionFactory,
        \Mobicommerce\Mobiadmin3\Helper\Data $mobiadmin3Helper,
        \Magento\Framework\Data\FormFactory $formFactory
    ) {
        $data = [];
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->request = $request;
        $this->mobiadmin3ResourceAppsettingCollectionFactory = $mobiadmin3ResourceAppsettingCollectionFactory;
        $this->mobiadmin3Helper = $mobiadmin3Helper;
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
       	$storeId = $this->getRequest()->getParam('store');
	   	$appcode = $applicationData->getAppCode();
	   	$applicationKey = $applicationData->getAppKey();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $cms_settings_data = [];
        $collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
        $collection->addFieldToFilter('app_code', $appcode)->addFieldToFilter('setting_code','cms_settings');
        
        $collection->addFieldToFilter('storeid', $storeId);

        if($collection->getSize() > 0){
            $cms_settings_data = @unserialize($collection->getFirstItem()->getValue());
        }
        
	   	$fieldset = $form->addFieldset('company_information', ['legend'=> __('Company Information')]);

	   	$fieldset->addField(
            'contact_information[company_name]',
            'text',
            [
                'name' => 'contact_information[company_name]',
                'label' => __('Company Name'),
                'title' => __('Company Name'),
                'value' => $cms_settings_data['contact_information']['company_name'],
                'required' => true
            ]
        );

	   	$fieldset->addField(
            'contact_information[company_address]',
            'textarea',
            ['name' => 'contact_information[company_address]', 'label' => __('Address'), 'title' => __('Address'), 'value' => $cms_settings_data['contact_information']['company_address']]
        );

        $fieldset->addField(
            'contact_information[phone_number]',
            'text',
            ['name' => 'contact_information[phone_number]', 'label' => __('Phone Number'), 'title' => __('Phone Number'), 'class' => '__validate-phoneStrict', 'value' => $cms_settings_data['contact_information']['phone_number']]
        );

        $fieldset->addField(
            'contact_information[email_address]',
            'text',
            ['name' => 'contact_information[email_address]', 'label' => __('Email Address'), 'title' => __('Email Address'), 'class' => 'validate-email', 'value' => $cms_settings_data['contact_information']['email_address']]
        );

        $fieldset->addField(
            'contact_information[menu_icon]',
            'file',
            ['name' => 'contact_information[menu_icon]', 'label' => __('Left Menu Icon'), 'title' => __('Left Menu Icon')]
        );        

        $fieldset->addField(
            'company_latlontext',
            'note',
            ['name' => 'company_latlontext', 'label' => '', 'text' => 'Set your store "Latitude" and "Longitude" if you wish to show your store on google map','bold' => true]
        );

        $fieldset->addField(
            'contact_information[latitude]',
            'text',
            ['name' => 'contact_information[latitude]', 'label' => __('Latitude'), 'title' => __('Latitude'), 'class' => 'validate-number', 'value' => $cms_settings_data['contact_information']['latitude']]
        );

        $fieldset->addField(
            'contact_information[longitude]',
            'text',
            ['name' => 'contact_information[longitude]', 'label' => __('Longitude'), 'title' => __('Longitude'), 'class' => 'validate-number', 'value' => $cms_settings_data['contact_information']['longitude']]
        );

        $fieldset->addField(
            'contact_information[zoom_level]',
            'text',
            ['name' => 'contact_information[zoom_level]', 'label' => __('Zoom Level'), 'title' => __('Zoom Level'), 'class' => 'validate-number-range number-range-1-15', 'value' => $cms_settings_data['contact_information']['zoom_level']]
        );

        $fieldset->addField(
            'contact_information[pin_color]',
            'text',
            ['name' => 'contact_information[pin_color]', 'label' => __('Map Pin Color'), 'title' => __('Map Pin Color'), 'value' => $cms_settings_data['contact_information']['pin_color']]
        );

        $fieldset = $form->addFieldset('socialmedia_fieldset', ['legend'=> __('Social Media URLs [STORE VIEW]')]);

        $fieldset->addField(
            'social_media_helptext',
            'note',
            ['name' => 'social_media_text', 'label' => '', 'text' => 'If you have your social media accounts/pages, than activate respective social media plate form and Supply their URL. Activated plate form icons will be displayed on Info section page.','bold' => true]
        );

        $fieldset->addType(
            'mobicommerce_checkbox_with_textbox',
            '\Mobicommerce\Mobiadmin3\Block\Adminhtml\Customformfield\Edit\Renderer\Checkboxwithtextbox'
        );

        $social_icons = [
            "facebook"   => ["img" => "soci-facebook.gif"],
            "twitter"    => ["img" => "soci-twitter.gif"],
            "linkedin"   => ["img" => "soci-linkedin.gif"],
            "pinterest"  => ["img" => "soci-pinterest.gif"],
            "youtube"    => ["img" => "soci-youtube.gif"],
            "blog"       => ["img" => "soci-blog.gif"],
            "googleplus" => ["img" => "soci-googleplus.gif"],
            "instagram"  => ["img" => "soci-instagram.gif"],
            "telegram"   => ["img" => "soci-telegram.png"],
            ];

        foreach($social_icons as $_icon_name => $_icon) {
            $fieldset->addField(
                'social_media_'.$_icon_name.'_group',
                'mobicommerce_checkbox_with_textbox',
                [
                    'name' => 'social_media_'.$_icon_name.'_group',
                    'image_label' => $this->getViewFileUrl('Mobicommerce_Mobiadmin3/mobiadmin3/images/'.$_icon['img']),
                    'title' => $_icon_name,
                    'class' => 'validate-url',
                    'value' => 1,
                    'textbox_name' => 'social_media['.$_icon_name.'][url]',
                    'textbox_value' => isset($cms_settings_data['social_media'][$_icon_name]['url']) ? $cms_settings_data['social_media'][$_icon_name]['url'] : '',
                    'checkbox_name' => 'social_media['.$_icon_name.'][checked]',
                    'checkbox_value' => (isset($cms_settings_data['social_media'][$_icon_name]['checked']) && $cms_settings_data['social_media'][$_icon_name]['checked']) ? '1' : '0'
                ]
            );
        }

        $fieldset = $form->addFieldset('cmspages_fieldset', ['legend'=> __('CMS Pages [STORE VIEW]')]);

        $fieldset->addField(
            'cmspages_helptext',
            'note',
            ['name' => 'cmspages_helptext', 'label' => '', 'text' => 'Select all pages which you want to show activate or show in Mobile app and set their sequence/order number.','bold' => true]
        );

        $cmsCollection = $objectManager->create('Magento\Cms\Model\Page')->getCollection()
            ->addFieldToFilter('is_active', 1);

        if($cmsCollection)
        {
            $fieldset->addType(
                'mobicommerce_checkbox_with_textbox',
                '\Mobicommerce\Mobiadmin3\Block\Adminhtml\Customformfield\Edit\Renderer\Checkboxwithtextbox'
            );

            $cms_pages_saved_data = [];
            if($cms_settings_data['cms_pages'])
            {
                foreach($cms_settings_data['cms_pages'] as $_cmsdata)
                {
                    $cms_pages_saved_data[$_cmsdata['id']] = $_cmsdata['index'];
                }
            }

            foreach($cmsCollection as $_cmspage)
            {
                //echo '<pre>';print_r($cms_settings_data);exit;
                $fieldset->addField(
                    'cmspage_'.$_cmspage->getPageId().'_group',
                    'mobicommerce_checkbox_with_textbox',
                    [
                        'name' => 'cmspages_'.$_cmspage->getPageId().'_group',
                        'label' => $_cmspage->getTitle(),
                        'class' => 'validate-number validate-not-negative-number',
                        'value' => 1,
                        'textbox_name' => 'cms_pages[index]['.$_cmspage->getPageId().']',
                        'textbox_value' => isset($cms_pages_saved_data[$_cmspage->getPageId()]) ? $cms_pages_saved_data[$_cmspage->getPageId()] : '',
                        'checkbox_name' => 'cms_pages[status]['.$_cmspage->getPageId().']',
                        'checkbox_value' => (isset($cms_pages_saved_data[$_cmspage->getPageId()])) ? '1' : '0'
                    ]
                );
            }
        }

       	return parent::_prepareForm();
   	}
}