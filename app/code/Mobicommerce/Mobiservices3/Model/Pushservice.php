<?php
namespace Mobicommerce\Mobiservices3\Model;

class Pushservice extends \Mobicommerce\Mobiservices3\Model\AbstractModel {

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\NotificationFactory
     */
    protected $mobiadmin3NotificationFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Applications\CollectionFactory
     */
    protected $mobiadmin3ResourceApplicationsCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\ApplicationsFactory
     */
    protected $mobiadmin3ApplicationsFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Licence\CollectionFactory
     */
    protected $mobiadmin3ResourceLicenceCollectionFactory;    
    protected $moduleManager;
    protected $_scopeConfig;
        
    public function __construct(
        \Mobicommerce\Mobiadmin3\Model\NotificationFactory $mobiadmin3NotificationFactory,
        \Mobicommerce\Mobiadmin3\Model\Resource\Applications\CollectionFactory $mobiadmin3ResourceApplicationsCollectionFactory,
        \Mobicommerce\Mobiadmin3\Model\ApplicationsFactory $mobiadmin3ApplicationsFactory,
        \Mobicommerce\Mobiadmin3\Model\Resource\Licence\CollectionFactory $mobiadmin3ResourceLicenceCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig         
    ) {
        $this->mobiadmin3NotificationFactory = $mobiadmin3NotificationFactory;
        $this->mobiadmin3ResourceApplicationsCollectionFactory = $mobiadmin3ResourceApplicationsCollectionFactory;
        $this->mobiadmin3ApplicationsFactory = $mobiadmin3ApplicationsFactory;
        $this->mobiadmin3ResourceLicenceCollectionFactory = $mobiadmin3ResourceLicenceCollectionFactory;
        $this->moduleManager = $moduleManager;
        $this->_scopeConfig = $scopeConfig;        
    }

    public function addNotification($data = [])
    {
        $type = isset($data['type']) ? $data['type'] : NULL;
        $message = isset($data['message']) ? $data['message'] : NULL;
        $licence_key = isset($data['licence_key']) ? $data['licence_key'] : NULL;

        if(empty($type) || empty($message) || empty($licence_key)){
            return $this->errorStatus("invalid_data");
        }
        else{
            //added or 1 for testing purposs
            if($this->_validateLicenceKey($licence_key) || 1){
                $this->mobiadmin3NotificationFactory->create()->setData([
                    "type"        => $type,
                    "date_added"  => date("Y-m-d H:i:s"),
                    "message"     => $message,
                    "read_status" => "0"
                    ])->save();

                return $this->successStatus("Notification added");
            }
            else{
                return $this->errorStatus("Invalid licence key");
            }
        }
    }

    public function updateBuild($data = [])
    {
        $appcode = isset($data['appcode']) ? $data['appcode'] : NULL;
        $appkey  = isset($data['appkey']) ? $data['appkey'] : NULL;
        $licence_key = isset($data['licence_key']) ? $data['licence_key'] : NULL;
        if(!empty($appcode) && !empty($appkey) && !empty($licence_key)){
            if($this->_validateLicenceKey($licence_key) || 1){
                $collection = $this->mobiadmin3ResourceApplicationsCollectionFactory->create()->addFieldToFilter('app_code',$appcode)
                    ->addFieldToFilter('app_key',$appkey);
                if($collection){
                    foreach($collection as $_collection){
                        if(isset($data['app_name'])){
                            $_collection->setData('app_name', $data['app_name']);
                        }
                        if(isset($data['app_mode'])){
                            $_collection->setData('app_mode', $data['app_mode']);
                        }
                        if(isset($data['android_status'])){
                            $_collection->setData('android_status', $data['android_status']);
                        }
                        if(isset($data['android_url'])){
                            $_collection->setData('android_url', $data['android_url']);
                        }
                        if(isset($data['udid'])){
                            $_collection->setData('udid', $data['udid']);
                        }
                        if(isset($data['ios_status'])){
                            $_collection->setData('ios_status', $data['ios_status']);
                        }
                        if(isset($data['ios_url'])){
                            $_collection->setData('ios_url', $data['ios_url']);
                        }
                        if(isset($data['delivery_status'])){
                            $_collection->setData('delivery_status', $data['delivery_status']);
                        }
                        if(isset($data['addon_parameters'])){
                            $_collection->setData('addon_parameters', $data['addon_parameters']);
                        }
                        
                        $_collection->save();
                    }
                }
                return $this->successStatus($collection->count() . " apps updated");
            }
            else{
                return $this->errorStatus("Invalid licence key");
            }
        }
    }

    public function removeapps($data = [])
    {
        $licence_key = isset($data['licence_key']) ? $data['licence_key'] : NULL;
        if($licence_key && isset($data['appstodelete']) && !empty($data['appstodelete'])){
            if($this->_validateLicenceKey($licence_key)){
                $appcodes = [];
                foreach($data['appstodelete'] as $_app){
                    $appcodes[] = $_app['appcode'];
                }
                $deletedAppsCount = $this->mobiadmin3ApplicationsFactory->create()->deleteapps($appcodes);
                return $this->successStatus($deletedAppsCount . " Apps deleted");
            }
        }
        return $this->errorStatus("No records found");
    }

