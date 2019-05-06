<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Application;
use Magento\Store\Model\Group;
use \Magento\Store\Model\StoreRepository;
class CreateApp extends \Magento\Backend\App\Action
{
	protected $_pageFactory;
    protected $_storeManager;
    protected $_resultForwardFactory;
    protected $_resultLayoutFactory;
    protected $_resultPageFactory;
    protected $_storeRepository;
    protected $_customerSession;    
    protected $_mobiadmin3Helper;
    protected $_messageManager;
    protected $_dir;
    protected $request;
    protected $scopeConfig;
    protected $resourceConnection;
    protected $mobiadmin3ResourceLicenceCollectionFactory;
    protected $mobiadmin3ApplicationsFactory;
    protected $mobiadmin3Applications;
    protected $uploaderFactory;
    protected $authSession;
        
	public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
       	\Magento\Backend\Model\Session $customerSession,
       	\Mobicommerce\Mobiadmin3\Helper\Data $mobiadmin3Helper,
       	\Magento\Framework\Message\ManagerInterface $messageManager,
       	\Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Mobicommerce\Mobiadmin3\Model\Resource\Licence\CollectionFactory $mobiadmin3ResourceLicenceCollectionFactory,
        \Mobicommerce\Mobiadmin3\Model\ApplicationsFactory $mobiadmin3ApplicationsFactory,
        \Mobicommerce\Mobiadmin3\Model\Applications $mobiadmin3Applications,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
       	StoreRepository $storeRepository,
       	\Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->_request = $request;
        
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_customerSession = $customerSession;
      	$this->_resultForwardFactory = $resultForwardFactory;
      	$this->_mobiadmin3Helper = $mobiadmin3Helper;
      	$this->_storeRepository = $storeRepository;
      	$this->_messageManager = $messageManager;
      	$this->resourceConnection = $resourceConnection;
      	$this->mobiadmin3ResourceLicenceCollectionFactory = $mobiadmin3ResourceLicenceCollectionFactory;
      	$this->mobiadmin3ApplicationsFactory = $mobiadmin3ApplicationsFactory;
      	$this->mobiadmin3Applications = $mobiadmin3Applications;
      	$this->uploaderFactory = $uploaderFactory;
      	$this->authSession = $authSession;
      	$this->_dir = $dir;
      
