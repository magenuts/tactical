<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit\Tab;

class Form extends \Magento\Backend\Block\Widget\Form\Generic {

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
       	$storeId = $applicationData->getAppStoreid();
	   	$appcode = $applicationData->getAppCode();
	   	$applicationKey = $applicationData->getAppKey();

       	//Push Notification section
	   	$fieldset = $form->addFieldset('pushnotification', ['legend'=> __('Push Notification [Website]')]);
	   	
	   	$fieldset->addField('general_label', 'label', [
			'value' => __('For Android, push notifications are activated directly once your mobile app is ready. For iOS version, you need to provide us with UDID number, will generate certificate for activating push notifications. Once approved, it enables sending you push notifications for iOS app as well.'),
			'container_id' => 'mobicommerce_general_label',
        	]);

	   	$fieldset->addField('appcode', 'hidden', [
			'name'     => 'appcode',
			'value'    => $appcode,
			'disabled' => false,
       		]);

	   	$fieldset->addField('appkey', 'hidden', [
			'name'     => 'appkey',
			'value'    => $applicationKey,
			'disabled' => false,
       		]);

		$collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();   
		$pushnotiCollection = $collection->addFieldToFilter('app_code', $appcode)->addFieldToFilter('setting_code','push_notification');
		$PushData = $pushnotiCollection->getData();
		$PushData = $PushData['0'];
		$PushDatas = $PushData['value'];
	   
	   	$pushDataValues = $this->mobiadmin3Helper->_jsonUnserialize($PushDatas);
	   	if($pushDataValues['active_push_notification']){
	      	$activeNotification = true;
	   	}else{
	      	$activeNotification = false;
	   	}
	   	if($pushDataValues['sandboxmode']){
	      	$activeSandBoxMode = true;
	   	}else{
		   	$activeSandBoxMode = false;
	   	}

	   	$fieldset->addField('active_push_notification', 'checkbox', [
			'label'    => __('Activate Push Notification'),
			'name'     => 'pushnotification[active_push_notification]',
			'value'    => '1' ,
			'checked'  => $activeNotification,
			'disabled' => false,
       		]);

	   	$fieldset->addField('sandboxmode', 'checkbox', [
			'label'    => __('Sandbox mode'),
			'name'     => 'pushnotification[sandboxmode]',
			'value'    => '1',
			'checked'  => $activeSandBoxMode,
			'disabled' => false,
			'note'     => __('Please Make sure your 2195 port is open to send IOS push notifications.'),
       		]);

       	$fieldset->addField('android_key', 'text', [
			'label' => __('GCM API Key'),
			'name'  => 'pushnotification[android_key]',
			'value' => $pushDataValues['android_key'],
       		]);

	   	$fieldset->addField('android_sender_id', 'text', [
			'label' => __('Google API Project Number'),
			'name'  => 'pushnotification[android_sender_id]',
			'value' => $pushDataValues['android_sender_id'],
       		]);

	   	$fieldset->addField('upload_iospem_file', 'file', [
			'label'    => __('Upload iOS PEM file'),
			'required' => false,
			'name'     => 'upload_iospem_file',
			'value'    => $pushDataValues['upload_iospem_file'],
			'note'	   => __('Provide PEM file')
       		]);
	   
       	if(!empty($pushDataValues['upload_iospem_file'])){		   
			$fieldset->addField('note', 'note', [
				'label' => '',
				'text'  => $pushDataValues['upload_iospem_file'],
				]);
	  	}

       	$fieldset->addField('appfilename', 'hidden', [
			'name'     => 'upload_iospem_file_name',
			'value'    => $pushDataValues['upload_iospem_file'],
			'disabled' => false,
       		]);
       	
       	/**
       	 * changed by yash - 04-05-2016
		 * made this filed text insead of password
		 * because some browsers change its value when you click on remember password in admin
       	 */
	   	$fieldset->addField('pem_password', 'text', [
			'label' => __('PEM Password'),
			'name'  => 'pushnotification[pem_password]',
			'value' => $pushDataValues['pem_password'],
       		]);
       
		$fieldset          = $form->addFieldset('app_info', ['legend'=>__('Application Information [Website]')]);
		$collection        = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
		$appinfoCollection = $collection->addFieldToFilter('app_code', $appcode)->addFieldToFilter('setting_code','appinfo');
		$appInfoData       = $appinfoCollection->getData();
		$allAppInfo        = $appInfoData['0']['value'];
	   
	   	$allAppInfoValues = $this->mobiadmin3Helper->_jsonUnserialize($allAppInfo);

	   	$fieldset->addField('app_info_label', 'label', [
			'value' => __('The information shared here will be displayed on different social media platforms when someone share this app.'),
			'container_id' => 'mobicommerce_general_label',
        	]);

	   	$fieldset->addField('bundle_id', 'text', [
			'label' => __('Bundle ID'),
			'name'  => 'appinfo[bundle_id]',
			'value' => $allAppInfoValues['bundle_id'],
       		]);

	   	$fieldset->addField('iosappid', 'text', [
			'label' => __('iOS App ID'),
			'name'  => 'appinfo[iosappid]',
			'value' => $allAppInfoValues['iosappid'],
       		]);

	   	$fieldset->addField('android_appname', 'text', [
			'label' => __('App Name on Google Play Store'),
			'name'  => 'appinfo[android_appname]',
			'value' => $allAppInfoValues['android_appname'],
       		]);

	   	$fieldset->addField('android_appweburl', 'text', [
			'label' => __('App URL on Google Play Store'),
			'name'  => 'appinfo[android_appweburl]',
			'value' => $allAppInfoValues['android_appweburl'],
       		]);

	   	$fieldset->addField('ios_appname', 'text', [
			'label' => __('App Name on Apple Store'),
			'name'  => 'appinfo[ios_appname]',
			'value' => $allAppInfoValues['ios_appname'],
       		]);

	   	$fieldset->addField('ios_appweburl', 'text', [
			'label' => __('App URL on Apple Store'),
			'name'  => 'appinfo[ios_appweburl]',
			'value' => $allAppInfoValues['ios_appweburl'],
       		]);

	   	$fieldset->addField('app_description', 'textarea', [
			'label' => __('Application Description'),
			'name'  => 'appinfo[app_description]',
			'value' => $allAppInfoValues['app_description'],
       		]);

	   	$fieldset->addField('app_share_image', 'image', [
			'label'    => __('Application Image'),
			'required' => false,
			'name'     => 'app_share_image',
			'value'    => $allAppInfoValues['app_share_image'],
			'note'	   => __('Supported Filetypes: png, jpg, jpeg')
       		]);

       	return parent::_prepareForm();
   	}
}