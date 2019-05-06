<?php
namespace Mobicommerce\Mobiadmin3\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
	
	protected $mobibaseversion = '3';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory
     */
    protected $mobiadmin3ResourceAppsettingCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $_dir;
    protected $productAttributesCollection;
    protected $_storeManager;
    
    /**
     * Locales lists.
     *
     * @var ListsInterface
     */
    private $localeLists;
    
    protected $jsonHelper;

    protected $_mobiadmin3WidgetFactory;
    protected $_mobiadmin3CategorywidgetFactory;
    protected $_uploaderFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory $mobiadmin3ResourceAppsettingCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttributesCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Mobicommerce\Mobiadmin3\Model\WidgetFactory $mobiadmin3WidgetFactory,
        \Mobicommerce\Mobiadmin3\Model\CategorywidgetFactory $mobiadmin3CategorywidgetFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory
    ) {
        $this->request = $request;
        $this->mobiadmin3ResourceAppsettingCollectionFactory = $mobiadmin3ResourceAppsettingCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_dir = $dir;
        $this->productAttributesCollection = $productAttributesCollection;
        $this->_storeManager = $storeManager;
        $this->localeLists = $localeLists;
        
        parent::__construct($context);
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $jsonHelper = $objectManager->create('\Magento\Framework\Json\Helper\Data');        
        $this->jsonHelper = $jsonHelper;

        $this->_mobiadmin3WidgetFactory = $mobiadmin3WidgetFactory;
        $this->_mobiadmin3CategorywidgetFactory = $mobiadmin3CategorywidgetFactory;
        $this->_uploaderFactory = $uploaderFactory;
    }

	public function getMobiBaseVersion()
	{
		return $this->mobibaseversion;
	}
    
    public function getWebsites()
	{
		return $this->_storeManager->getWebsites();
	}
    
    public function getStoreConfig()
    {
        return $this->scopeConfig;
    }

	public function getProductAttributes()
	{
		/*
		$attributes = [
    		[
				'code'  => 'default_short_description',
				'label' => 'Default Short Description'
    			],
    		[
				'code'  => 'default_long_description',
				'label' => 'Default Long Description'
    			],
    		];
    		*/
    	$attributes = [];
    	$excludeAttr = [];
    	$attr = $this->productAttributesCollection;
    	if($attr){
    		foreach ($attr as $_attr) {
    			if($_attr->getIsVisibleOnFront() && !in_array($_attr->getAttributeCode(), $excludeAttr)){
    				$attributes[] = [
						'code'  => $_attr->getAttributeCode(),
						'label' => $_attr->getFrontendLabel(),
    					];
    			}
			}
    	}

    	return $attributes;
	}

	public function getAppLocaleCode()
	{
    	$storeid = $this->_storeManager->getStore()->getId();
		$locale = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeid);
		return $locale;
	}

	public function getThemeName($appcode)
	{
		$collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create()->addFieldToFilter('app_code', $appcode)
			->addFieldToFilter('setting_code', 'theme_folder_name');
		$data = $collection->getData();
        if($data && isset($data['0']['value'])){
            return $data['0']['value'];
        }
	}

	public function setLanguageCodeData($locale)
	{
		if($locale != 'en_US' && !file_exists($this->_dir->getUrlPath("media").'/mobi_assets/multilanguage/v3/'.$locale.'.xml'))
			@copy($this->_dir->getUrlPath("media").'/mobi_assets/multilanguage/v3/en_US.xml', $this->_dir->getUrlPath("media").'/mobi_assets/multilanguage/v3/'.$locale.'.xml');

		$xml = $this->_dir->getUrlPath("media").'/mobi_commerce/multilanguage/v3/'.$locale.'.xml';
		if(!file_exists($xml)){
			if(!file_exists($this->_dir->getUrlPath("media").'/mobi_commerce/multilanguage'))
				mkdir($this->_dir->getUrlPath("media").'/mobi_commerce/multilanguage', 0755);

			if(!file_exists($this->_dir->getUrlPath("media").'/mobi_commerce/multilanguage/v3'))
				mkdir($this->_dir->getUrlPath("media").'/mobi_commerce/multilanguage/v3', 0755);

			if(file_exists($this->_dir->getUrlPath("media").'/mobi_assets/multilanguage/v3/'.$locale.'.xml')){
				@copy($this->_dir->getUrlPath("media").'/mobi_assets/multilanguage/v3/'.$locale.'.xml', $this->_dir->getUrlPath("media").'/mobi_commerce/multilanguage/v3/'.$locale.'.xml');
			}
			else
				@copy($this->_dir->getUrlPath("media").'/mobi_assets/multilanguage/v3/'.$locale.'.xml', $this->_dir->getUrlPath("media").'/mobi_commerce/multilanguage/v3/en_US.xml');
		}
	}

	public function getLanguageData($locale)
	{
		$this->setLanguageCodeData($locale);
		$labels = [];

		$xml =$this->_dir->getUrlPath("media").'/mobi_assets/multilanguage/v3/'.$locale.'.xml';
        $xmldata = simplexml_load_file($xml);
        foreach($xmldata as $_key => $_data){
        	$labels[$_key] = (array)$_data;
        }

        $childxml = $this->_dir->getUrlPath("media").'/mobi_commerce/multilanguage/v3/'.$locale.'.xml';
        $childxmldata = simplexml_load_file($childxml);
        foreach($childxmldata as $_key => $_data){
        	if(array_key_exists($_key, $labels)){
        		$labels[$_key]['mm_text'] = (string)$_data->mm_text;
        	}
        }

		return $labels;
	}

	public function _jsonUnserialize($data = null)
	{
		$jsonData = json_decode($data, true);
		if(is_array($jsonData)){
			return $jsonData;
		}
		else{
			return @unserialize($data);
		}
	}

	public function buyNowUrl($version)
	{
		$baseurl = 'http://www.mobicommerce.net/mobiweb/index/';
		if($version == '001')// professional
			return $baseurl.'addtocart';
		else if($version == '002')// enterprise
			return $baseurl.'addtocartbyoption';
		else
			return 'addtocart';
	}

	public function curlBuildUrl()
	{
		return 'http://build3.build.mobi-commerce.net/';
	}

	public function mobicommerceEmailId()
	{
		return 'plugin@mobicommerce.net';
	}
	
	public function getLocaleLabel($locale)
	{
		$locales = $this->localeLists->getOptionLocales();
		foreach($locales as $_locale){
			if($_locale['value'] == $locale){
				return $_locale['label'];
			}
		}
        return "";
	}

	public function getAllChildCategories($category, $result = [], $firstcall = true)
	{
		$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactory = $_objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        
		$children=$categoryFactory->create()->addAttributeToSelect('*')
			->addAttributeToFilter('is_active', ['in' => [0,1]])
			->addAttributeToFilter('parent_id', $category)
            ->getData();
                
        if(!$firstcall){
			$result[] = $category;
		}
       
        foreach($children as $child){
            $result[] = $child['entity_id'];
            if($child['children_count'] > 0){
                
                $result1 = $this->getAllChildCategories($child['entity_id'], $result, false);
				$result = array_unique(array_merge($result, $result1));
            }
        }
		return $result;
	}

	public function getMobicommercePrerequisites()
	{
		return [];
	}

    public function getData()
    {
        return $this->_data;
    }

    public function setData($data)
    {
        $this->_data = $data;
    }
    
    public function androidpushnotification($pushparams, $pushdata, $devices = [])
    {
        $log = [];

        $android_key = $pushdata['android_key'];
        if(!empty($android_key) && !empty($devices) && !empty($pushparams['heading']) && !empty($pushparams['message'])){
            $msg = [
                'message'  => $pushparams['message'],
                'title'    => $pushparams['heading'],
                'deeplink' => $pushparams['deeplink'],
                'imageurl' => $pushparams['image_url'],
                'vibrate'  => 1,
                'sound'    => 1,
                'notId'    => time()
            ];
            //echo '<pre>';print_r($msg);exit;

            $headers = [
                'Authorization: key=' . $android_key,
                'Content-Type: application/json'
                ];

            /**
             * done by tauseef
             * as android is not taking more then 1000 devices in one call
             * date: 21-01-2016
             */
            //echo '<pre>';print_r($devices);exit;
            $androidDevices = array_chunk($devices, 1000);
            foreach($androidDevices as $adevices){
                $fields = [
                    'registration_ids' => $adevices,
                    'data'             => $msg
                ];
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                $result = curl_exec($ch);
                curl_close($ch);

                $log[] = 'Android Msg array: ';
                $log[] = $msg;
                $log[] = 'Android List of devices: ';
                $log[] = $adevices;
                $log[] = 'Android google response';
                $log[] = $result;
            }
        }
        $this->printPushLog($log);
    }

    public function iospushnotification($pushparams, $pushdata, $devices = [], $appcode = '')
    {
        return false;
        $log = [];

        $sandboxmode = false;
        $passphrase = $pushdata['pem_password'];
        $pemFile = $pushdata['upload_iospem_file'];
        
        $mediapath = $this->_dir->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $pemFile = $mediapath . $appcode . '/appinfo/' . $pemFile;
        
        $log[] = 'iOS PEM file URL : '.$pemFile;
        if(file_exists($pemFile)) {
            $log[] = 'iOS PEM file Exists.';
        }
        else {
            $log[] = 'iOS PEM file Does not Exist.';
        }
        $log[] = 'iOS PEM Password : '.$passphrase;

        //echo '<pre>';print_r($pemFile);exit;
        if(isset($pushdata['sandboxmode']) && $pushdata['sandboxmode'] == '1'){
            $sandboxmode = true;
        }

        $log[] = 'iOS Sandbox mode: '.($sandboxmode ? 'ON' : 'OFF');
        
        if(!empty($pemFile) && !empty($pushparams['message'])){
            $ctx = stream_context_create();
            stream_context_set_option($ctx, 'ssl', 'local_cert', $pemFile);
            stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

            // Open a connection to the APNS server
            $push_url = "ssl://gateway.push.apple.com:2195";
            if($sandboxmode){
                $push_url = "ssl://gateway.sandbox.push.apple.com:2195";
            }
            
            $log[] = 'iOS Push URL: '.$push_url;

            if(!empty($devices)){
                $fp = stream_socket_client(
                    $push_url, $err,
                    $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

                $log[] = "telnet gateway.push.apple.com 2195 command reply";
                $log[] = exec("telnet gateway.push.apple.com 2195", $a);

                if(!$fp){
                    $log[] = "Failed to connect: $err $errstr" . PHP_EOL;
                    //die("Failed to connect: $err $errstr");
                    //return "Failed to connect: $err $errstr" . PHP_EOL;
                }
                else{
                    //die("connected");
                    $log[] = "iOS status connected";
                    //echo 'Connected to APNS' . PHP_EOL;
                    $body['aps'] = [
                        'alert'    => $pushparams['message'],
                        'sound'    => 'default',
                        'deeplink' => $pushparams['deeplink']
                        ];

                    $payload = json_encode($body);
                    
                    $log[] = "Devices: ";
                    $log[] = $devices;

                    $iosDevices = array_chunk($devices, 10);
                    foreach ($iosDevices as $_device) {
                        $fp = stream_socket_client(
                        $push_url, $err,
                        $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
                        
                        $msg = "";
                        foreach ($_device as $value) {
                            $msg .= chr(0) . pack('n', 32) . pack('H*', $value) . pack('n', strlen($payload)) . $payload;
                        }
                        $result = fwrite($fp, $msg, strlen($msg));
                        if(!$result){
                            //echo 'Message not delivered' . PHP_EOL;
                            $log[] = 'Message not delivered' . PHP_EOL;
                        }
                        else{
                            //echo 'Message successfully delivered' . PHP_EOL;
                            $log[] = 'Message successfully delivered' . PHP_EOL;
                        }
                        fclose($fp);
                    }
                }
            }
        }
        $this->printPushLog($log);
    }
    
    public function printPushLog($log = [])
    {
        /*
        if(Mage::getStoreConfig('mobiconfig/general/debug_pushnotification')) {
            echo '<pre>';print_r($log);exit;
        }
        */
    }

    public function saveWidget($post)
	{
        $store_id = (int) $this->request->getParam('store', 0);
        $cat = $this->request->getParam('cat', null);
        $appcode = $post['appcode'];
		$widget_data = $post['widget'];
        $media_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $media_dir = $this->_dir->getPath("media");
        if(empty($cat))
        {
			$widget_media_path = $media_dir.'/mobi_commerce/'.$appcode.'/widget_image/';
			$widgetimageurl = 'mobi_commerce/'.$appcode.'/widget_image/';
        }
        else
        {
			$widget_media_path = $media_dir.'/mobi_commerce/category/';
			$widgetimageurl = 'mobi_commerce/category/';
        }

		if(!empty($post['widget_id'])) {
            if(empty($cat))
		      $widgetModel = $this->_mobiadmin3WidgetFactory->create()->load($post['widget_id']);
            else
                $widgetModel = $this->_mobiadmin3CategorywidgetFactory->create()->load($post['widget_id']);
                
            if($widgetModel->getWidgetCode() == 'widget_image_slider'){
                if(isset($_FILES['banners']['error']) && !empty($_FILES['banners']['error'])){
                    foreach($_FILES['banners']['error'] as $i => $errorcount) {
                        $_widget_image_slider = [];
                        if($errorcount == '0'){
                            $img_name = uniqid().'.'.PATHINFO($_FILES['banners']['name'][$i], PATHINFO_EXTENSION);
                            $directory_gallery = $widget_media_path;
                            
                            $uploader = $this->_uploaderFactory->create(['fileId' => "banners[$i]"]);
                            $uploader->setAllowedExtensions(['jpg','gif','png','jpeg']);
                            $uploader->setAllowCreateFolders(true);
                            $uploader->setAllowRenameFiles(true);
                            $uploader->setFilesDispersion(false);
                            $result = $uploader->save($directory_gallery, $img_name);
                            
                            $widget_data['widget_data']['banners'][$i]['banner_url'] = $widgetimageurl.$img_name;
                        }   

                        if(!isset($widget_data['widget_data']['banners'][$i]['banner_status']))
                            $widget_data['widget_data']['banners'][$i]['banner_status'] = '0';
                    }
                }

                if(isset($widget_data['widget_data']['banners']['{index}'])) {
                    unset($widget_data['widget_data']['banners']['{index}']);
                }
                $widget_data = $this->_removeImagelessBanners($widget_data);
            }
            
            if($widgetModel->getWidgetCode() == 'widget_category'){
				$categories = [];
				$savecat = [];

				if(count($widget_data['widget_data']['categories']) && is_array($widget_data['widget_data']['categories'])){
                    $widget_data['widget_data']['categories'] = json_decode($widget_data['widget_data']['categories'], true);
                    
					foreach($widget_data['widget_data']['categories'] as $cat_id => $sort){
						$categories['id'] = $cat_id;
						if(!empty($widget_data['widget_data']['category_position_'.$cat_id])){
						    $categories['position'] = $widget_data['widget_data']['category_position_'.$cat_id];
						}
						if($widget_data['widget_data']['category_navigate_'.$cat_id] == 1){
						    $categories['navigate'] = $widget_data['widget_data']['category_navigate_'.$cat_id];
						}else{
							$categories['navigate'] = 0;
						}
						$savecat[] = $categories;
					}
                    $savecat = json_decode($widget_data['widget_data']['categories'], true);
				}
				$widget_data['widget_data']['save_categories'] = $savecat;
			}

			$widgetModel
				->setWidgetId($post['widget_id'])
				->setWidgetLabel($widget_data['name'])
				->setWidgetStatus($widget_data['enable'])
				->setWidgetData(serialize($widget_data['widget_data']));

            $widgetModel->save();
		} else {
			if(!empty($widget_data['selected_widget'])) {	
				if($widget_data['selected_widget'] == 'widget_image_slider')
				{
					if(isset($_FILES['banners']['error']) && !empty($_FILES['banners']['error'])){
						foreach($_FILES['banners']['error'] as $i => $errorcount) {
							if($errorcount == '0'){
								$img_name = uniqid().'.'.PATHINFO($_FILES['banners']['name'][$i], PATHINFO_EXTENSION);
								$directory_gallery = $widget_media_path;
                                
                                $uploader = $this->_uploaderFactory->create(['fileId' => "banners[$i]"]);
                                $uploader->setAllowedExtensions(['jpg','gif','png','jpeg']);
                                $uploader->setAllowCreateFolders(true);
                                $uploader->setAllowRenameFiles(true);
                                $uploader->setFilesDispersion(false);
                                $result = $uploader->save($directory_gallery, $img_name);
                                
								$widget_data['widget_data']['banners'][$i]['banner_url'] = $widgetimageurl.$img_name;
							}
                            
                            if(!isset($widget_data['widget_data']['banners'][$i]['banner_status']))
                                $widget_data['widget_data']['banners'][$i]['banner_status'] = '0';
						}
					}

                    if(isset($widget_data['widget_data']['banners']['{index}'])) {
                        unset($widget_data['widget_data']['banners']['{index}']);
                    }
                    $widget_data = $this->_removeImagelessBanners($widget_data);
				}

				if($widget_data['selected_widget'] == 'widget_category')
				{
					$categories = [];
					$savecat = [];
					if(count($widget_data['widget_data']['categories']) && is_array($widget_data['widget_data']['categories'])){
						foreach($widget_data['widget_data']['categories'] as $cat_id){
							$categories['id'] = $cat_id;
							if(!empty($widget_data['widget_data']['category_position_'.$cat_id])){
								$categories['position'] = $widget_data['widget_data']['category_position_'.$cat_id];
							}
							if($widget_data['widget_data']['category_navigate_'.$cat_id] == 1){
								$categories['navigate'] = $widget_data['widget_data']['category_navigate_'.$cat_id];
							}else{
								$categories['navigate'] = 0;
							}
							$savecat[] = $categories;
						}					
					}
					$widget_data['widget_data']['save_categories'] = $savecat;
				}
				if($widget_data['selected_widget'] == 'widget_product_slider')
				{
					$products = [];
					$saveproducts = [];
					
					if(count($widget_data['widget_data']['products']) && is_array($widget_data['widget_data']['products'])){   
						foreach($widget_data['widget_data']['products'] as $prod_id){
							$products['id'] = $prod_id;
							if(!empty($widget_data['widget_data']['product_position_'.$prod_id])){
								$products['position'] = $widget_data['widget_data']['product_position_'.$prod_id];
							}							
							$saveproducts[] = $products;
						}					
					}
					$widget_data['widget_data']['save_products'] = $saveproducts;				
				}
				$data_to_save = [
					'widget_code'     => $widget_data['selected_widget'],
					'widget_label'    => $widget_data['name'],
					'widget_status'   => $widget_data['enable'],
					'widget_store_id' => $store_id,
					'widget_data'     => serialize($widget_data['widget_data']),
				];

                if(!empty($cat)) {
                    $data_to_save['widget_category_id'] = $cat;
                    $this->_mobiadmin3CategorywidgetFactory->create()->setData($data_to_save)->save();
                }
                else {
                    $data_to_save['widget_app_code'] = $appcode;
                    $this->_mobiadmin3WidgetFactory->create()->setData($data_to_save)->save();
                }
			}
		}
	}

	protected function _removeImagelessBanners($widget_data)
    {
        $image_sliders = [];
        if(isset($widget_data['widget_data']['banners']) && $widget_data['widget_data']['banners'])
        {
            foreach ($widget_data['widget_data']['banners'] as $key => $value) {
                if($value['banner_url']) {
                    $image_sliders[] = $value;
                }
            }
        }

        $widget_data['widget_data']['banners'] = $image_sliders;
        return $widget_data;
    }
}