      	parent::__construct($context);
    }

	public function getAdminSession()
	{
	   return $this->_customerSession;
    }
    
    public function getWebsitesList()
    {
        return $websiteGroups = $this->_storeManager->getWebsite();
    }
        
   	protected function __sendEmailBeforeCreateApp($data)
	{
		return false;
		if(!empty($data)){
			$groupId = $data['store'];
			$storeId = 0;
			foreach ($this->_storeManager->getWebsites() as $website){
			    foreach ($website->getGroups() as $group){
			    	if($group->getGroupId() == $groupId){
			    		$storeId = $group->getDefaultStoreId();
			    	}
			    }
			}
            //commented by Parvez
            $storeUrl = $this->_storeManager->getStore($storeId)->getBaseUrl(\Magento\Store\Model\Store::URL_TYPE_WEB);
			$body = "App Name:- ".$data['appname']." <br>  Store Url:-  ".$storeUrl." <br> Email Id :- ".$data['primaryemail']." <br> Phone Number:- ".$data['phone_country_code']."-".$data['phone']."";
            
            $to = $this->_mobiadmin3Helper->mobicommerceEmailId();
			
            $user = $this->getAdminSession();
			$from = $user->getEmail();
			$mail = new \Zend_Mail();
			$mail->setBodyText('Mobicommerce Create App Request');
			$mail->setBodyHtml($body);
			$mail->setFrom($from, $data['appname']);
			$mail->addTo($to, 'Mobicommerce');
			$mail->setSubject("Create App Request From ".$storeUrl);
			//try {$mail->send();}
			//catch (Exception $e){}
		}
	}

    protected function __resetConnection()
	{
		$db = $this->resourceConnection->getConnection('core_read');
		$db->closeConnection();
		$db->getConnection();
		$db = $this->resourceConnection->getConnection('core_write');
		$db->closeConnection();
		$db->getConnection();
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
        $media_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $media_dir = $this->_dir->getPath("media");
        $app_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        $max_execution_time = ini_get('max_execution_time');
		if($max_execution_time != -1 && $max_execution_time < 300){
			ini_set('max_execution_time', 300);
		}
		$max_input_time = ini_get('max_input_time');
		if($max_input_time != -1 && $max_input_time < 300){
			ini_set('max_input_time', 300);
		}

		$refererUrl = $this->_redirect->getRefererUrl();		
		$postData = $this->getRequest()->getPostValue();
		$postData['email'] = $this->authSession->getUser()->getEmail();

		if(!isset($postData['store'])){
			$url = $this->getUrl('mobicommerce/application/new');
			$resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($url);
            return $resultRedirect;
		}
        
        $stores = $this->_storeManager->getStores(false);
        $websiteIds = [];
        $storeList = [];
        foreach ($stores as $store) {
            $websiteId = $store->getWebsite();
            $storeId = $store->getId();
            $storeName = $store->getName();
            $storeList[$storeId] = $storeName;
            array_push($websiteIds, $websiteId);
        }
        
        $stores = $this->_storeRepository->getList();
        $websiteIds = [];
        $storeList = [];
        foreach ($stores as $store) {
            $websiteId = $store["website_id"];
            $storeId = $store["store_id"];
            $storeName = $store["name"];
            $storeList[$storeId] = $storeName;
            array_push($websiteIds, $websiteId);
        }
        
		$groupid = $postData['store'];
        $storeid = $groupid;
        
		$configurations = [
			'connectorVersionCode'   => '90c4203d29f077997e7c4e11ce402a93d604dab6',
			'max_execution_time'     => ini_get('max_execution_time'),
			'max_input_time'         => ini_get('max_input_time'),
			'add_store_code_to_urls' => $this->scopeConfig->getValue('web/url/use_store', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
			'default_store_code'     => $this->_storeManager->getStore()->getCode(),
			'mobicommerce_version'   => $this->_mobiadmin3Helper->getMobiBaseVersion(),
			];

		$validation = true;
        
		if(!empty($_FILES)){
			$images = [
				"appsplash" => "Splash",
				"appicon"   => "Icon",
				];

			foreach($images as $_image_name => $_image_label){
				if($_FILES[$_image_name]['name'] != '' && strtolower(PATHINFO($_FILES[$_image_name]['name'], PATHINFO_EXTENSION)) != 'png'){
					$this->_messageManager->addError(__($_image_label.' must be png'));
					$this->_customerSession->setData( 'createapp', $this->request->getPostValue());
					$validation = false;
				}
			}

			if(!$validation){
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath($refererUrl);	
    			return $resultRedirect;
			}
		}
        
		$this->__sendEmailBeforeCreateApp($postData);
		
		$curlData = $postData;
		$media_path = $media_dir .'/'. 'mobi_commerce';
		$mediaUrl = $media_url.'mobi_commerce/';
		$mediaMobiAssetUrl = $media_url.'mobi_assets/defaults/';
		
		$images = [
			"appsplash" => [
				"w" => 1536,
				"h" => 2048,
				"r" => null
				],
			"appicon" => [
				"w" => 1024,
				"h" => 1024,
				"r" => null
				]
			];
		foreach($images as $_image_name => $_image_size){

			if(isset($_FILES[$_image_name]['name']) && !empty($_FILES[$_image_name]['name'])){			
				try{
					$size = getimagesize($_FILES[$_image_name]['tmp_name']);
					if($_image_size['w'] != null && $_image_size['h'] != null && ($size[0] != $_image_size['w'] || $size[1] != $_image_size['h'])){
						$this->_messageManager->addError(__(ucfirst($_image_name).' Icon dimension must be '.$_image_size['w'].'X'.$_image_size['h']));
						$this->_customerSession->setData( 'createapp', $this->getRequest()->getPost());
					    $resultRedirect = $this->resultRedirectFactory->create();
                        $resultRedirect->setPath($refererUrl);			
					    return $resultRedirect;
					}

                    $uploader = $this->uploaderFactory->create(['fileId' => $_image_name]);
					$uploader->setAllowRenameFiles(false);
					$uploader->setAllowCreateFolders(true);
					$filename = uniqid().'.'.PATHINFO($_FILES[$_image_name]['name'], PATHINFO_EXTENSION);
					$uploader->save($media_path, $filename);
					$curlData[$_image_name] = $mediaUrl.$filename;
				}catch(Exception $e){
					$this->logger->debug($e);
					$this->_redirectError(502);
				}
			}
		}

		if(!isset($curlData['appsplash'])){
			$curlData['appsplash'] = $mediaMobiAssetUrl.'splash.png'; 
		}
		if(!isset($curlData['appicon'])){
			$curlData['appicon'] = $mediaMobiAssetUrl.'icon.png'; 
		}

		$this->__resetConnection();

        //commented by parvez need to be changed
        $curlData['approoturl'] = $app_url;
		$curlData['media_url'] = $media_url;
		/* code for licence key */
		$LicenceModel = $this->mobiadmin3ResourceLicenceCollectionFactory->create();
		$licencekey = "";
		if($LicenceModel->getLastItem()){
			$licencekey = $LicenceModel->getLastItem()->getMlLicenceKey();
		}
		$curlData['applicencekey'] = $licencekey;
		/* code for licence key - upto here */

		$curlData['backend_platform'] = 'magento';
		$curlData['ipaddress'] = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
		$curlData['deeplink_hostname'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
		$curlData['configurations'] = $configurations;
        $fields_string = http_build_query($curlData);

		$ch = curl_init();
		$url = $this->_mobiadmin3Helper->curlBuildUrl().'build/add';        
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($curlData));
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($curlData));
		$result = curl_exec($ch);
		//echo '<pre>';print_r($result);exit;
		curl_close($ch);
		$result = json_decode($result, true);
		$this->__resetConnection();
		
		if(isset($result)){
		    if($result['status'] == 'success'){				
				$appid = null;
				if($result['data']['appcode']){
					$data = [
						"groupId"               => $groupid,
						"version_type"          => $curlData['version_type'],
						"app_name"              => $curlData['appname'],
						"app_code"              => $result['data']['appcode'],
						"app_key"               => $result['data']['appkey'],
						"app_theme_folder_name" => $curlData['apptheme'],
						"android_status"        => $result['data']['android_status'],
						"android_url"           => $result['data']['android_url'],
						"ios_status"            => $result['data']['ios_status'],
						"ios_url"               => $result['data']['ios_url'],						
						"app_license_key"       => $licencekey,
                        "theme_android"         => 'shopper',
                        "theme_ios"             => 'shopper',
						];
                    
				    $appobject = $this->mobiadmin3Applications->saveApplicationData($data);
                    $appid = $appobject['appid'];
				}else{
				    $this->_messageManager->addError(__($result['message']));
					$this->_customerSession->setData( 'createapp', $this->request->getPost());
				}
				$this->_messageManager->addSuccess(__($result['message']));
				$this->_redirect('mobicommerce/application/edit', 
					[
					'id'       => $appid,
					'store'    => $groupid,
					'_current' => true
                ]);
		    }else {
				$this->_messageManager->addError(__($result['message']));
				$this->_customerSession->setData( 'createapp', $this->getRequest()->getPost());
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath($refererUrl);			
			    return $resultRedirect;
			}
		}else{
           	$resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($refererUrl);	
			return $resultRedirect;
		}
	}
}