    public function buyapp($data = [])
    {
        $appcode              = isset($data['appcode']) ? $data['appcode'] : NULL;
        $appkey               = isset($data['appkey']) ? $data['appkey'] : NULL;
        $licence_key          = isset($data['licence_key']) ? $data['licence_key'] : NULL;
        $mode                 = isset($data['mode']) ? $data['mode'] : NULL;
        $mobicommerce_orderid = isset($data['mobicommerce_orderid']) ? $data['mobicommerce_orderid'] : NULL;
        $services_purchased   = isset($data['services_purchased']) ? $data['services_purchased'] : NULL;

        if(empty($appcode) || empty($appkey) || empty($licence_key) || empty($mode) || empty($mobicommerce_orderid)){
            return $this->errorStatus("Invalid parameters");
        }
        else{
            $appCollection = $this->mobiadmin3ResourceApplicationsCollectionFactory->create()->addFieldToFilter('app_code', $appcode)
                ->addFieldToFilter('app_key', $appkey)
                ->addFieldToFilter('app_license_key', $licence_key);

            $services_purchased = explode(',', $services_purchased);

            if($appCollection->count() > 0){
                foreach($appCollection as $_app){
                    $_app->setData("app_mode", $mode);
                    $_app->setData("android_url", isset($data['android_url']) ? $data['android_url'] : NULL);
                    $_app->setData("android_status", isset($data['android_status']) ? $data['android_status'] : NULL);
                    $_app->setData("ios_url", isset($data['ios_url']) ? $data['ios_url'] : NULL);
                    $_app->setData("ios_status", isset($data['ios_status']) ? $data['ios_status'] : NULL);
                    $_app->setData("delivery_status", isset($data['delivery_status']) ? $data['delivery_status'] : NULL);
                    $_app->setData('webapp_url', isset($data['webapp_url']) ? $data['webapp_url'] : NULL)->save();
                    $_app->setData("addon_parameters", serialize([
                        "mobicommerce_orderid" => $mobicommerce_orderid,
                        "services_purchased" => $services_purchased
                        ]));
                    $_app->setData("update_time", date("Y-m-d H:i:s"));
                    $_app->save();
                }

                $licenceCollection = $this->mobiadmin3ResourceLicenceCollectionFactory->create();
                if($licenceCollection->count() > 0){
                    foreach($licenceCollection as $_licence){
                        $_licence->setData("ml_debugger_mode", 'no');
                        $_licence->save();
                    }
                }
                return $this->successStatus($appCollection->count() . " apps updated");
            }
            else{
                return $this->errorStatus("No apps found");
            }
        }
    }

    public function getPluginVersion()
    {
        $return = $this->successStatus();
        $return['data']['moduleStatus'] = $this->moduleManager->isEnabled('Mobicommerce_Mobiservices3');
        $return['data']['moduleVersion'] = $this->_scopeConfig->getValue('mobicommerce3_config/module/version', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);        
        return $return;
    }

    public function getModulesList($data = [])
    {
        $licence_key = isset($data['licence_key']) ? $data['licence_key'] : NULL;
        if($licence_key){
            $licenceCollection = $this->mobiadmin3ResourceLicenceCollectionFactory->create();
            $serverLicenceKey = NULL;
            $debuggerMode = false;
            if($licenceCollection->getLastItem()){
                $serverLicenceKey = $licenceCollection->getLastItem()->getMlLicenceKey();
                $debuggerMode = $licenceCollection->getLastItem()->getMlDebuggerMode();
                if($debuggerMode == 'yes'){
                    $debuggerMode = true;
                }
            }
            if($licence_key == $serverLicenceKey && $debuggerMode){
                $return = $this->successStatus();
                $magentoDir = Mage::getBaseDir();
                $return['data']['modules'] = array_keys((array)Mage::getConfig()->getNode('modules')->children());
                $return['data']['permissions']  = [
                    'timthumb.php' => substr(sprintf('%o', fileperms(Mage::getBaseDir() . '/MobiSettings/timthumb.php')), -4),
                    'timcache'     => substr(sprintf('%o', fileperms(Mage::getBaseDir(\Magento\Store\Model\Store::URL_TYPE_MEDIA) . '/timcache')), -4),
                    ];
                return $return;
            }
        }
        return $this->errorStatus("oops");
    }

    protected function _validateLicenceKey($key=null)
    {
        return true;
        if(empty($key))
            return false;

        $licenceCollection = $this->mobiadmin3ResourceLicenceCollectionFactory->create();
        if($licenceCollection->count() && $licenceCollection->getLastItem()){
            $serverLicenceKey = $licenceCollection->getLastItem()->getMlLicenceKey();
            if($key == $serverLicenceKey){
                return true;
            }
        }
        return false;
    }
}