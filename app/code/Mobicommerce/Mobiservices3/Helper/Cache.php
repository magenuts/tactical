<?php
namespace Mobicommerce\Mobiservices3\Helper;

class Cache extends \Magento\Framework\App\Helper\AbstractHelper {

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Mobicommerce\Mobiadmin3\Helper\Data
     */
    protected $mobiadmin3Helper;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Applications\CollectionFactory
     */
    protected $mobiadmin3ResourceApplicationsCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory
     */
    protected $mobiadmin3ResourceAppsettingCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Categoryicon\CollectionFactory
     */
    protected $mobiadmin3ResourceCategoryiconCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Categorywidget\CollectionFactory
     */
    protected $mobiadmin3ResourceCategorywidgetCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Widget\CollectionFactory
     */
    protected $mobiadmin3ResourceWidgetCollectionFactory;
    protected $_dir;   
     
    protected $categoryTree;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected $categoryCollection;

    protected $categoryCollectionFactory;
    protected $productModel;
    protected $catalogConfig;
     
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mobicommerce\Mobiadmin3\Helper\Data $mobiadmin3Helper,
        \Mobicommerce\Mobiadmin3\Model\Resource\Applications\CollectionFactory $mobiadmin3ResourceApplicationsCollectionFactory,
        \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory $mobiadmin3ResourceAppsettingCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Mobicommerce\Mobiadmin3\Model\Resource\Categoryicon\CollectionFactory $mobiadmin3ResourceCategoryiconCollectionFactory,
        \Mobicommerce\Mobiadmin3\Model\Resource\Categorywidget\CollectionFactory $mobiadmin3ResourceCategorywidgetCollectionFactory,
        \Mobicommerce\Mobiadmin3\Model\Resource\Widget\CollectionFactory $mobiadmin3ResourceWidgetCollectionFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection,
        \Magento\Catalog\Model\Category $categoryCollectionFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Model\Config $catalogConfig
    ) {
        $this->storeManager = $storeManager;
        $this->mobiadmin3Helper = $mobiadmin3Helper;
        $this->mobiadmin3ResourceApplicationsCollectionFactory = $mobiadmin3ResourceApplicationsCollectionFactory;
        $this->mobiadmin3ResourceAppsettingCollectionFactory = $mobiadmin3ResourceAppsettingCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->mobiadmin3ResourceCategoryiconCollectionFactory = $mobiadmin3ResourceCategoryiconCollectionFactory;
        $this->mobiadmin3ResourceCategorywidgetCollectionFactory = $mobiadmin3ResourceCategorywidgetCollectionFactory;
        $this->mobiadmin3ResourceWidgetCollectionFactory = $mobiadmin3ResourceWidgetCollectionFactory;
        $this->_dir = $dir;  
        
        $this->categoryTree = $categoryTree;
        
        $this->categoryCollection = $categoryCollection;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productModel = $productModel;
        $this->catalogConfig = $catalogConfig;
        parent::__construct($context);
    }

    public function getModel($modelPath)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connector = $objectManager->create('\Mobicommerce\Mobiservices3\Block\Connector');
        $model = $objectManager->create($connector->_getConnectorModel($modelPath));
        return $model;
    }    

    public function getMobiHelper($helperPath)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection
        $productCollection */
        $helper = $objectManager->create($helperPath);
        /** Apply filters here */
        return $helper;
    }
    
    public function getCoreHelper($helperPath)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection
        $productCollection */
        $helper = $objectManager->create($helperPath);
        /** Apply filters here */
        return $helper;
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

    public function createCacheDir($appcode)
    {
        $store = $this->storeManager->getStore()->getId();
        $currency_code = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $path = $this->_dir->getPath("media").'/mobi_commerce/'.$appcode.'/cache';
        if(!file_exists($path))
            mkdir($path, 0755, TRUE);

        if(!file_exists($path.'/'.'store'))
            mkdir($path.'/'.'store', 0755, TRUE);

        if(!file_exists($path.'/'.'store'.'/'.$store))
            mkdir($path.'/'.'store'.'/'.$store, 0755, TRUE);

        $cachepath_root = $this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.$appcode.'/'.'cache';
        $cachepath_language = $this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.'v'.'/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/'.'cache'.'/'.'language';

        if(!file_exists($this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.'v')){
            @mkdir($this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.'v', 0755);
        }
        if(!file_exists($this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.'v'.'/'.$this->mobiadmin3Helper->getMobiBaseVersion())){
            @mkdir($this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.'v'.'/'.$this->mobiadmin3Helper->getMobiBaseVersion(), 0755);
        }
        if(!file_exists($this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.'v'.'/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/'.'cache')){
            @mkdir($this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.'v'.'/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/'.'cache', 0755);
        }
        if(!file_exists($this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.'v'.'/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/'.'cache'.'/'.'store')){
            @mkdir($this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.'v'.'/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/'.'cache'.'/'.'store', 0755);
        }

        $cachepath_store = $this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.$appcode.'/'.'cache'.'/'.'store'.'/'.$store;
        $cachepath_store_v = $this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.'v'.'/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/'.'cache'.'/'.'store'.'/'.$store;
        $cachepath_store_currency = $this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.$appcode.'/'.'cache'.'/'.'store'.'/'.$store.'/'.$currency_code;
        $cachepath_catproducts = $this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.$appcode.'/'.'cache'.'/'.'store'.'/'.$store.'/'.$currency_code.'/'.'category_products';
        $cachepath_catwidgets = $this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.'v'.'/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/'.'cache'.'/'.'store'.'/'.$store.'/'.$currency_code.'/'.'category_widgets';

        $data['cachepath_root']           = $cachepath_root;
        $data['cachepath_store']          = $cachepath_store;
        $data['cachepath_store_v']        = $cachepath_store_v;
        $data['cachepath_store_currency'] = $cachepath_store_currency;
        $data['cachepath_language']       = $cachepath_language;
        $data['cachepath_catproducts']    = $cachepath_catproducts;
        $data['cachepath_catwidgets']     = $cachepath_catwidgets;
        foreach($data as $key => $value){
            if(!file_exists($value)){
                @mkdir($value, 0755);
            }
        }
        return $data;
    }

    public function flushAllCache()
    {
        $collection = $this->mobiadmin3ResourceApplicationsCollectionFactory->create();

        $paths = [];
        $paths[] = $this->_dir->getPath("media").'/mobi_commerce/v/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/cache';

        if($collection->getSize() > 0){
            foreach($collection as $_collection){
                $paths[] = $this->_dir->getPath("media").'/mobi_commerce/'.$_collection->getAppCode().'/cache';
            }
        }

        foreach($paths as $path){
            if(file_exists($path)){
                $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->rrmdir($path);
            }
        }
    }

    /**
     * This function is used to flush and regenerate cache data
     */
    public function flushAndRegenerateAllCache()
    {
        $this->flushAllCache();
    }

    /**
     * This function is used to generate cache file for specific type of caching.
     */
    public function setCacheData($type, $appcode, $params = null)
    {
        if(empty($appcode))
            return false;

        $paths = $this->createCacheDir($appcode);
        $store = $this->storeManager->getStore()->getId();
        $data = [];
        
        switch ($type) {
            case 'homepage_widgets':
                $data = $this->_getWidgets($appcode);
                file_put_contents($paths['cachepath_store_currency'].'/'.'homepage_widgets.txt', serialize($data));
                //echo '<pre>';print_r($data);exit;
                break;
            case 'category':
                $rootCategoryId = $this->storeManager->getStore($store)->getRootCategoryId();
                $tree = $this->categoryTree->load();
                
                $root = $tree->getNodeById($rootCategoryId);
                if($root && $root->getId() == 1) { 
                    $root->setName(__('Root')); 
                }
                
                $collection = $this->categoryCollection->addAttributeToSelect('name')
                    ->addAttributeToFilter('is_active','1');

                $tree->addCollectionData($collection, true);
                $data = $this->_nodeToArray($root, $store);
                $data = $this->_make_tree_to_list($data['children']);
                $data = $this->_remove_category_children($data);
                $data = $this->_assignCategoryThumbnail($data);
                
                file_put_contents($paths['cachepath_store_v'].'/'.'category.txt', serialize($data));
                //echo '<pre>';print_r($data);exit;
                break;
            case 'category_products':
                if(isset($params['categoryId']) && !empty($params['categoryId'])){
                    $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
                    $catalog_helper = $object_manager->get('\Magento\Catalog\Helper\Product');

                    $category = $this->categoryCollectionFactory->load($params['categoryId']);
                    $pCollection = $category->getProductCollection();
                    $pCollection->addAttributeToSelect($this->catalogConfig->getProductAttributes())
                        ->addAttributeToFilter('status', '1')
                        ->addAttributeToFilter('visibility', '4')
                        ->setStoreId($store)
                        ->addMinimalPrice()
                        ->addFinalPrice();

                    $advanceSettings = $this->_getAdvanceSettings($appcode);

                    if(isset($advanceSettings['productlist']['default_sorting']) && !empty($advanceSettings['productlist']['default_sorting'])){
                        switch ($advanceSettings['productlist']['default_sorting']) {
                            case "position":
                                $pCollection->setOrder('position', 'ASC');
                                break;
                            case "price-l-h": 
                                $pCollection->setOrder('price', 'asc');
                                break;
                            case "price-h-l":
                                $pCollection->setOrder('price', 'desc');
                                break;
                            case "rating-h-l":
                                $pCollection->joinField('rating_score', 
                                       'review_entity_summary', 
                                       'rating_summary', 
                                       'entity_pk_value=entity_id', 
                                       ['entity_type'=>1, 'store_id'=> $this->storeManager->getStore()->getId()],
                                       'left'
                                );
                                $pCollection->setOrder('rating_score', 'desc');
                                break;
                            case "name-a-z":
                                $pCollection->setOrder('name', 'asc');
                                break;
                            case "name-z-a":
                                $pCollection->setOrder('name', 'desc');
                                break;
                            case "newest":
                            case "newest_first":
                                $pCollection->setOrder('entity_id', 'desc');
                                break;
                            default: 
                                $pCollection->setOrder('ordered_qty', 'asc');
                                break;
                        }
                    }
                    $product_count = $pCollection->getSize();
                    $_limit = isset($params['limit']) ? $params['limit'] : 20;
                    if(empty($_limit)) $_limit = 20;
                    $pCollection->getSelect()->limit($_limit);
                    
                    $filter = $this->getModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog')->_getFilters($params['categoryId']);
                    
                    $data = [
                        'filter'        => $filter,
                        'products'      => [],
                        'product_count' => $product_count
                        ];
                    if($pCollection->getSize() > 0){
                        foreach($pCollection as $_collection){
                            $product =$this->getCoreModel('Magento\Catalog\Model\Product')->setStoreId($store)->load($_collection->getId());
                           
                            $info = $product->getData();
                            $stockItem = $product->getExtensionAttributes()->getStockItem();
                            $inventory = $this->getCoreModel('\Magento\CatalogInventory\Model\Stock\StockItemRepository')->get($stockItem->getItemId());

                            $stock = true;
                            if (!$product->isSaleable()) $stock = false;
                            if(!$inventory->getIsInStock()) $stock = false;
                            
                            if($product->getPriceInfo()->getPrice('base_price')->getAmount()->getBaseAmount())
                            {
                                $price = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($product->getPriceInfo()->getPrice('base_price')->getAmount()->getBaseAmount());
                            }
                            else
                            {
                                $price = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
                            }
                      
                            $_product = [
                                'product_id'              => $product->getId(),
                                'name'                    => $product->getName(),
                                'type'                    => $product->getTypeId(),
                                'qty_increments'          => (int) $inventory->getQtyIncrements(),
                                'price'                   => $price,
                                'special_price'           => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($product->getPriceInfo()->getPrice('special_price')->getAmount()->getBaseAmount()),
                                'stock_status'            => $stock,
                                'review_summary'          => $this->getModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog')->getReviewSummary($product, $store),
                               	'product_small_image_url' => $catalog_helper->getImageUrl($product),
                                ];

                            $_product['product_image_url'] = $_product['product_small_image_url'];
                            
                            $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Product')->addDiscount($_product);
                            $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->addImageRatio($_product);

                            $data['products'][] = $_product;
                        }
                    }
                    file_put_contents($paths['cachepath_catproducts'].'/'.$params['categoryId'].'.txt', serialize($data));
                    //echo '<pre>';print_r($data);exit;
                }
                break;
            case 'category_widgets':
                if(isset($params['categoryId']) && !empty($params['categoryId'])){
                    $data = $this->_getWidgets($appcode, $params['categoryId']);
                    file_put_contents($paths['cachepath_catwidgets'].'/'.$params['categoryId'].'.txt', serialize($data));
                    //echo '<pre>';print_r($data);exit;
                }
                break;
            case 'social_login':
                $data = $this->_getSocialLogin();
                file_put_contents($paths['cachepath_store_v'].'/'.'social_login.txt', serialize($data));
                //echo '<pre>';print_r($data);exit;
                break;
            case 'homepage_categories':
                $data = $this->_getHomepageCategories($appcode);
                file_put_contents($paths['cachepath_store'].'/'.'homepage_categories.txt', serialize($data));
                //echo '<pre>';print_r($data);exit;
                break;
            case 'advance_settings':
                $data = $this->_getAdvanceSettings($appcode);
                file_put_contents($paths['cachepath_store'].'/'.'advance_settings.txt', serialize($data));
                //echo '<pre>';print_r($data);exit;
                break;
            case 'googleanalytics':
                $data = $this->_getAnalyticsSettings($appcode);
                file_put_contents($paths['cachepath_root'].'/'.'googleanalytics.txt', serialize($data));
                //echo '<pre>';print_r($data);exit;
                break;
            case 'cms':
                $collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
                $collection
                    ->addFieldToFilter('app_code', $appcode)
                    ->addFieldToFilter('setting_code', 'cms_settings')
                    ->addFieldToFilter('storeid', $store);
                if($collection->getSize() > 0){
                    $data = @unserialize($collection->getFirstItem()->getValue());

                    if (isset($data['contact_information']['menu_icon']) && $data['contact_information']['menu_icon']) {
                        $MCMediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                        $data['contact_information']['menu_icon'] = $MCMediaUrl.$data['contact_information']['menu_icon'];
                    }
                    
                    $cmsCollection = $this->getCoreModel('\Magento\Cms\Model\Page')->getCollection()->addFieldToFilter('is_active', 1);
                    $allcms = [];
                    if($cmsCollection->getSize()){
                        foreach($cmsCollection as $_collection){
                            $allcms[$_collection->getPageId()] = $_collection->getData();
                        }
                    }
                    
                    $cms = [];
                    foreach($data['cms_pages'] as $key => $value){
                        if(isset($allcms[$value['id']])){
                            $cms[] = [
                                'page_id' => $allcms[$value['id']]['page_id'],
                                'title'   => $allcms[$value['id']]['title'],
                                //'content' => $allcms[$value['id']]['content']
                                ];
                        }
                    }
                    $data['cms_pages'] = $cms;
                    file_put_contents($paths['cachepath_store'].'/'.'cms.txt', serialize($data));
                    //echo '<pre>';print_r($data);exit;
                }
                break;
            case 'language':
                if(is_numeric($store) || $store === true)
                    $locale = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
                else
                    $locale = $store;

                $language = $this->mobiadmin3Helper->getLanguageData($locale);
                $data = [];
                foreach($language as $key => $label){
                    $data[] = [
                        'code' => $key,
                        'text' => $label['mm_text'],
                        ];
                }
                file_put_contents($paths['cachepath_language'].'/'.$locale.'.txt', serialize($data));
                //echo '<pre>';print_r($language);exit;
                break;
            case 'appinfo':
                $collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
                $collection
                    ->addFieldToFilter('app_code', $appcode)
                    ->addFieldToFilter('setting_code', 'appinfo');
                if($collection->getSize() > 0){
                    $data = @unserialize($collection->getFirstItem()->getValue());
                    file_put_contents($paths['cachepath_root'].'/'.'appinfo.txt', serialize($data));
                    //echo '<pre>';print_r($data);exit;
                }
                break;
            case 'pushsettings':
                $collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
                $collection
                    ->addFieldToFilter('app_code', $appcode)
                    ->addFieldToFilter('setting_code', 'push_notification');
                if($collection->getSize() > 0){
                    $data = @unserialize($collection->getFirstItem()->getValue());
                    file_put_contents($paths['cachepath_root'].'/'.'pushsettings.txt', serialize($data));
                    //echo '<pre>';print_r($data);exit;
                }
                break;
            case 'storeinfo':
                $currencyCode = $this->storeManager->getStore($store)->getCurrentCurrencyCode();
                $data = [
                    'store_id'          => $this->storeManager->getStore($store)->getId(),
                    'store_name'        => $this->storeManager->getStore($store)->getName(),
                    'store_code'        => $this->storeManager->getStore($store)->getCode(),
                    'locale_identifier' => $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                    'root_category_id'  => $this->storeManager->getStore($store)->getRootCategoryId(),
                    'currency_name'     => Mage::app()->getLocale($store)->currency($currencyCode)->getName(),
                    'currency_symbol'   => Mage::app()->getLocale($store)->currency($currencyCode)->getSymbol(),
                    'currency_code'     => $this->storeManager->getStore($store)->getCurrentCurrencyCode(),
                    'base_url'          => $this->scopeConfig->getValue('web/unsecure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                    'storeConfig'       => [
                        'web' => [
                            'add_store_code_to_urls' => $this->scopeConfig->getValue('web/url/use_store', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                            ],
                        ]
                    ];

                if(empty($data['currency_symbol'])){
                    $data['currency_symbol'] = $data['currency_code'];
                }
                file_put_contents($paths['cachepath_store_currency'].'/'.'storeinfo.txt', serialize($data));
                //echo '<pre>';print_r($data);exit;
                break;
            case 'country':
                $data = [];        
                $country_default = $this->scopeConfig->getValue('general/country/default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                
                $countryHelper = $this->getCoreModel('Magento\Directory\Model\Config\Source\Country'); 
                $countryFactory = $this->getCoreModel('Magento\Directory\Model\CountryFactory');
                
                $countries = $countryHelper->toOptionArray(); //Load an array of countries
                $cache = null;
                foreach ( $countries as $countryKey => $country ) {
                    if ( $country['value'] != '' ) { //Ignore the first (empty) value
                        if ($country_default == $country['value']){
                            $cache = [
                                'iso2'   => $country['value'],
                                'name'   => $country['label'],
                                'states' => $this->getModel('Mobicommerce\Mobiservices3\Model\Config')->_getStates(['country_code' => $country['value']]),
                            ];
                        }
                        else{
                            $data[] = [
                                'iso2'   => $country['value'],
                                'name'   => $country['label'],
                                'states' => $this->getModel('Mobicommerce\Mobiservices3\Model\Config')->_getStates(['country_code' => $country['value']]),
                            ];
                        }
                    }
                }

                if(!empty($data)){
                    $iso2 = [];
                    $name = [];
                    foreach ($data as $key => $row){
                        $iso2[$key]  = $row['iso2'];
                        $name[$key] = $row['name'];
                    }
                    array_multisort($name, SORT_ASC, $iso2, SORT_DESC, $data);
                }

                if($cache){
                    array_unshift($data, $cache);
                }        
                file_put_contents($paths['cachepath_store'].'/'.'country.txt', serialize($data));
                break;
            case 'stores':
                $data = [];

                $storeViews = $this->storeManager->getStores();
                foreach($storeViews as $_store){
                    if($_store->getIsActive() == '1'){
                        $s = $_store->getData();
                        $s['base_url'] = $this->scopeConfig->getValue('web/unsecure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $_store->getId());
                        $s['base_secure_url'] = $this->scopeConfig->getValue('web/secure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $_store->getId());
                        $s['config'] = [
                            'web_url_use_store' => $this->scopeConfig->getValue('web/url/use_store', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $_store->getId()),
                            'default_country' => $this->scopeConfig->getValue('general/country/default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $_store->getId()),
                            'display_state' => $this->scopeConfig->getValue('general/region/display_all', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $_store->getId())
                            ];
                        $s['root_category_id']  = $this->storeManager->getStore($_store->getId())->getRootCategoryId();
                        $s['group_name'] = $this->storeManager->getStore($_store->getId())->getGroup()->getName();
                        
                        if($_store->getStoreId() == $this->storeManager->getStore()->getStoreId()){
                            $codes = $this->storeManager->getStore()->getAvailableCurrencyCodes(true);

                            $available_currency_codes = $this->storeManager->getStore($store)->getAvailableCurrencyCodes(true);
                            
                            $currencies = $available_currency_codes;
                            $s['currencies'] = [];
                            foreach($currencies as $_currency){
                                $currencyFactory = $this->getCoreModel('Magento\Directory\Model\Currency')->load($_currency);
                                
                                $s['currencies'][] = [
                                    'name'   => $this->getCoreModel('\Magento\Framework\Locale\CurrencyInterface')->getCurrency($_currency)->getName(),
                                    'symbol' => $currencyFactory->getCurrencySymbol(),
                                    'code'   => $_currency
                                    ];
                            }
                            
                            $currencyCode = $this->storeManager->getStore($store)->getCurrentCurrencyCode();
                            $currencyFactory = $this->getCoreModel('Magento\Directory\Model\CurrencyFactory')->create()->load($currencyCode);
                           
                            $s['currency'] = [
                                'name'   => $currencyFactory->getCurrencyName(),
                                'symbol' => $currencyFactory->getCurrencySymbol(),
                                'code'   => $currencyCode
                                ];
                            $s['config']['web_secure_use_in_frontend'] = $this->scopeConfig->getValue('web/secure/use_in_frontend', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $_store->getId());

                            if($s['base_url'] == $s['base_secure_url'])
                            {
                                $s['config']['web_secure_use_in_frontend'] = 0;
                            }

                            $s['config']['no_image_url'] = $this->productModel->getSmallImageUrl(200, 200);
                            $s['config']['customer'] = [];
                            $s['config']['customer']['address'] = [
                                'prefix_show'    => $this->scopeConfig->getValue('customer/address/prefix_show', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                                'prefix_options' => $this->scopeConfig->getValue('customer/address/prefix_options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                                'dob_show'       => $this->scopeConfig->getValue('customer/address/dob_show', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                                'taxvat_show'    => $this->scopeConfig->getValue('customer/address/taxvat_show', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                                'gender_show'    => $this->scopeConfig->getValue('customer/address/gender_show', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                                'street_lines'   => $this->scopeConfig->getValue('customer/address/street_lines', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                                ];

                            $locale = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
                            if(strpos($locale, 'ar_') !== false || strpos($locale, 'fa_') !== false) {
                                $s['rtl'] = '1';
                            }
                        }
                        $data[] = $s;
                    }
                }

                file_put_contents($paths['cachepath_store_v'].'/'.'stores.txt', serialize($data));
                //echo '<pre>';print_r($data);exit;
                break;
            default:
                # code...
                break;
        }

        return $data;
    }

    /**
     * This function is used to get cache data
     * If file does not exists, then it will create cache file.
     */
    public function getCacheData($type, $appcode, $params = null)
    {
        if(empty($appcode))
            return false;

        $store = $this->storeManager->getStore()->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connector = $objectManager->create('\Mobicommerce\Mobiservices3\Block\Connector');
        $_isMobiCacheEnabled = $connector->isMobiCacheEnabled();
        if(!$_isMobiCacheEnabled){
            return $this->setCacheData($type, $appcode, $params);
        }

        $paths = $this->createCacheDir($appcode);
        switch ($type) {
            case 'homepage_widgets':
                if(!file_exists($paths['cachepath_store_currency'].'/'.'homepage_widgets.txt')){
                    $this->setCacheData($type, $appcode);
                }
                return @unserialize(file_get_contents($paths['cachepath_store_currency'].'/'.'homepage_widgets.txt'));
                break;
            case 'category':
                if(!file_exists($paths['cachepath_store_v'].'/'.'category.txt')){
                    $this->setCacheData($type, $appcode);
                }
                return @unserialize(file_get_contents($paths['cachepath_store_v'].'/'.'category.txt'));
                break;
            case 'category_products':
                if(!file_exists($paths['cachepath_catproducts'].'/'.$params['categoryId'].'.txt')){
                    $this->setCacheData($type, $appcode, $params);
                }
                return @unserialize(file_get_contents($paths['cachepath_catproducts'].'/'.$params['categoryId'].'.txt'));
                break;
            case 'category_widgets':
                if(!file_exists($paths['cachepath_catwidgets'].'/'.$params['categoryId'].'.txt')){
                    $this->setCacheData($type, $appcode, $params);
                }
                return @unserialize(file_get_contents($paths['cachepath_catwidgets'].'/'.$params['categoryId'].'.txt'));
                break;
            case 'social_login':
                if(!file_exists($paths['cachepath_store_v'].'/'.'social_login.txt')){
                    $this->setCacheData($type, $appcode);
                }
                return @unserialize(file_get_contents($paths['cachepath_store_v'].'/'.'social_login.txt'));
                break;
            case 'homepage_categories':
                if(!file_exists($paths['cachepath_store'].'/'.'homepage_categories.txt')){
                    $this->setCacheData($type, $appcode);
                }
                return @unserialize(file_get_contents($paths['cachepath_store'].'/'.'homepage_categories.txt'));
                break;    
            case 'advance_settings':
                if(!file_exists($paths['cachepath_store'].'/'.'advance_settings.txt')){
                    $this->setCacheData($type, $appcode);
                }
                return @unserialize(file_get_contents($paths['cachepath_store'].'/'.'advance_settings.txt'));
                break;
            case 'googleanalytics':
                if(!file_exists($paths['cachepath_root'].'/'.'googleanalytics.txt')){
                    $this->setCacheData($type, $appcode);
                }
                return @unserialize(file_get_contents($paths['cachepath_root'].'/'.'googleanalytics.txt'));
                break;
            case 'cms':
                if(!file_exists($paths['cachepath_store'].'/'.'cms.txt')){
                    $this->setCacheData($type, $appcode);
                }
                return @unserialize(file_get_contents($paths['cachepath_store'].'/'.'cms.txt'));
                break;
            case 'language':
                $locale = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
                if(!file_exists($paths['cachepath_language'].'/'.$locale.'.txt')){
                    $this->setCacheData($type, $appcode);
                }
                return @unserialize(file_get_contents($paths['cachepath_language'].'/'.$locale.'.txt'));
                break;
            case 'appinfo':
                if(!file_exists($paths['cachepath_root'].'/'.'appinfo.txt')){
                    $this->setCacheData($type, $appcode);
                }
                return @unserialize(file_get_contents($paths['cachepath_root'].'/'.'appinfo.txt'));
                break;
            case 'pushsettings':
                if(!file_exists($paths['cachepath_root'].'/'.'pushsettings.txt')){
                    $this->setCacheData($type, $appcode);
                }
                return @unserialize(file_get_contents($paths['cachepath_root'].'/'.'pushsettings.txt'));
                break;
            case 'storeinfo':
                if(!file_exists($paths['cachepath_store_currency'].'/'.'storeinfo.txt')){
                    $this->setCacheData($type, $appcode);
                }
                return @unserialize(file_get_contents($paths['cachepath_store_currency'].'/'.'storeinfo.txt'));
                break;
            case 'country':
                if(!file_exists($paths['cachepath_store'].'/'.'country.txt')){
                    $this->setCacheData($type, $appcode);
                }
                return @unserialize(file_get_contents($paths['cachepath_store'].'/'.'country.txt'));
                break;
            case 'stores':
                if(!file_exists($paths['cachepath_store_v'].'/'.'stores.txt')){
                    $this->setCacheData($type, $appcode);
                }
                return @unserialize(file_get_contents($paths['cachepath_store_v'].'/'.'stores.txt'));
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * This function is used to set product detail cache
     */
    public function setProductCache($productId)
    {
        $store = $this->storeManager->getStore()->getId();
        $currency_code = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $path = $this->_dir->getPath("media").'/'.'mobi_commerce'.'/'.'v'.'/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/'.'cache'.'/'.'store'.'/'.$store.'/'.$currency_code.'/'.'product';
        @mkdir($path, 0755, TRUE);
        
        $product = $this->getCoreModel('\Magento\Catalog\Model\Product')->setStoreId($store)->load($productId);

        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $catalog_helper = $object_manager->get('\Magento\Catalog\Helper\Product');
        
        if($product->getId()){
            $option =$this->getModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog')->_getAllProductOptions($product);
            $prices = $this->getModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog')->_productPrices($product);
           
            $images = [];
            
            foreach ($product->getMediaGallery('images') as $image) {
                if($image['disabled']){
                    continue;
                }
                
                $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $fullimage = $mediaUrl.'catalog/product'.$image['file'];
                                                
                $_image = [
                    'full_image_url' => $fullimage,
                    'id'             => isset($image['value_id']) ? $image['value_id'] : null,
                    'position'       => $image['position'],
                    'label'          => $image['label'],
                    ];
                $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->addImageRatio($_image);
                $images[] = $_image;
            }
            
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $fullimage = $mediaUrl.'catalog/product'.$this->getCoreHelper('Magento\Catalog\Helper\Image')->init($product, 'image')->getUrl();
                                    
            if(empty($images)){
                try{
                    $_image = [
                        'full_image_url' => $fullimage,
                        'id'             => '0',
                        'position'       => '1',
                        'label'          => 'Base Image',
                        ];
                    $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->addImageRatio($_image);
                    $images[] = $_image;
                }
                catch(Exception $e){}
            }

            if($product->getTypeId() == 'configurable'){
                $configurable_images = [];
                $associated_products = $product->loadByAttribute('sku', $product->getSku())->getTypeInstance()->getUsedProducts($product);
               
                if (count($associated_products) > 0) {
                    foreach($associated_products as $key => $ap){
                        if($key > 0)
                            continue;
                            
                        $ap = $this->getCoreModel('\Magento\Catalog\Model\Product')->setStoreId($store)->load($ap->getId());
                        foreach ($ap->getMediaGallery('images') as $image) {
                            if($image['disabled']){
                                continue;
                            }
                            
                            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                            $fullimage = $mediaUrl.'catalog/product'.$image['file'];
                                                                                    
                            $_configurable_image = [
                                'full_image_url' => $fullimage,
                                'id'             => isset($image['value_id']) ? $image['value_id'] : null,
                                'position'       => $image['position'],
                                'label'          => $image['label'],
                                ];
                           $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->addImageRatio($_configurable_image);
                            $configurable_images[] = $_configurable_image;
                       }
                    }
                }
                
                if(!empty($configurable_images)){
                    $images = $configurable_images;
                }
            }
          
            $stockItem = $product->getExtensionAttributes()->getStockItem();
            $inventory = $this->getCoreModel('\Magento\CatalogInventory\Model\Stock\StockItemRepository')->get($stockItem->getItemId());
            
            $stock = true;
            if (!$product->isSaleable()) $stock = false;
            if(!$inventory->getIsInStock()) $stock = false;
            
            $productData = $product->getData();
            $price = 0;
            if($product->getPriceInfo()->getPrice('base_price')->getAmount()->getBaseAmount())
            {
                $price = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($product->getPriceInfo()->getPrice('base_price')->getAmount()->getBaseAmount());
            }
            else
            {
                $price = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
            }
           
            $_product = [
                'product_id'            => $product->getId(),
                'name'                  => $product->getName(),
                'type'                  => $product->getTypeId(),
                'type_id'               => $product->getTypeId(),
                'sku'                   => $product->getSku(),
                'url'                   => $product->getProductUrl(),
                'price'                   => $price,
				'special_price'           => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($product->getPriceInfo()->getPrice('special_price')->getAmount()->getBaseAmount()),
                
                'description'           => $this->getCoreHelper('Magento\Catalog\Helper\Output')->productAttribute($product, $product->getDescription(), 'description'),
                'short_description'     => $this->getCoreHelper('Magento\Catalog\Helper\Output')->productAttribute($product, $product->getShortDescription(), 'short_description'),
                'max_qty'               => (int) $inventory->getQty(),
                'threshold_qty'         => $this->scopeConfig->getValue('cataloginventory/options/stock_threshold_qty'),
                'qty_increments'        => (int) $inventory->getQtyIncrements(),
                'product_images'        => $images,
                'product_thumbnail_url' => NULL,
                'stock_status'          => $stock,
                'options'               => $option,
                'review_summary'        =>$this->getModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog')->getReviewSummary($product, $store),
                'product_thumbnail_url' => $catalog_helper->getImageUrl($product)
            ];
          
            $reviews =$this->getModel('Mobicommerce\Mobiservices3\Model\Review')->getReviews([
                'page'       => 1,
                'limit'      => 3,
                'product_id' => $product->getId(),
                'store'      => $store
                ]);
            $_product['reviews'] = $reviews['data']['reviews'];

            $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->addImageRatio($_product);

            if ($prices){
                $_product = array_merge($_product, $prices);
            }

            $_product = $this->getModel('Mobicommerce\Mobiservices3\Model\Custom')->getCustomProductDetailFields($product, $_product);

            $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Product')->addDiscount($_product);
            $_product['relatedProducts']['products'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog')->getRelatedProducts($product, $store);
            $_product['ratingOptions'] =$this->getModel('Mobicommerce\Mobiservices3\Model\Review')->_getRatingOptions(["product_id" => $productId], $store);
            
            file_put_contents($path.'/'.$productId.'.txt', serialize($_product));
            //echo '<pre>';print_r($_product);exit;
            return $_product;
        }
        return null;
    }

    /**
     * This function is used to get product detail cache
     */
    public function getProductCache($productId)
    {
        $store = $this->storeManager->getStore()->getId();
        $currency_code = $this->storeManager->getStore()->getCurrentCurrencyCode();
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connector = $objectManager->create('\Mobicommerce\Mobiservices3\Block\Connector');
        $_isMobiCacheEnabled = $connector->isMobiCacheEnabled();
        if(!$_isMobiCacheEnabled){
            return $this->setProductCache($productId);
        }
        
        $path = $this->_dir->getPath("media").'/mobi_commerce/v/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/cache/store/'.$store.'/'.$currency_code.'/product';
        if(!file_exists($path.'/'.$productId.'.txt')){
            $this->setProductCache($productId);
        }
        return @unserialize(file_get_contents($path.'/'.$productId.'.txt'));
    }

    public function clearProductCache($productId = null)
    {
        $this->flushAllCache();
        return;
    }

    public function clearCmsCache()
    {
        $this->flushAllCache();
        return;
    }

    /**
     * This function will be used as hook.
     * When there is certain change in either category or products,
     * we will destroy cache file of that product'c category or that category file
     * depend on either product is changed or category is changed
     */
    public function clearCategoryProductsCache($params = null)
    {
        $this->flushAllCache();
        return;
    }

    protected function _nodeToArray(\Magento\Framework\Data\Tree\Node $node, $store) 
    { 
        $result = [];
        if(!$node)
            return $result;
        
        $category = $this->categoryCollectionFactory->load($node->getId());
        $result['category_id']    = $node->getId();
        $result['parent_id']      = $node->getParentId();
        $result['name']           = $category->getName();
        
        $result['products_count'] = $category->getProductCollection()->addAttributeToFilter('status', '1')->addAttributeToFilter('visibility', '4')->setStoreId($store)->addMinimalPrice()->getSize();
        
        $result['display_mode']   = $category->getDisplayMode();
        $result['thumbnail_url']  = null;
        $result['banner_url']  = null;
        $result['has_widgets']  = false;
        if($image = $category->getThumbnail()){
            $result['thumbnail_url']  = $this->_dir->getUrlPath("media").'catalog/category/'.$image;
        }

        $result['children'] = [];
        foreach ($node->getChildren() as $child) {
            $result['children'][] = $this->_nodeToArray($child, $store); 
        }

        return $result; 
    }

    protected function _make_tree_to_list($categories = null, $category_result = [])
    {
        if(!empty($categories)){
            foreach($categories as $category){
                $category_result[] = $category;
                if(isset($category['children']) && count($category['children']) > 0){
                    $category_result = $this->_make_tree_to_list($category['children'], $category_result);
                }
            }
        }
        return $category_result;
    }

    protected function _remove_category_children($categories = [])
    {
        $_category_array = [];
        if(!empty($categories)){
            foreach($categories as $key => $category){
                $categories[$key]['children'] = isset($category['children']) ? count($category['children']) : 0;

            $_category_array[] = $categories[$key];
            }
        }
        return $_category_array;
    }

    protected function _assignCategoryThumbnail($categories)
    {
        $allcategories = [];
        $categoryIds = [];
        if(!empty($categories)) {
            foreach ($categories as $key => $value) {
                $allcategories[$value['category_id']] = $value;
                $categoryIds[] = $value['category_id'];
            }
        }

        $media_url = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $collection = $this->mobiadmin3ResourceCategoryiconCollectionFactory->create();
        $collection->addFieldToFilter('mci_category_id', ['in' => $categoryIds]);
        if($collection->getSize() > 0) {
            foreach($collection as $_collection) {
                $thumbnail = $_collection->getMciThumbnail();
                $banner = $_collection->getMciBanner();
                if(!empty($thumbnail)) {
                    $allcategories[$_collection->getMciCategoryId()]['thumbnail_url'] = $media_url.'mobi_commerce/category/'.$thumbnail;
                }
                if(!empty($banner)) {
                    $allcategories[$_collection->getMciCategoryId()]['banner_url'] = $media_url.'mobi_commerce/category/'.$banner;
                }
            }
        }

        $collection = $this->mobiadmin3ResourceCategorywidgetCollectionFactory->create();
        $collection->addFieldToFilter('widget_category_id', ['in' => $categoryIds]);
        if($collection->getSize() > 0) {
            foreach($collection as $_collection) {
                $allcategories[$_collection->getWidgetCategoryId()]['has_widgets'] = true;
            }
        }
        return array_values($allcategories);
    }

    public function _getAdvanceSettings($appcode)
    {
        $collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
        $collection->addFieldToFilter('app_code', $appcode)
            ->addFieldToFilter('setting_code', 'advance_settings');

        if($collection->getSize() > 0){
            return @unserialize($collection->getFirstItem()->getValue());
        }
        return null;
    }

    public function _getHomepageCategories($appcode)
    {
        $store = $this->storeManager->getStore()->getId();
        $collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
        $collection->addFieldToFilter('app_code', $appcode)
            ->addFieldToFilter('setting_code', 'homepage_categories')
            ->addFieldToFilter('storeid', $store);

        if($collection->getSize() > 0){
            return @unserialize($collection->getFirstItem()->getValue());
        }
        return null;
    }

    public function _getAnalyticsSettings($appcode)
    {
        $collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
        $collection->addFieldToFilter('app_code', $appcode)
            ->addFieldToFilter('setting_code', 'googleanalytics');
        if($collection->getSize() > 0){
            return @unserialize($collection->getFirstItem()->getValue());
        }
        return null;
    }

    protected function _getWidgets($appcode, $categoryId = null)
    {
        $store = $this->storeManager->getStore()->getId();
        $media_url = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $catalog_helper = $object_manager->get('\Magento\Catalog\Helper\Product');

        if(empty($categoryId)){
            $collection = $this->mobiadmin3ResourceWidgetCollectionFactory->create();
            $collection->addFieldToFilter('widget_app_code', $appcode)
                ->addFieldToFilter('widget_store_id', $store);
        }
        else{
            $collection = $this->mobiadmin3ResourceCategorywidgetCollectionFactory->create();
            $collection->addFieldToFilter('widget_category_id', $categoryId);
        }
        $collection->addFieldToFilter('widget_status', '1');
        $collection->setOrder('widget_position', 'ASC');
        $data = [];
        if($collection->getSize() > 0){
            foreach($collection as $_collection){
                $_data = [
                    'widget_id'    => $_collection->getWidgetId(),
                    'widget_label' => $_collection->getWidgetLabel(),
                    'widget_code'  => $_collection->getWidgetCode(),
                    'widget_data'  => @unserialize($_collection->getWidgetData()),
                    ];
                                    
                switch($_data['widget_code']){
                    case 'widget_category':
                        $output_ids = [];
                        $ids = @json_decode($_data['widget_data']['categories'], true);
                        
                        if(is_array($ids) && !empty($ids)){
                            
                            $temp_ids = [];
                            $_category_array = [];
                            $_position_array = [];
                            foreach ($ids as $_category => $_position){
                                $temp_ids[] = [
                                    'category' => $_category,
                                    'position' => $_position
                                    ];
                                $_category_array[$_category] = $_category;
                                $_position_array[$_category] = $_position;
                            }
                            array_multisort($_position_array, SORT_ASC, $_category_array, SORT_ASC, $temp_ids);

                            $ids = [];
                            foreach ($temp_ids as $key => $value) {
                                $ids[$value['category']] = $value['position'];
                            }
                        }

                        $categoryArray = [];
                        $categories = $this->getCacheData('category', $appcode);

                        foreach($categories as $_category){
                            $categoryArray[$_category['category_id']] = $_category;
                        }
                        if(is_array($ids) && !empty($ids)){
                            foreach($ids as $_key => $_value){
                                if(isset($categoryArray[$_key]))
                                    $output_ids[] = $categoryArray[$_key];
                            }
                        }

                        if(empty($output_ids))
                            $_data = null;
                        else
                            $_data['widget_data']['categories'] = $output_ids;
                        break;
                    case 'widget_product_slider':
                        $productResultArray = [];
                        if(!in_array($_data['widget_data']['productslider_type'], ['newarrivals', 'bestseller', 'productviewed'])){
                            $ids = json_decode($_data['widget_data']['products'], true);
                            $output_ids = [];
                            foreach($ids as $_key => $_value){
                                $output_ids[] = $_key;
                            }

                            $productsArray = [];
                            $collection = $this->getCoreModel('Magento\Catalog\Model\ResourceModel\Product\Collection')->addAttributeToSelect('*')
                                ->addAttributeToFilter('status', '1')
                                ->addAttributeToFilter('visibility', '4')
                                ->addAttributeToFilter('entity_id', ['in' => $output_ids])
                                ->setStoreId($store)
                                ->addMinimalPrice()
                                ->addFinalPrice();

                            if($collection->getSize() > 0){
                                foreach($collection as $_collection){
                                    $product = $this->productModel->setStoreId($store)->load($_collection->getId());
                                    $stockItem = $product->getExtensionAttributes()->getStockItem();
                                    $inventory = $this->getCoreModel('\Magento\CatalogInventory\Model\Stock\StockItemRepository')->get($stockItem->getItemId());

                                    $stock = true;
                                    if (!$product->isSaleable()) $stock = false;
                                    if(!$inventory->getIsInStock()) $stock = false;
                                    $productData = $product->getData();
                                    if($product->getPriceInfo()->getPrice('base_price')->getAmount()->getBaseAmount())
                                    {
                                        $price = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($product->getPriceInfo()->getPrice('base_price')->getAmount()->getBaseAmount());
                                    }
                                    else
                                    {
                                        $price = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
                                    }
                                    
                                    $_product = [
                                        'product_id'              => $_collection->getId(),
                                        'name'                    => $_collection->getName(),
                                        'type'                    => $_collection->getTypeId(),
                                        'qty_increments'          => (int) $inventory->getQtyIncrements(),
                                        'price'                   =>$price,
			                            'special_price'           => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($product->getPriceInfo()->getPrice('special_price')->getAmount()->getBaseAmount()),
                                        
                                        'stock_status'            => $stock,
                                        'review_summary'          => $this->getModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog')->getReviewSummary($_collection, $store),
                                        'product_small_image_url' => $catalog_helper->getImageUrl($product),
                                        'sort_index'              => isset($ids[$_collection->getId()]) ? $ids[$_collection->getId()] : 0
                                        ];

                                    $prices = $this->getModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog')->_productPrices($_collection);
                                    if ($prices) {
                                        $_product = array_merge($_product, $prices);
                                    }
                                    $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Product')->addDiscount($_product);
                                    $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->addImageRatio($_product);

                                    $productsArray[$_collection->getId()] = $_product;
                                }
                            }
                            foreach($output_ids as $_id){
                                if(isset($productsArray[$_id]))
                                    $productResultArray[] = $productsArray[$_id];
                            }
                            if(empty($productResultArray))
                                $_data = null;
                            else{
                                $_prindex = [];
                                $_prid = [];
                                foreach ($productResultArray as $prkey => $prvalue) {
                                    $_prindex[$prkey] = $prvalue['sort_index'];
                                    $_prid[$prkey] = $prvalue['product_id'];
                                }
                                array_multisort($_prindex, SORT_ASC, $_prid, SORT_ASC, $productResultArray);
                                $_data['widget_data']['products'] = $productResultArray;
                            }
                        }
                        else{
                            $_data['widget_data']['products'] = $productResultArray;
                        }
                        break;
                    case 'widget_image':
                        $_data['widget_data']['widget_image'] = $media_url.$_data['widget_data']['widget_image'];
                        $mapcode = $_data['widget_data']['mapcode'];
                        $mapcode = $this->_getImagemapContent($mapcode, false);
                        if(empty($mapcode)){
                            $mapcode = '<img src="'.$_data['widget_data']['widget_image'].'" alt="">';
                        }
                        $mapcode = str_replace('<img src="mobi_commerce', '<img src="'.$media_url.'mobi_commerce', $mapcode);
                        $_data['widget_data']['mapcode'] = $mapcode;
                        $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->addImageRatio($_data['widget_data']);
                        break;
                    case 'widget_image_slider':
                        $banners = [];
                        if(!empty($_data['widget_data']['banners'])){
                            foreach($_data['widget_data']['banners'] as $_key => $_value){
                                $filename = $this->_dir->getPath("media").'/'.$_value['banner_url'];
                                if(isset($_value['banner_status']) && $_value['banner_status'] == '1' && file_exists($filename)){
                                    $_value['banner_url'] = $media_url.$_value['banner_url'];
                                    $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->addImageRatio($_value);
                                    $banners[] = $_value;
                                }
                            }
                        }
                        if(empty($banners))
                            $_data = null;
                        else
                            $_data['widget_data']['banners'] = $banners;
                        break;
                }
                if(!empty($_data)) {
                    $_data['widget_data']['widget_id'] = $_data['widget_id'];
                    $data[] = $_data;
                }
            }
        }
        return $data;
    }

    protected function _getSocialLogin($store = null)
    {
        $_social_login = [
            'facebook' => [
                'is_active'  => $this->scopeConfig->getValue('mobisociallogin3/fblogin/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                'title'      => $this->scopeConfig->getValue('mobisociallogin3/fblogin/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                'sort_order' => (int) $this->scopeConfig->getValue('mobisociallogin3/fblogin/sort_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                ],
            'google' => [
                'is_active'   => $this->scopeConfig->getValue('mobisociallogin3/gologin/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                'title'       => $this->scopeConfig->getValue('mobisociallogin3/gologin/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                'sort_order'  => (int) $this->scopeConfig->getValue('mobisociallogin3/gologin/sort_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                ],
            'twitter' => [
                'is_active'   => $this->scopeConfig->getValue('mobisociallogin3/twlogin/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                'title'       => $this->scopeConfig->getValue('mobisociallogin3/twlogin/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                'sort_order'  => (int) $this->scopeConfig->getValue('mobisociallogin3/twlogin/sort_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store),
                ],
            ];
    
        $social_login = [];
        foreach($_social_login as $key => $_login){            
            if($_login['is_active']){
                $social_login[$key] = $_login;
            }
        }

        foreach ($social_login as $key => $row){
            $sort_order[$key] = $row['sort_order'];
            $is_active[$key]  = $row['is_active'];
        }
       
        if(isset($sort_order) && count($sort_order))
                array_multisort($sort_order, SORT_ASC, $is_active, SORT_ASC, $social_login);

        // only return required params
        $data = [];
        if($social_login) {
            foreach ($social_login as $key => $value) {
                $data[] = [
                    'code' => $key,
                    'title' => $value['title']
                    ];
            }
        }
        return $data;
    }

    protected function _getImagemapContent($map, $decodeLink = true)
    {
        $map = htmlspecialchars_decode($map);
        if(!empty($map))
        {
            $doc = new \DOMDocument();
            $doc->loadHtml($map);
            $areas = $doc->getElementsByTagName('area');
            foreach($areas as $_area){
                $href = $_area->getAttribute("href");
                if($decodeLink){
                    $link = $this->_decodeDeeplink($href);
                    $map = str_replace('href="'.$href.'"', 'href="'.$link.'"', $map);
                }
                else{
                    $map = str_replace('href="'.$href.'"', 'href ="javascript:app.f.imagemap(\''.$href.'\');"', $map);
                }
            }
        }
        return $map;
    }

    protected function _decodeDeeplink($link = null)
    {
        if(!empty($link)){
            $explode = explode("||", $link);
            if(count($explode) == 2){
                $urltype = $explode['0'];
                $urltval = $explode['1'];
                if($urltype == 'product'){
                    $product = $this->productModel->load($urltval);
                    $link = $product->getProductUrl();
                }elseif($urltype == 'category'){
                    $link = Mage::getModel("catalog/category")->load($urltval)->getUrl();
                }elseif($urltype == 'cms'){
                    $link = Mage::getBaseUrl().$urltval.'.html';
                }elseif($urltype == 'phone'){
                    $link = "tel:'.$urltval.'";
                }elseif($urltype == 'email'){
                    $link = "mailto:'.$urltval.'";
                }elseif($urltype == 'external'){
                    $link = $urltval;
                }
            }
        }
        return $link;
    }

    protected function _getStores()
    {
        $stores = [];
        $_websites = $this->storeManager->getWebsites();
        foreach ($_websites as $website){
            foreach ($website->getGroups() as $group){
                $groupStores = $group->getStores();
                foreach ($groupStores as $_store){
                    $stores[] = $_store->getData();
                }
            }
        }

        return $stores;
    }
}