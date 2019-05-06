<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Application;
use Magento\Store\Model\Group;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Licence\CollectionFactory
     */
    protected $mobiadmin3ResourceLicenceCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\ApplicationsFactory
     */
    protected $mobiadmin3ApplicationsFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory
     */
    protected $mobiadmin3ResourceAppsettingCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\AppsettingFactory
     */
    protected $mobiadmin3AppsettingFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Applications\CollectionFactory
     */
    protected $mobiadmin3ResourceApplicationsCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\WidgetFactory
     */
    protected $mobiadmin3WidgetFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\PushhistoryFactory
     */
    protected $mobiadmin3PushhistoryFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Devicetokens\CollectionFactory
     */
    protected $mobiadmin3ResourceDevicetokensCollectionFactory;

    /**
     * @var \Magento\Framework\File\UploaderFactory
     */
    protected $uploaderFactory;
    protected $_storeManager;
    protected $_resultPageFactory;
    protected $_customerSession;
    protected $_mobiadmin3Helper;
    protected $messageManager;
    protected $_dir;
    
	public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Mobicommerce\Mobiadmin3\Helper\Data $mobiadmin3Helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Mobicommerce\Mobiadmin3\Model\Resource\Licence\CollectionFactory $mobiadmin3ResourceLicenceCollectionFactory,
        \Mobicommerce\Mobiadmin3\Model\ApplicationsFactory $mobiadmin3ApplicationsFactory,
        \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory $mobiadmin3ResourceAppsettingCollectionFactory,
        \Mobicommerce\Mobiadmin3\Model\AppsettingFactory $mobiadmin3AppsettingFactory,
        \Mobicommerce\Mobiadmin3\Model\Resource\Applications\CollectionFactory $mobiadmin3ResourceApplicationsCollectionFactory,
        \Mobicommerce\Mobiadmin3\Model\WidgetFactory $mobiadmin3WidgetFactory,
        \Mobicommerce\Mobiadmin3\Model\PushhistoryFactory $mobiadmin3PushhistoryFactory,
        \Mobicommerce\Mobiadmin3\Model\Resource\Devicetokens\CollectionFactory $mobiadmin3ResourceDevicetokensCollectionFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
        $this->mobiadmin3ResourceLicenceCollectionFactory = $mobiadmin3ResourceLicenceCollectionFactory;
        $this->mobiadmin3ApplicationsFactory = $mobiadmin3ApplicationsFactory;
        $this->mobiadmin3ResourceAppsettingCollectionFactory = $mobiadmin3ResourceAppsettingCollectionFactory;
        $this->mobiadmin3AppsettingFactory = $mobiadmin3AppsettingFactory;
        $this->mobiadmin3ResourceApplicationsCollectionFactory = $mobiadmin3ResourceApplicationsCollectionFactory;
        $this->mobiadmin3WidgetFactory = $mobiadmin3WidgetFactory;
        $this->mobiadmin3PushhistoryFactory = $mobiadmin3PushhistoryFactory;
        $this->mobiadmin3ResourceDevicetokensCollectionFactory = $mobiadmin3ResourceDevicetokensCollectionFactory;
        
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_mobiadmin3Helper = $mobiadmin3Helper;
        $this->mobiadmin3ApplicationsFactory = $mobiadmin3ApplicationsFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->_dir = $dir;      
    }
    
    public function getCoreModel($modelPath)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection
        $productCollection */
        $model = $objectManager->create($modelPath);
        /** Apply filters here */
        return $model;
    }
    
	public function execute()
	{
    	$this->_mobiadmin3Helper->getMobicommercePrerequisites();
		$id = $this->getRequest()->getParam('id', null);
        $urlStoreid = $this->getRequest()->getParam('store', null);
		$model = $this->mobiadmin3ApplicationsFactory->create();
		if($id){
			$model->load((int) $id);
            if ($model->getId()){
                $data = $this->_customerSession->getFormData(true);
                if($data){
                    $model->setData($data)->setId($id);
                }
            }
			else{
                $this->messageManager->addError(__('Application does not exist'));
                $this->_redirect('*/*/');
            }
		}

		$storeid = $this->_storeManager->getStore()->getId();
		$groupid = $model->getAppStoregroupid();
		$stores = $this->_storeManager->getStores();

		if(empty($urlStoreid)){
            $url = $this->getUrl('mobicommerce/application/edit', ['id' => $id, 'store' => $storeid]);
			$resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($url);
            return $resultRedirect;
		}

		$storeExists = $this->_storeManager->getStore($storeid)->getId();
		if(!$storeExists){
			$this->messageManager->addError(__('Store does not exist'));
            $this->_redirect('*/*/');
		}
		if($this->getRequest()->getPost('form_key')){
			$this->update();
		}
		else{
			$this->_coreRegistry->register('application_data', $model);
			$locale = $this->_mobiadmin3Helper->getAppLocaleCode();
			if($locale){
				$this->_setLanguageCode($locale);
			}
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->setActiveMenu('Mobicommerce_Mobiadmin3::applicationIndex');
            if($model->getAppName())
            {
               $resultPage->getConfig()->getTitle()->prepend( $model->getAppName()." ".__('App Management')); 
            }
            else
            {
                $resultPage->getConfig()->getTitle()->prepend(__('App Management'));
            }
            return $resultPage;
		}
	}

    public function _setLanguageCode($locale)
	{
       	$this->_mobiadmin3Helper->setLanguageCodeData($locale);
	}

    public function update()
    {
		if($this->getRequest()->getPost()){
			$storeid = $this->getRequest()->getParam('store', null);
            $appid = $this->getRequest()->getParam('id');
            $postData = $this->getRequest()->getPost();
            
			$appCode = $postData['appcode'];
			$appKey = $postData['appkey'];
            $errors = false;

            /**  Validation for Image Widget **/
            $validation = true;
			if(!empty($_FILES)) {
				if(isset($_FILES['widget_image']['name']) && !empty($_FILES['widget_image']['name'])) {
					$allowed =  ['png' ,'jpg'];
					$ext = strtolower(PATHINFO($_FILES['widget_image']['name'], PATHINFO_EXTENSION));
					if(!in_array($ext, $allowed)) {
						$this->messageManager->addError(__('Widget Image File type must be image( png ,jpg )'));
						$validation = false;
					}
				}
				if(!$validation){
					Mage::app()->getFrontController()->getResponse()->setRedirect($refererUrl);
					return;
				}
			}
			/**  Validation for Image Widget **/
            $this->_mobiadmin3Helper->saveWidget($postData);
			//save widget data
			$this->saveWidgetPosition($postData);
			$this->saveGoogleAnalyticsSettings($postData);
			$this->saveAdvanceSettings($postData);
			$this->saveHomepageCategories($postData);
			$this->savePersonalization($postData);
            $postData = $this->getRequest()->getPostValue();

			// Saving Push Notification Data With IOSPEM File Uploader
			if(isset($_FILES['upload_iospem_file']['name']) && !empty($_FILES['upload_iospem_file']['name'])){
				try{
                    $uploader = $this->uploaderFactory->create(['fileId' => 'upload_iospem_file']);
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setAllowedExtensions(['pem']);
                    $uploader->setAllowCreateFolders(true);
					
                    $media_path = $this->_dir->getPath("media").'/mobi_commerce/'.$appCode.'/appinfo/' ;
                    
					$iospemFilename = uniqid().'.'. PATHINFO($_FILES['upload_iospem_file']['name'], PATHINFO_EXTENSION);
					$uploader->save($media_path, $iospemFilename);
                    $data['upload_iospem_file'] = $iospemFilename;
				}catch (\Exception $e){
                    $this->messageManager->addError('Error uploading iOS PEM file: '.$e->getMessage());
				}
			}			
			
			if(!isset($postData['pushnotification']['active_push_notification'])){
			    $postData['pushnotification']['active_push_notification'] = '0';
			}

            if(!isset($postData['pushnotification']['sandboxmode'])){
				$postData['pushnotification']['sandboxmode'] = '0';
			}

			$pushNotificationData = $postData['pushnotification'];
			if(isset($data['upload_iospem_file']) && $data['upload_iospem_file']){
			    $pushNotificationData['upload_iospem_file'] = $data['upload_iospem_file'];
			}else{
				$pushNotificationData['upload_iospem_file'] = $postData['upload_iospem_file_name'];
			}

            $pushSettings = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
			$pushSettings = $pushSettings
				->addFieldToFilter('app_code', $appCode)
			    ->addFieldToFilter('setting_code', 'push_notification');
			foreach($pushSettings as $_pushSetting){
			   	$_pushSetting->setData('value', serialize($pushNotificationData))->save();
			}
            
            //Saving Application Information Data With App Share Image
			if(isset($_FILES['app_share_image']['name']) && !empty($_FILES['app_share_image']['name'])){
				try{
                    $uploader = $this->uploaderFactory->create(['fileId' => 'app_share_image']);
                    $uploader->setAllowedExtensions(['jpg','jpeg','png']);
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setAllowCreateFolders(true);
                    $media_path = $this->_dir->getPath("media").'/mobi_commerce/'.$appCode.'/appinfo/';
                    
					$shareImagename = uniqid().'.'.PATHINFO($_FILES['app_share_image']['name'], PATHINFO_EXTENSION);
					$uploader->save($media_path, $shareImagename);
                    $data['app_share_image'] = $shareImagename;
				}catch (\Exception $e){
                    $this->messageManager->addError('Error uploading application image: '.$e->getMessage());
				}
			}			
			$appInfoData = $postData['appinfo'];
			if(isset($data['app_share_image']) && $data['app_share_image']){
			    $appInfoData['app_share_image'] = 'mobi_commerce/'.$appCode.'/appinfo/'.$data['app_share_image'];
			}else if(isset($postData['app_share_image']['value'])){
				$appInfoData['app_share_image'] = $postData['app_share_image']['value'];
			}
			else{
				$appInfoData['app_share_image'] = '';	
			}

			if(isset($postData['app_share_image']['delete']) && $postData['app_share_image']['delete'] == 1){
			    $appInfoData['app_share_image'] = '';
			}

			$appInfoJsonData = serialize($appInfoData);
            $applicationSettingCollection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
			$applicationSettingCollection = $applicationSettingCollection
				->addFieldToFilter('app_code', $appCode)
			    ->addFieldToFilter('setting_code','appinfo');    
            foreach($applicationSettingCollection as $appinfo){
			    $appinfo->setData('value', $appInfoJsonData)->save();
			}
			
            // Save Cms Content Data with Contact Information
            if (isset($_FILES['contact_information_menu_icon']['name']) && !empty($_FILES['contact_information_menu_icon']['name'])) {
                try {
                    $uploader = $this->uploaderFactory->create(['fileId' => 'contact_information_menu_icon']);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setAllowCreateFolders(true);
                    $mobicommerceDir = 'mobi_commerce/' . $appCode . '/appinfo/';
                    $mediaPath = $this->_dir->getPath("media") . '/' . $mobicommerceDir;

                    $shareImagename = uniqid() . '.' . PATHINFO($_FILES['contact_information_menu_icon']['name'], PATHINFO_EXTENSION);
                    $uploader->save($mediaPath, $shareImagename);
                    $postData['contact_information']['menu_icon'] = $mobicommerceDir.$shareImagename;
                } catch (\Exception $e) {
                    $this->messageManager->addError('Error uploading left menu icon: '.$e->getMessage());
                }
            }
            
            if (isset($postData['contact_information']['menu_icon_isdelete']) && $postData['contact_information']['menu_icon_isdelete']) {
                $postData['contact_information']['menu_icon'] = "";
            }

            $cmscontentarray = [];
			$cmscontentarray['contact_information'] = $postData['contact_information'];
            $cmscontentarray['social_media'] = $postData['social_media'];
            $cmsSelected = [];
            if(isset($postData['cms_pages']['status']) && !empty($postData['cms_pages']['status'])){
            	foreach($postData['cms_pages']['status'] as $_cms_id => $_cms){
            		$cmsSelected[] = [
						'id'    => $_cms_id,
						'index' => isset($postData['cms_pages']['index'][$_cms_id]) ? (int) $postData['cms_pages']['index'][$_cms_id] : false
            			];
            	}

            	foreach ($cmsSelected as $key => $row) {
				    $cmsids[$key] = $row['id'];
				    $cmsindexes[$key] = $row['index'];
				}
				array_multisort($cmsindexes, SORT_ASC, $cmsids, SORT_ASC, $cmsSelected);
            }
            
            $cmscontentarray['cms_pages'] = $cmsSelected;
			$cmscontentarray = serialize($cmscontentarray);
			$applicationSettingCollection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create()->addFieldToFilter('app_code', $appCode)
				->addFieldToFilter('storeid', $storeid)
			    ->addFieldToFilter('setting_code', 'cms_settings');
				
			// Added by tauseef 
			if($applicationSettingCollection->getSize() > 0){
				foreach($applicationSettingCollection as $cmssetting){
					$cmssetting->setData('value', $cmscontentarray)->save();
				}
			}else{
				$applicationSettingCollection = $this->mobiadmin3AppsettingFactory->create();
				$applicationSettingCollection->setData('app_code', $appCode);
				$applicationSettingCollection->setData('storeid', $storeid);
				$applicationSettingCollection->setData('setting_code', "cms_settings");
				$applicationSettingCollection->setData('value', $cmscontentarray);
				$applicationSettingCollection->save();
			}
			// Added by tauseef - upto here 
			$this->savePushNotificationHistory($postData, $pushNotificationData);
            
            if(isset($errors) && !empty($errors)){
                $this->messageManager->addError(__(implode(',', $errors)));
			}else{
				$message = __('Application is successfully Save.');
                $this->messageManager->addSuccess($message);
			}

			$this->_redirect('*/*/edit', [
				'id'       => $appid,
				'_current' =>true
            ]);

		}
		else{
			$this->_redirect('mobicommerce3', [
                'id'       => $appid,
                '_current' =>true
            ]);
		}
	}

	public function saveWidgetPosition($post)
	{
	    if(isset($post['widget_position']) && count($post['widget_position'])) {
		    foreach($post['widget_position'] as $widget_id => $position) {		 	    
				$widgetModel = $this->mobiadmin3WidgetFactory->create()->load($widget_id);
				$widgetModel->setWidgetPosition($position)->save();
			}
	    }	 
	}

	/**
	 * Created by Yash
	 * Date: 17-07-2015
	 * Whatever product attributes user select, database will save rest of the attributes as disabled attributes
	 * so if user add more attributes, it will be by default enabled
	 */
	public function saveAdvanceSettings($data)
	{
		if(isset($data['advancesettings']) && !empty($data['advancesettings'])){
			$advancesettings = $data['advancesettings'];
			$all_attributes = [];
			$attributes = $this->_mobiadmin3Helper->getProductAttributes();
			if($attributes){
				foreach($attributes as $_attr){
					$all_attributes[] = $_attr['code'];
				}
			}

			if(!isset($advancesettings['productdetail']['showattribute'])){
				$advancesettings['productdetail']['showattribute'] = [];
			}

			$show_attributes = $advancesettings['productdetail']['showattribute'];
			$hidden_attributes = array_diff($all_attributes, $show_attributes);
			$advancesettings['productdetail']['showattribute'] = array_flip($hidden_attributes);

			if(!isset($advancesettings['productdetail']['showattribute_popup'])){
				$advancesettings['productdetail']['showattribute_popup'] = [];
			}
			$show_attributes = $advancesettings['productdetail']['showattribute_popup'];
			$hidden_attributes = array_diff($all_attributes, $show_attributes);
			$advancesettings['productdetail']['showattribute_popup'] = array_flip($hidden_attributes);
			
			$collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
			$collection->addFieldToFilter('app_code', $data['appcode'])->addFieldToFilter('setting_code', 'advance_settings');
			if($collection->getSize() > 0){
				foreach($collection as $_collection){
					$_collection->setData('value', serialize($advancesettings))->save();
				}
			}
			else{
				$this->mobiadmin3AppsettingFactory->create()->setData([
					'app_code'     => $data['appcode'],
					'setting_code' => 'advance_settings',
					'value'        => serialize($advancesettings)
					])->save();
			}
		}
	}

	public function saveHomepageCategories($data)
	{
        $store = (int) $this->getRequest()->getParam('store',0);

		if(!isset($data['homepage_categories'])){
			$data['homepage_categories'] = [];
		}
		else {
			$data['homepage_categories'] = array_flip($data['homepage_categories']);	
		}
        
		$collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
		$collection->addFieldToFilter('app_code', $data['appcode'])
			->addFieldToFilter('setting_code', 'homepage_categories')
			->addFieldToFilter('storeid', $store);

		if($collection->getSize() > 0){
			foreach($collection as $_collection){
				$_collection->setData('value', serialize($data['homepage_categories']))->save();
			}
		}
		else{
			$this->mobiadmin3AppsettingFactory->create()->setData([
				'app_code'     => $data['appcode'],
				'setting_code' => 'homepage_categories',
				'storeid'      => $store,
				'value'        => serialize($data['homepage_categories'])
				])->save();
		}
	}

	public function savePersonalization($data)
	{
		$appcode = $data['appcode'];
		$file_personalizer_parent = $this->_dir->getUrlPath("media").'/mobi_assets/v/'.$this->_mobiadmin3Helper->getMobiBaseVersion().'/theme_files/shopper/personalizer/personalizer.xml';
		$file_personalizer_child = $this->_dir->getUrlPath("media").'/mobi_commerce/'.$appcode.'/personalizer/personalizer.xml';
		$file_personalizer_css_child = $this->_dir->getUrlPath("media").'/mobi_commerce/'.$appcode.'/personalizer/personalizer.css';
			
		$xml_personalizer_child = new \DOMDocument('1.0');
		$xml_personalizer_child->formatOutput = true;
		$root = $xml_personalizer_child->createElement('mobicommerce_personalizer');
		$root = $xml_personalizer_child->appendChild($root);
		//echo '<pre>';print_r($data['personalizer']);exit;
        foreach($data['personalizer'] as $personalizer_key => $personalizer_value) {
			$xml_personalizer_key = $xml_personalizer_child->createElement($personalizer_key);
			$new_xml_personalizer_child = $root->appendChild($xml_personalizer_key);
		    
			$current_value_key = $xml_personalizer_child->createElement('current_value');       
			$current_value = $xml_personalizer_child->createTextNode($personalizer_value);
			$current_value_key->appendChild($current_value);
			$new_xml_personalizer_child->appendChild($current_value_key);
		}
		$xml_personalizer_child->save($file_personalizer_child);

		//save css
		$css = [];
		if(file_exists($file_personalizer_parent)) {
			$code_personalizer_parent = simplexml_load_file($file_personalizer_parent);
			foreach ($code_personalizer_parent as $option => $value) {
				if((string)$value->type == 'css') {
					$_color = isset($data['personalizer'][$option]) ? $data['personalizer'][$option] : '';
					$_css = (string)$value->css;
					$_css = str_replace('--COLOR--', $_color, $_css);
					$_css = implode("\r\n", explode('|', $_css));
					$css[] = $_css;
				}
			}
		}

		if(!empty($css)) {
			file_put_contents($file_personalizer_css_child, implode("\r\n", $css));
		}
		//save css - upto here
		
		if(isset($data['change_personalizer']) && !empty($data['change_personalizer'])) {
			$url = $this->_mobiadmin3Helper->curlBuildUrl().'build/updatePersonalizer'; 
			$curldata = [
				'appcode'      => $appcode,
				'appkey'       => $data['appkey'],
				'personalizer' => [
					'android_primary_theme'   => $data['personalizer']['android_primary_theme'],
					'android_secondary_theme' => $data['personalizer']['android_secondary_theme'],
					'ios_primary_theme'       => $data['personalizer']['ios_primary_theme'],
					]
				];
			//echo $url;exit;
			//echo '<pre>';print_r($curldata);exit;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_NOBODY, TRUE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 15); 
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			curl_setopt($ch, CURLOPT_POST, count($curldata));
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($curldata));
			$result = curl_exec($ch);
			//$info = curl_getinfo($ch);
			curl_close($ch);
			//echo '<pre>';print_r($result);print_r($info);exit;
			$result = json_decode($result, TRUE);
			if($result['status'] == 'success') {
				$this->messageManager->addSuccess($result['message']);
			}
			else {
				$this->messageManager->addError($result['message']);
			}
		}
	}

	/**
	 * Created by Yash
	 * Date: 23-09-2015
	 */
	public function saveGoogleAnalyticsSettings($data)
	{
		if(isset($data['analyticsSettings']) && !empty($data['analyticsSettings'])){
			$analyticsSettings = $data['analyticsSettings'];
			$collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
			$collection->addFieldToFilter('app_code', $data['appcode'])->addFieldToFilter('setting_code', 'googleanalytics');
			if($collection->getSize() > 0){
				foreach($collection as $_collection){
					$_collection->setData('value', serialize($analyticsSettings))->save();
				}
			}
			else{
				$this->mobiadmin3AppsettingFactory->create()->setData([
					'app_code'     => $data['appcode'],
					'setting_code' => 'googleanalytics',
					'value'        => serialize($analyticsSettings)
					])->save();
			}
		}
	}

    /**
	 * added by yash
	 * to save sent push notification in db
	 */
	public function savePushNotificationHistory($postData, $pushNotificationData)
    {
		/*
		* Sending Android and IOS Push Notification
        */
		$heading  = $postData['pushheading'];
		$message  = $postData['pushnotifications'];
		$whom     = $postData['whom'];
		$type     = $postData['push_device_type'];
		$deeplink = $postData['pushdeeplink'];
		$store    = $postData['push_store'];
		$image_url = '';
		$send_to   = [];
        if(!empty($heading) && !empty($message))
        {
        	if(isset($_FILES['pushfile']['name']) && !empty($_FILES['pushfile']['name'])){
                try {
                    $path = $this->_dir->getUrlPath("media").'/mobi_commerce/'.$postData['appcode'].'/pushnotification';
                    $fname = uniqid().'.'.PATHINFO($_FILES['pushfile']['name'], PATHINFO_EXTENSION);
                    $uploader = $this->uploaderFactory->create(['fileId' => 'pushfile']);
                    $uploader->setAllowedExtensions(['jpg','png','jpeg']);
                    $uploader->setAllowCreateFolders(true);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $result = $uploader->save($path, $fname);
                    $image_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'mobi_commerce/'.$postData['appcode'].'/pushnotification/'.$result['file'];
                }
                catch(\Exception $e) {
                    $this->messageManager->addError('Error uploading push image file: '.$e->getMessage());
                }
			}

			if(!empty($postData['specificcustomer'])){
				$send_to = $postData['specificcustomer'];
			}else if(!empty($postData['customer_groupId'])){
				$send_to = $postData['customer_groupId'];
			}

			if(empty($send_to)){
				$send_to = NULL;
				$send_to_devices = NULL;
			}
			else{
				$send_to_devices = $send_to;
				$send_to = implode(',', $send_to);
				$send_to = ','.$send_to.',';
			}
			
			$data = [
				'appcode'        => $postData['appcode'],
				'date_submitted' => date('Y-m-d H:i:s'),
				'store_id'		 => $store,
				'device_type'    => $type,
				'heading'        => $heading,
				'message'        => $message,
				'deeplink'       => $deeplink,
				'image'          => $image_url,
				'send_to_type'   => $whom,
				'send_to'        => $send_to
				];
            
			$this->mobiadmin3PushhistoryFactory->create()->setData($data)->save();
			
			$androidDevices = [];
			$iosDevices = [];

			$deviceCollection = $this->mobiadmin3ResourceDevicetokensCollectionFactory->create()->addFieldToFilter('md_appcode', $postData['appcode'])
				->addFieldToFilter('md_enable_push', '1');

			if(!empty($store)) {
				$deviceCollection->addFieldToFilter('md_store_id', $store);
			}

			if(in_array($whom, ['customer_group'])){
				$customers = [];
				$cgCollection = $this->getCoreModel('Magento\Customer\Model\Customer')->getCollection()->addAttributeToSelect('*')
					->addFieldToFilter('group_id', ['in' => $send_to_devices]);
				$gcustomerId = '';
				foreach ($cgCollection as $_group) {
					$customers[] = $_group['entity_id'];
				}
				//echo '<pre>';print_r($send_to_devices);exit;
				$deviceCollection->addFieldToFilter('md_userid', ['in' => [$customers]]);
			}
			else if(in_array($whom, ['specific_customer'])){
				$deviceCollection->addFieldToFilter('md_userid', ['in' => [$send_to_devices]]);
			}

			if(!empty($deviceCollection)){
				foreach($deviceCollection as $_device){
					if($_device['md_devicetype'] == 'android'){
						$androidDevices[] = $_device['md_devicetoken'];
					}
					else{
						$iosDevices[] = $_device['md_devicetoken'];
					}
				}
			}
			
			$pushparams = [
				'heading'   => $heading,
				'message'   => $message,
				'deeplink'  => $deeplink,
				'image_url' => $image_url
				];

			$pushsizeArray = $pushparams;
			$pushsize = serialize($pushsizeArray);
			if (function_exists('mb_strlen')) {
			    $pushsize = mb_strlen($pushsize, '8bit');
			} else {
			    $pushsize = strlen($pushsize);
			}
			
			if($pushsize > 4096){
				$errors[] = __('Push notification size is '.$pushsize.' bytes. It should not be greater then 4096 bytes');
			}
			else{
				if(!empty($androidDevices)){
					if(in_array($type, ['android', 'both'])){
						$this->_mobiadmin3Helper->androidpushnotification($pushparams, $pushNotificationData, $androidDevices);
					}
				}
            	if(!empty($iosDevices)){
            		if(in_array($type, ['ios', 'both'])){
						$this->_mobiadmin3Helper->iospushnotification($pushparams, $pushNotificationData, $iosDevices);
					}
            	}
			}
        }
	}
}
