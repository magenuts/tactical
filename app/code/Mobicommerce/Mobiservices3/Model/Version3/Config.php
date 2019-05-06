<?php
namespace Mobicommerce\Mobiservices3\Model\Version3;


class Config extends \Mobicommerce\Mobiservices3\Model\AbstractModel {

    private $app_mode;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Applications\CollectionFactory
     */
    protected $mobiadmin3ResourceApplicationsCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory
     */
    protected $mobiadmin3ResourceAppsettingCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Helper\Data
     */
    protected $mobiadmin3Helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $dir;
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,      
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Mobicommerce\Mobiadmin3\Model\Resource\Applications\CollectionFactory $mobiadmin3ResourceApplicationsCollectionFactory,
        \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory $mobiadmin3ResourceAppsettingCollectionFactory,
        \Mobicommerce\Mobiadmin3\Helper\Data $mobiadmin3Helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem\DirectoryList $dir
    )
    {
        $this->mobiadmin3ResourceApplicationsCollectionFactory = $mobiadmin3ResourceApplicationsCollectionFactory;
        $this->mobiadmin3ResourceAppsettingCollectionFactory = $mobiadmin3ResourceAppsettingCollectionFactory;
        $this->mobiadmin3Helper = $mobiadmin3Helper;
        $this->scopeConfig = $scopeConfig;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->dir = $dir;
        
        parent::__construct($context, $registry, $storeManager, $eventManager);
        $this->getModel('Mobicommerce\Mobiservices3\Model\User')->autoLoginMobileUser();
    }
    
    protected function _getDefaultGroup($data)
    {
        $collection = $this->mobiadmin3ResourceApplicationsCollectionFactory->create()->addFieldToFilter('app_code', $data['appcode'])
            ->addFieldToFilter('app_key', $data['app_key']);
        
        if($collection->getSize()){
            $colloectionArray = $collection->getData();
            foreach($colloectionArray as $_collection) {
                $this->app_mode = $_collection['app_mode'];
                return $_collection['app_storegroupid'];
            }
        }
        else{
            return FALSE;
        }
    }

	public function getAllInitialData($data)
    {
        if(!isset($data['appcode'])){
            return $this->errorStatus('Unauthorized Access');
        }

		$groupId = $this->_getDefaultGroup($data);

        if(empty($groupId))
            return $this->errorStatus('Unauthorized Access');

        $store_id = $this->storeManager->getStore()->getStoreId();
        $valid_stores = [];
        $group_default_store = 0;

        foreach($this->storeManager->getWebsites() as $website){
            foreach($website->getGroups() as $group){
                if($group->getGroupId() == $groupId){
                    $group_default_store = $group->getDefaultStoreId();
                    foreach($group->getStores() as $_store){
                        $valid_stores[] = $_store->getStoreId();
                    }
                }
            }
        }

        if(!in_array($store_id, $valid_stores)){
            $store_id = $this->setAppStore($group_default_store);
        }
        
        if(isset($data['store']) && !empty($data['store'])){
            $this->setAppStore($data['store']);
            $store_id = $data['store'];
        }
        
        if(isset($data['currency']) && !empty($data['currency'])){
            $this->setAppStore($store_id, $data['currency']);
        }

        /* set push preference to either true or false */
        $this->getModel('Mobicommerce\Mobiservices3\Model\Push')->updatePreference($data);
        /* set push preference to either true or false - upto here */

		$info = $this->successStatus();
        $info['data']['store_code']      = $this->storeManager->getStore()->getCode();
        $info['data']['stores']          = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('stores', $data['appcode']);
        $info['data']['appinfo']         = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('appinfo', $data['appcode']);
        $info['data']['homedata']        = $this->getModel('Mobicommerce\Mobiservices3\Model\Home')->_getHomeData($data);
        $info['data']['CMS']             = $this->getModel('Mobicommerce\Mobiservices3\Model\Cms')->_getCmsdata($data);
        $info['data']['language']        = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('language', $data['appcode']);
        $info['data']['push']            = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('pushsettings', $data['appcode']);
        $info['data']['cart_details']    = $this->getModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart')->getCartInfo($data);
        $info['data']['userdata']        =$this->getModel('Mobicommerce\Mobiservices3\Model\User')->getCustomerData($data);
        $info['data']['wishlist']        =$this->getModel('Mobicommerce\Mobiservices3\Model\Wishlist\Wishlist')->getWishlistInfo($data);
        $info['data']['countries']       = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('country', $data['appcode']);
        $info['data']['googleanalytics'] = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('googleanalytics', $data['appcode']);
        $info['data']['social_login']    = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('social_login', $data['appcode']);

        $_category_keys = [];
        $_category_values = [];
        $_categories      = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('category', $data['appcode']);
        if($_categories) {
            foreach ($_categories as $_category) {
                $_category_keys = array_keys($_category);
                $_category_values[] = array_values($_category);
            }
        }
        $info['data']['category_keys'] = $_category_keys;
        $info['data']['category_values'] = $_category_values;

        $info = $this->getPersonalizer($info, $data);
		return $info;
	}

    private function getPersonalizer($info, $data)
    {
        if($this->app_mode == 'demo')
        {
            $theme_name = '';
            $collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
            $collection
                ->addFieldToFilter('app_code', $data['appcode'])
                ->addFieldToFilter('setting_code', 'theme_folder_name');
            if($collection->getSize() > 0){
                $theme_name = $collection->getFirstItem()->getValue();
            }
            
            $file_personalizer_parent = $this->dir->getUrlPath("media").'/mobi_assets/v/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/theme_files/'.$theme_name.'/personalizer/personalizer.xml';
            $file_personalizer_child = $this->dir->getPath("media").'/mobi_commerce/'.$data['appcode'].'/personalizer/personalizer.xml';
            if(file_exists($file_personalizer_parent) && file_exists($file_personalizer_child)) {
                $code_personalizer_parent = simplexml_load_file($file_personalizer_parent);
                $code_personalizer_child = simplexml_load_file($file_personalizer_child);
                $android_statusbar_color = '';
                foreach ($code_personalizer_parent->android_primary_theme->options->option as $option) {
                    if($option->value == (string)$code_personalizer_child->android_primary_theme->current_value) {
                        $android_statusbar_color = (string) $option->statusbar;
                    }
                }

                $info['data']['personalizer'] = [
                    'android_primary_theme' => (string)$code_personalizer_child->android_primary_theme->current_value,
                    'android_statusbar_color' => $android_statusbar_color,
                    'android_secondary_theme' => (string)$code_personalizer_child->android_secondary_theme->current_value,
                    'ios_primary_theme' => (string)$code_personalizer_child->ios_primary_theme->current_value,
                    'ios_secondary_theme' => (string)$code_personalizer_child->ios_secondary_theme->current_value,
                    ];
            }
        }

        return $info;
    }

	public function _getStoreSettings()
    {
		$options = Mage::getResourceSingleton('customer/customer')->getAttribute('gender')->getSource()->getAllOptions();
        $values = [];
        foreach ($options as $option){
            if ($option['value']){
                $values[] = [
                    'label' => $option['label'],
                    'value' => $option['value'],
                ];
            }
        }

        $country_code = $this->scopeConfig->getValue('general/country/default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $country = Mage::getModel('directory/country')->loadByCode($country_code);
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $info = [
            'store_info' => [
                'store_id'          => $this->_getStoreId(),
                'store_name'        => $this->_getStoreName(),
                'store_code'        => $this->storeManager->getStore()->getCode(),
                'country_code'      => $country->getId(),
                'country_name'      => $country->getName(),
                'locale_identifier' => Mage::app()->getLocale()->getLocaleCode(),
                'currency_name'     => Mage::app()->getLocale()->currency($currencyCode)->getName(),
                'currency_symbol'   => Mage::app()->getLocale()->currency($currencyCode)->getSymbol(),
                'currency_code'     => $currencyCode,
            ],
            'storeConfig' => [
                'web' => [
                    'add_store_code_to_urls' => $this->scopeConfig->getValue('web/url/use_store', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    ],
                'checkout_config' => [
                    'enable_guest_checkout' => $this->scopeConfig->getValue('checkout/options/guest_checkout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    'enable_agreements'     => is_null($this->scopeConfig->getValue('checkout/options/enable_agreements', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) ? 0 : $this->scopeConfig->getValue('checkout/options/enable_agreements', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                ],
                'catalog' => [
                    'frontend' => [
                        'default_sort_by' => $this->scopeConfig->getValue('catalog/frontend/default_sort_by', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                        ],
                    ],
                ],
        ];

        if(empty($info['store_info']['currency_symbol'])){
            $info['store_info']['currency_symbol'] = $info['store_info']['currency_code'];
        }
		
		return $info;
	}

    public function _getCounties()
    {
        $list = [];        
        $country_default = $this->scopeConfig->getValue('general/country/default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        $countryHelper = $this->getCoreModel('Magento\Directory\Model\Config\Source\Country'); 
        $countryFactory = $this->getCoreModel('Magento\Directory\Model\CountryFactory');
        
        $countries = $countryHelper->toOptionArray(); //Load an array of countries
        
        foreach ( $countries as $countryKey => $country ) {
            if ( $country['value'] != '' ) { //Ignore the first (empty) value
                $cache = null;
                if ($country_default == $country['value']){
                    $cache = [
                        'iso2'   => $country['value'],
                        'name'   => $country['label'],
                        'states' =>  $this->_getStates(['country_code' => $country['value']]),
                    ];
                }
                else{
                    $list[] = [
                        'iso2'   => $country['value'],
                        'name'   => $country['label'],
                        'states' => $this->_getStates(['country_code' => $country['value']]),
                    ];
                }
            }
        }

        if(!empty($list)){
            $iso2 = [];
            $name = [];
            foreach ($list as $key => $row){
                $iso2[$key]  = $row['iso2'];
                $name[$key] = $row['name'];
            }
            array_multisort($name, SORT_ASC, $iso2, SORT_DESC, $list);
        }

        if($cache){
            array_unshift($list, $cache);
        }        
        return $list;        
    }

    public function _getStates($data)
    {
        $code = $data['country_code'];
        $countryFactory = $this->getCoreModel('Magento\Directory\Model\CountryFactory');
        $list = [];
        if ($code) {
            $states = $countryFactory->create()->setId($code)->getLoadedRegionCollection()->toOptionArray();

            if(count($states))
            {
                foreach ($states as $state) {
                    $list[] = [
                        'region_id' => $state['value'],
                        'name'      =>  $state['label'],
                        'code'      => $state['value'],
                    ];
                }
            }
            return $list;
        }
        else{
            return [];
        }
    }

    public function _getAgreements()
    {
        if(!$this->scopeConfig->getValue('checkout/options/enable_agreements')){
            $agreements = [];
            return $agreements;
        }
        else{
            $agreements = Mage::getModel('checkout/agreement')->addStoreFilter($this->storeManager->getStore()->getId())
                ->addFieldToFilter('is_active', 1);
            return $agreements->getData();
        }
    }

    public function getAgreements()
    {
        $info = $this->successStatus();
        $info['data'] = $this->_getAgreements();
        return $info;
    }

    public function setAppStore($storeId, $currency = null)
    {
        $store = $this->storeManager->getStore($storeId);
        if($store->getId()){
            $storeId = $store->getId();
            
            $this->getCoreModel('Magento\Store\Model\StoreCookieManager')->setStoreCookie($store);
            $this->storeManager->setCurrentStore(
                $this->storeManager->getStore($storeId)->getCode()
            );

            if ($currency) {
                $this->storeManager->getStore()->setCurrentCurrencyCode($currency);
            }
            return true;
        }

        return false;
    }
}