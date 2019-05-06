<?php
namespace Mobicommerce\Mobiservices3\Model\Version3\Catalog;


class Catalog extends \Mobicommerce\Mobiservices3\Model\AbstractModel {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Reports\Block\Product\Viewed
     */
    protected $reportsProductViewed;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory
     */
    protected $mobiadmin3ResourceAppsettingCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Helper\Data
     */
    protected $mobiadmin3Helper;

    /**
     * @var \Magento\Framework\ImageFactory
     */
    protected $imageFactory;
    
    protected $_searchData;
    protected $_reviewFactory;
    protected $catalogConfig;
    protected $_dir;   
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,      
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Reports\Block\Product\Viewed $reportsProductViewed,
        \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory $mobiadmin3ResourceAppsettingCollectionFactory,
        \Mobicommerce\Mobiadmin3\Helper\Data $mobiadmin3Helper,
        \Magento\Framework\ImageFactory $imageFactory,
        \Magento\Search\Model\QueryFactory $searchData,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Framework\Filesystem\DirectoryList $dir
    )
	{
        $this->imageFactory = $imageFactory;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
        $this->reportsProductViewed = $reportsProductViewed;
        $this->mobiadmin3ResourceAppsettingCollectionFactory = $mobiadmin3ResourceAppsettingCollectionFactory;
        $this->mobiadmin3Helper = $mobiadmin3Helper;
        $this->_searchData = $searchData;
        $this->catalogConfig = $catalogConfig;
		parent::__construct($context, $registry, $storeManager, $eventManager);
        $this->getModel('Mobicommerce\Mobiservices3\Model\User')->autoLoginMobileUser();
        
        $this->_reviewFactory = $reviewFactory;
        $this->_dir = $dir;
	}

	public function search($data) 
	{
		$keyword = $data['q'];
		$_helper = $this->_searchData;
		$queryParam = str_replace('%20', ' ', $keyword);
		$query = $_helper->get();
        
        $query->setQueryText($keyword);
		$query->setStoreId($this->storeManager->getStore()->getId());       
       
		if ($query->getQueryText() != '') {
            $check = false;
		    if ($query->isQueryTextShort()) {   
			     $query->setId(0)->setIsActive(1)->setIsProcessed(1);
		    } else {  
				if ($query->getId()) {
				    $query->setPopularity($query->getPopularity() + 1);
				} else {
				    $query->setPopularity(1);
				}
                
				if ($query->getRedirect()) {
				    $query->save();
				    $check = true;
				} else {
				    $query->prepare();
				}
		    }
		    if ($check == FALSE) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $objectManager->get(\Magento\CatalogSearch\Helper\Data::class)->checkNotes();
				if (!$query->isQueryTextShort()) {
				    $query->save();
				}
		    }		    
		} else {
		    return $this->statusError();
		}
        
        if (method_exists($_helper, 'getEngine')) {
		    $engine = $this->_searchData->getEngine();
		    if ($engine instanceof \Magento\Framework\DataObject) {
				$isLayeredNavigationAllowed = $engine->isLeyeredNavigationAllowed();
		    } else {
				$isLayeredNavigationAllowed = true;
		    }
		} else {
		    $isLayeredNavigationAllowed = true;
		}
        
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $layerResolver = $objectManager->get(\Magento\Catalog\Model\Layer\Resolver::class);
        $layerResolver->create(\Magento\Catalog\Model\Layer\Resolver::CATALOG_LAYER_SEARCH);
        $layer = $layerResolver->get();
        
		return $collection = $layer->getProductCollection();
	}

	public function getCategories($data)
	{
	    $info = $this->successStatus();
        $categories = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('category', $data['appcode']);
	    
        $categories = $this->getNlevelCateories($categories, $data['category_id']);
	    $info['data']['categories'] = $categories;
	    $info['data']['category_id'] = $data['category_id'];
	    return $info;
	}

	public function productList($data)
	{
		$storeId = $this->_getStoreId();
		if(isset($data['q']) && $data['q'] != ''){
			$pCollection = $this->search($data);
		}
		else{
			$pCollection = $this->getCoreModel('Magento\Catalog\Model\ResourceModel\Product\Collection')
				->addAttributeToFilter('status', '1')
				->addAttributeToFilter('visibility', '4')
				->setStoreId($storeId)
				->addMinimalPrice()
				->addFinalPrice();
		}

		if(isset($data['sort'])){
			$pCollection = $this->_sortProductCollection($data['sort'], $pCollection);
		}

		$pCollection = $this->_applyFilter($data, $pCollection);

		$page = 1;
	    $limit = 20;
	    if(isset($data['page']) && !empty($data['page'])) $page = $data['page'];
		if(isset($data['limit']) && !empty($data['limit'])) $limit = $data['limit'];

		$productsCount = $pCollection->getSize();
		$pCollection->getSelect()->limit($limit, ($page - 1) * $limit);
        
        $products = [];
		if($pCollection->getSize() > 0){
            foreach($pCollection as $_collection){
            	

               $products[] = $this->processProduct($_collection);
            }
        }
		
		$info = $this->successStatus();
		$info['message'] = NULL;
		$info['data']['products'] = $products;
		$info['data']['product_count'] = $productsCount;
		//commented by Parvez , will code in RWS
    	$info['data']['filters'] = $this->_getFilters();
		$info['data']['token'] = $data['token'];

		return $info;
    }
    
    
    public function processProduct($_collection)
    {
        // echo "<pre>"; print_r($_collection->getData());
        // echo $_collection->getFinalPrice();
        $storeId = $this->_getStoreId();
        
        $product = $this->getCoreModel('Magento\Catalog\Model\Product')->setStoreId($storeId)->load($_collection->getId());
                
        $stockItem = $product->getExtensionAttributes()->getStockItem();
		$inventory = $this->getCoreModel('\Magento\CatalogInventory\Model\Stock\StockItemRepository')->get($stockItem->getItemId());
        
    	$stock = true;
        if (!$product->isSaleable()) $stock = false;
        if(!$product->isAvailable()) $stock = false;
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $thumbnailimage = $mediaUrl.'catalog/product'.$_collection->getThumbnail();
        $fullimage = $mediaUrl.'catalog/product'.$_collection['product_small_image_url'];
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
			'product_id'              => $_collection->getId(),
			'name'                    => $_collection->getName(),
			'type'                    => $_collection->getTypeId(),
			'qty_increments'          => (int) $inventory->getQtyIncrements(),
			'price'                   => $price,
        	'special_price'           => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($product->getPriceInfo()->getPrice('special_price')->getAmount()->getBaseAmount()),
			'stock_status'            => $stock,
			'review_summary'          => $this->getReviewSummary($_collection, $_collection->getStore()->getWebsiteId()),
			'product_small_image_url' => $thumbnailimage,
			'product_image_url'       => $fullimage
            ];

        $prices = $this->_productPrices($_collection);
	    if ($prices) {
			$_product = array_merge($_product, $prices);
	    }
	    $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Product')->addDiscount($_product);
	    $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->addImageRatio($_product);
        return $_product;
    }

    public function _applyFilter($data, $pCollection)
    {
    	$storeId = $this->storeManager->getStore()->getId();
    	$filters = [];
    	if(isset($data['filters']) && !empty($data['filters'])) {
    		parse_str($data['filters'], $filters);
    	}
    	
		if(!empty($filters)):
			foreach($filters as $key => $value){
				if($key == 'subcategories'){
					//$pCollection->addCategoryFilter($this->getCoreModel('\Magento\Catalog\Model\CategoryFactory')->load($value),true);
					$pCollection->addCategoriesFilter(['eq' => $value]);
				}
				else if(is_array($value)){
					if($key == "price"){
						foreach($value as $option){
							$option = explode("-", $option);
							if($option[0] == '') $option[0] = 0;
							if($option[1] == '') $option[1] = 100000000;
							$pCollection->addAttributeToFilter($key, ['from' => $option[0], 'to' => $option[1]]);
						}
					}
					else{
						$optionArray = [];
						foreach($value as $option){
							$optionArray[] = ['attribute' => $key, 'finset' => $option];
						}
						//echo '<pre>';print_r($optionArray);exit;
						$pCollection->addAttributeToFilter($optionArray);
					}
				}
				else{
					if($key == "price"){
						$option = explode("-",$value);
						if($option[0] == '') $option[0] = 0;
						if($option[1] == '') $option[1] = 100000000;
						$pCollection->addAttributeToFilter($key, ['from' => $option[0], 'to' => $option[1]]);
						$product_ids = [];
						foreach($pCollection as $_collection){
							if($_collection->getTypeId() == 'simple') {
				                $price = $_collection->getFinalPrice();
				                if($price >= $option[0] && $price <= $option[1]){
				                    $product_ids[] = $_collection->getId();
				                }
				                else{
				                	$pCollection->removeItemByKey($_collection->getId());
				                }
				            }
				            elseif($_collection->getTypeId() == 'grouped'){
				            	$minimal_price = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($_collection->getMinimalPrice());
				                if($minimal_price >= $option[0] && $minimal_price <= $option[1] ){
				                	$product_ids[] = $_collection->getId();
				                }
				                else{
				                	$pCollection->removeItemByKey($_collection->getId());
				                }
				            }
				            else{
				            	$product_ids[] = $_collection->getId();
				            }
						}
						$product_ids = array_unique($product_ids);
						$pCollection->addAttributeToFilter('entity_id', ['in' => $product_ids]);
					}
					else{
						$value = (int)$value;
                        $table_name = $this->getCoreModel('\Magento\Framework\App\ResourceConnection')->getConnection()->getTableName('catalog_product_index_eav');
					  	$pCollection->joinField($key.'_idx',
							$table_name,
							null,
							'entity_id=entity_id',
							"{{table}}.store_id='".$storeId."' AND {{table}}.value = '".$value."'",
							'INNER');
					}
				}
			}
		endif;

		return $pCollection;
    }

    public function getCategoryWidgets($data)
    {
    	$storeId = $this->storeManager->getStore()->getId();
    	$info = $this->successStatus();
		$widgets = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('category_widgets', 'undefined', ['categoryId' => $data['category_id']]);
		
        $info['data']['widgets'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Home')->_arrangeWidgetData($widgets);
		
        $info['data']['category_id'] = $data['category_id'];
		return $info;
    }

    public function categoryProductList($data)
    {
    	$storeId = $this->storeManager->getStore()->getId();
    	$cachedata = true;
    	$categoryId = $data['category_id'];
    	$appcode = $data['appcode'];

    	$advanceSettings =$this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->_getAdvanceSettings($appcode);

    	$default_sorting = null;
    	if(isset($advanceSettings['productlist']['default_sorting']) && !empty($advanceSettings['productlist']['default_sorting'])){
    		$default_sorting = $advanceSettings['productlist']['default_sorting'];
    	}
    	$sort = isset($data['sort']) ? $data['sort'] : null;
    	if(!empty($sort) && $sort != $default_sorting)
    		$cachedata = false;

    	$page = 1;
	    $limit = 20;
	    if(isset($data['page']) && !empty($data['page'])) $page = $data['page'];
		if(isset($data['limit']) && !empty($data['limit'])) $limit = $data['limit'];

		if($page > 1){
			$cachedata = false;
		}

		if(!empty($data['filters'])) {
			$cachedata = false;
		}

		if($cachedata){
			$params = ['categoryId' => $categoryId, 'limit' => $limit];
			$category_products = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('category_products', $appcode, $params);
		}
		else{
			$category = $this->getCoreModel('\Magento\Catalog\Model\Category')->load($categoryId);
	        $pCollection = $category->getProductCollection();
	        $pCollection->addAttributeToSelect($this->catalogConfig->getProductAttributes())
	        	->addAttributeToSelect('*')
	            ->addAttributeToFilter('status', '1')
	            ->addAttributeToFilter('visibility', '4')
	            ->setStoreId($storeId)
	            ->addMinimalPrice()
	            ->addFinalPrice();

	        if(empty($sort))
	        	$sort = $default_sorting;

	        $pCollection = $this->_sortProductCollection($sort, $pCollection);
	        $pCollection = $this->_applyFilter($data, $pCollection);
            
	        $product_count = $pCollection->getSize();
	        
	        $pCollection->getSelect()->limit($limit, ($page - 1) * $limit);
            $pCollection->getSelect()->group('entity_id');

            $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
			$catalog_helper = $object_manager->get('\Magento\Catalog\Helper\Product');
            
	        $products = [];
			if($pCollection->getSize() > 0){
	            foreach($pCollection as $_collection){
	            	$product = $this->getCoreModel('Magento\Catalog\Model\Product')->setStoreId($storeId)->load($_collection->getId());

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
                        'price'                   => $price,
			            'special_price'           => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($product->getPriceInfo()->getPrice('special_price')->getAmount()->getBaseAmount()),
						'stock_status'            => $stock,
						'review_summary'          => $this->getReviewSummary($_collection, $storeId),
						'product_small_image_url' => $catalog_helper->getImageUrl($product),
	                    ];

	               	$_product['product_image_url'] = $_product['product_small_image_url'];

	                $prices = $this->_productPrices($_collection);
				    if ($prices) {
						$_product = array_merge($_product, $prices);
				    }

				    $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Product')->addDiscount($_product);
				    $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->addImageRatio($_product);
	                $products[] = $_product;
	            }
	        }

	        $category_products = [
	        	'products' => $products
	        	];
		}

		$info = $this->successStatus();
		$info['data']['products'] = $category_products['products'];
		$info['data']['filters'] = isset($category_products['filter']) ? $category_products['filter'] : $this->_getFilters($categoryId);        
		$info['data']['product_count'] = isset($category_products['product_count']) ? $category_products['product_count'] : $product_count;
		$info['data']['category_id'] = (string)$categoryId;
		$info['data']['token'] = $data['token'];
		
		return $info;
    }

    protected function _sortProductCollection($sort, $pCollection)
    {
    	switch ($sort) {
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
                       ['entity_type' => 1, 'store_id' => $this->storeManager->getStore()->getId()],
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
                $pCollection->setOrder('entity_id', 'desc');
                break;
            default: 
                $pCollection->setOrder('ordered_qty', 'asc');
                break;
        }
        return $pCollection;
    }

    public function getReviewSummary($product, $store = null)
    {
    	if(empty($store)){
    		$store = $this->storeManager->getStore()->getId();
    	}

        $this->_reviewFactory->create()->getEntitySummary($product,$store);
        $summary = $product->getRatingSummary()->getRatingSummary();
        $averageRating = round($summary * 0.05, 1);
        $data = [
			'count'         => $product->getRatingSummary()->getReviewsCount(),
			'summary'       => $summary, // out of 100
			'averageRating' => $averageRating // out of 5
        	];
        return $data;
    }

    public function productInfo($data)
    {
    	$productId = $data['product_id'];
        
    	$storeId = $this->storeManager->getStore()->getId();

    	$productData = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getProductCache($productId, $storeId);

        if (!empty($productData)){
        	$product = $this->getCoreModel('Magento\Catalog\Model\Product')->setStoreId($storeId)->load($productId);
	
            $requestObj = $this->getCoreModel('\Magento\Framework\App\RequestInterface');
            $event_name = $requestObj->getModuleName() . '_' .
	        	$requestObj->getActionName();
                
			$name_of_event = $event_name. '_product_detail';
			$event_value = [
				'object'  => $this,
				'product' => $product
				];
			$this->eventManager->dispatch($name_of_event, $event_value);

        	if(isset($data['addRecentViews']) && $data['addRecentViews'] == '1'){
        		$this->eventManager->dispatch('catalog_controller_product_view', ['product' => $product]);
        	}
            $information = $this->successStatus();
            $information['data']['product_details'] = $productData;
            $information['data']['product_details']['ratingOptions'] =$this->getModel('Mobicommerce\Mobiservices3\Model\Review')->_getRatingOptions(["product_id" => $productId]);
                                                
            if(isset($data['addRecentViews']) && $data['addRecentViews'] == '1'){
            	$information['data']['recentlyViewed'] = $this->getRecentlyViewedProducts();
            }
        }else{
            $information = $this->errorStatus();
        }
        return $information;
    }

    public function _productPrices($product)
    {
        $prices = [];
        $type = $product->getTypeId();
        switch ($type) {          
            case \Magento\Bundle\Model\Product\Type::TYPE_CODE :
                $productPrice = $product->getPriceModel();
                list($_minimalPriceTax, $_maximalPriceTax) = $productPrice->getTotalPrices($product, null, null, false);

                if ($product->getPriceType() == 1) {
                    $_weeeTaxAmount = $this->getCoreHelper('Magento\Weee\Helper')->getAmount($product);
                    $_weeeTaxAmountInclTaxes = $_weeeTaxAmount;
                    if ($this->getCoreHelper('Magento\Weee\Helper')->isTaxable()) {
                        $_attributes = $this->getCoreHelper('Magento\Weee\Helper')->getProductWeeeAttributesForRenderer($product, null, null, null, true);
                        $_weeeTaxAmountInclTaxes = $this->getCoreHelper('Magento\Weee\Helper')->getAmountInclTaxes($_attributes);
                    }
                    if ($_weeeTaxAmount && $this->getCoreHelper('Magento\Weee\Helper')->typeOfDisplay($product, [0, 1, 4])) {
                        $_minimalPriceTax += $_weeeTaxAmount;
                        $_minimalPriceInclTax += $_weeeTaxAmountInclTaxes;
                    }
                    if ($_weeeTaxAmount && $this->getCoreHelper('Magento\Weee\Helper')->typeOfDisplay($product, 2)) {
                        $_minimalPriceInclTax += $_weeeTaxAmountInclTaxes;
                    }   
                }
                $prices = [
                    'min_price' =>  $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($_minimalPriceTax),
                    'max_price' => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($_maximalPriceTax),
                    ];
                break;            
            case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE :
                $_minimalPriceValue = $product->getMinimalPrice();
                
                /* custom code added for getting minimum and maximum price for grouped product */
                $groupedProduct = $product;
                $aProductIds = $groupedProduct->getTypeInstance()->getChildrenIds($groupedProduct->getId());

                $group_prices = [];
                foreach ($aProductIds as $ids) {
                    foreach ($ids as $id) {
                        $aProduct = $this->getCoreModel('Magento\Catalog\Model\Product')->load($id);
                        $group_prices[] = $aProduct->getPriceModel()->getPrice($aProduct);
                    }
                }

                if(!empty($group_prices))
                {
                    $prices = [
                        'min_price' => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency(min($group_prices)),
                        'max_price' => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency(max($group_prices))
                        ];

                    $prices['min_price'] = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($_minimalPriceValue);
                    if(empty($prices['min_price'])){
                    	$grouped_product = $this->getCoreModel('Magento\Catalog\Model\ResourceModel\Product\Collection')->addAttributeToSelect($this->catalogConfig->getProductAttributes())
					        ->addAttributeToFilter("entity_id", $product->getId())
					        ->setPage(1, 1)
					        ->addMinimalPrice()
					        ->addFinalPrice()
					        ->addTaxPercents()
					        ->load()
					        ->getFirstItem();

					    $prices['min_price'] = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($grouped_product->getMinimalPrice());
                    }
                }
                break;
        }
        return $prices;
    }

    public function getAttributes($product)
    {
        $result = [];
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute){
            if ($attribute->getIsVisibleOnFront()){
                $result[] = [
                    'title' => $attribute->getFrontendLabel(),
                    'value' => $attribute->getFrontend()->getValue($product),
                ];
            }
        }
        return $result;
    }

    public function getProductOptions($product)
    {
        $type = $product->getTypeId();
        switch ($type) {
            case \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE:
                return $this->getSimpleProductOptions($product);
                break;
            case \Magento\Bundle\Model\Product\Type::TYPE_CODE :
                return $this->getBundleProductOptions($product);
                break;
            case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE :
                return $this->getConfigurableProductOptions($product);
                break;
            case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE :
                return $this->getGroupedProductOptions($product);
                break;
            case \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL :
                return $this->getVirtualProductOptions($product);
                break;
        }
    }

    public function _getAllProductOptions($product)
    {
        $type = $product->getTypeId();
        $options = [
            'product_options'          => $this->getSimpleProductOptions($product),
            'product_super_attributes' => [],
            'super_group'              => [],
            'link'                     => [],
            'sample_links'             => [],
            'bundle'                   => [],
            'virtual'                  => [],
            ];

        switch ($type) {
            case \Magento\Bundle\Model\Product\Type::TYPE_CODE :
                $options['bundle'] = $this->getBundleProductOptions($product);
                break;
            case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE :
                $options['product_super_attributes'] = $this->getConfigurableProductOptions($product);
                break;
            case 'downloadable' :
                $links = $this->getDownloadableLinks($product);
                $options['link'] = $links['links'];
                $options['sample_links'] = $links['samples'];
                break;
            case  \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE :
                $options['super_group'] = $this->getGroupedProductOptions($product);
                break;
            case \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL :
                $options['virtual'] =  $this->getVirtualProductOptions($product);
                break;
        }

        return $options;
    }

    public function getSimpleProductOptions($product)
    {
		$options = [];
		foreach ($product->getOptions() as $o) {
            $_tmpOptions = $o->getData();
            if($o->getGroupByType() == \Magento\Catalog\Model\Product\Option::OPTION_GROUP_TEXT)
		    {
		     	$_tmpTextType= [
					'price'          => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($o->getPrice(true)),
					'price_type'     => $o->getPriceType(),
					'sku'            => $o->getSku(),
					'max_characters' => $o->getMaxCharacters(),
		     		];
		     	$_tmpOptions = array_merge($_tmpOptions, $_tmpTextType);
		 	}
		    if($o->getGroupByType() == \Magento\Catalog\Model\Product\Option::OPTION_GROUP_DATE)
		    {
		     	 $_tmpTextType= [
					'price'      => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($o->getPrice(true)),
					'price_type' => $o->getPriceType(),
					'sku'        => $o->getSku(),
		     	 	];
		     	$_tmpOptions = array_merge($_tmpOptions, $_tmpTextType);
		 	}	     
		    if($o->getGroupByType()== \Magento\Catalog\Model\Product\Option::OPTION_GROUP_SELECT)
		    {
			    $values = $o->getValues();
			    $_tmp['options'] = [];
			    foreach ($values as $v) {
			        $_tmp['options'][] = $v->getData();
			    }
		     	$_tmpOptions = array_merge($_tmpOptions, $_tmp);
		    }

		    if($o->getGroupByType()== \Magento\Catalog\Model\Product\Option::OPTION_GROUP_FILE)
		    {
		    	$_tmpTextType= [
					'file_extension' => $o->getFileExtension(),
					'image_size_x'   => $o->getImageSizeX(),
					'image_size_y'   => $o->getImageSizeY(),
		     	 	];
		     	$_tmpOptions = array_merge($_tmpOptions, $_tmpTextType);
		    }
		    $options[]=$_tmpOptions;
		}
        return $options;
    }

    public function getBundleProductOptions($product)
    {
        $typeInstance = $product->getTypeInstance(true);
        $typeInstance->setStoreFilter($product->getStoreId(), $product);
        $optionCollection = $typeInstance->getOptionsCollection($product);

        $selectionCollection = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($product), $product
        );

        $attributes = $optionCollection->appendSelections($selectionCollection, false, false);        

        $options = [];
        foreach ($attributes as $_attribute) {
        	$_tmpOptions = array (
				'option_id'    => $_attribute->getId(),
				'option_title' => $_attribute->getTitle(),
				'position'     => $_attribute->getPosition(),
				'required'     => $_attribute->getRequired(),
				'option_type'  => $_attribute->getType(),
				);
            $_tmp['options'] = [];
            foreach ($_attribute->getSelections() as $_selection) {
                $_tmp['options'][] = array (
					'option_id'                       => $_selection->getSelectionId(),
					'option_value'                    => $_selection->getName(),
					'option_selection_qty'            => $_selection->getSelectionQty(),    
					'option_selection_can_change_qty' => $_selection->getSelectionCanChangeQty(),    
					'option_position'                 => $_selection->getPosition(),    
					'option_is_default'               => $_selection->getIsDefault(),                    		            		
					'option_price'                    => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($product->getPriceModel()->getSelectionPreFinalPrice($product, $_selection, 1)),
                	);
            }
            $_tmpOptions = array_merge($_tmpOptions, $_tmp);
	        $options[] = $_tmpOptions;
        }
        return $options;
    }

    public function getConfigurableProductOptions($product)
    {
        $options    = [];
    	$attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);

        if (!$this->hasAllowProducts()) {
            $products = [];
            $skipSaleableCheck = true;
            
           	$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
           	$productTypeInstance = $_objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
           	
            $allProducts = $productTypeInstance->getUsedProducts($product);
            foreach ($allProducts as $_product) {
                if ($_product->isSaleable() || $skipSaleableCheck) {
                    $products[] = $_product;
                }
            }
            $this->setAllowProducts($products);
        }

        $products = $this->getData('allow_products');
       
        $list_value = [];
        $information = [];
        foreach ($products as $_product) {
			$productId = $_product->getId();
            foreach ($attributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $_product->getData($productAttribute->getAttributeCode());
				if (!isset($options[$productAttributeId])) {
                    $options[$productAttributeId] = [];
                }

                if (!isset($options[$productAttributeId][$attributeValue])) {
                    $options[$productAttributeId][$attributeValue] = [];
                }
                $options[$productAttributeId][$attributeValue][] = $productId;
            }
        }

        foreach ($attributes as $attribute) {
            $attInfo = $attribute->getData();
            
            $attInfo['prices'] = $attInfo['options'];
            if(isset($attInfo['product_attribute'])) unset($attInfo['product_attribute']);
            $attributeId =  $attribute->getProductAttribute()->getId();
            
            if(!empty($attInfo['prices'])){
                foreach($attInfo['prices'] as $p_key => $p){
                    $productsIndex = [];
                    if (isset($options[$attributeId][$p['value_index']])) {
                        $productsIndex = $options[$attributeId][$p['value_index']];
                    }
                    $price = $product->getFinalPrice();
                	if(empty($price))
                		$price = $product->getPrice();
                    if(@$p['is_percent'] == '1'){
                    	$attInfo['prices'][$p_key]['pricing_value'] = (($price * $attInfo['prices'][$p_key]['pricing_value']) / 100);
                    }
                    $attInfo['prices'][$p_key]['pricing_final_value'] = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($price + @$attInfo['prices'][$p_key]['pricing_value']);
                    $attInfo['prices'][$p_key]['pricing_value'] = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency(@$attInfo['prices'][$p_key]['pricing_value']);
                    $attInfo['prices'][$p_key]['dependence_option_ids'] = $productsIndex;
                }
            }
            
            $productAttribute = $product->getResource()->getAttribute($attribute->getProductAttribute()->getAttributeCode());
            
            $information[] = $attInfo;       
        }
        return $information;
    }

    public function getDownloadableLinks($product)
    {
        $linkArr = [];
        $links = $product->getTypeInstance(true)->getLinks($product);
        foreach ($links as $item) {
            $tmpLinkItem = [
				'link_id'             => $item->getId(),
				'title'               => $item->getTitle(),
				'price'               => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($item->getPrice()),
				'number_of_downloads' => $item->getNumberOfDownloads(),
				'is_shareable'        => $item->getIsShareable(),
				'link_url'            => $item->getLinkUrl(),
				'link_type'           => $item->getLinkType(),
				'sample_file'         => $item->getSampleFile(),
				'sample_url'          => $item->getSampleUrl(),
				'sample_type'         => $item->getSampleType(),
				'sort_order'          => $item->getSortOrder()
            ];
            $media_dir =$this->_dir->getPath("media");
            
            $file = $media_dir."/".$this->getCoreHelper('Magento\Downloadable\Helper\File')->getFilePath(
                $this->getCoreModel('Magento\Downloadable\Model\Link')->getBasePath(), $item->getLinkFile()
            );
            
            if ($item->getLinkFile() && !is_file($file)) {
                $this->getCoreHelper('\Magento\MediaStorage\Helper\File\Storage\Database')->saveFileToFilesystem($file);
            }

            if ($item->getLinkFile() && is_file($file)) {
                $name =$this->getCoreHelper('Magento\Downloadable\Helper\File')->getFileFromPathFile($item->getLinkFile());
                $tmpLinkItem['file_save'] = [
                    [
						'file'   => $item->getLinkFile(),
						'name'   => $name,
						'size'   => filesize($file),
						'status' => 'old'
	                    ]];
            }
            $sampleFile = $this->getCoreHelper('Magento\Downloadable\Helper\File')->getFilePath(
                $this->getCoreModel('Magento\Downloadable\Model\Link')->getBaseSamplePath(), $item->getSampleFile()
            );
            if ($item->getSampleFile() && is_file($sampleFile)) {
                $tmpLinkItem['sample_file_save'] = [
                    [
						'file'   => $item->getSampleFile(),
						'name'   => $this->getCoreHelper('Magento\Downloadable\Helper\File')->getFileFromPathFile($item->getSampleFile()),
						'size'   => filesize($sampleFile),
						'status' => 'old'
                    	]];
            }
            if ($item->getNumberOfDownloads() == '0') {
                $tmpLinkItem['is_unlimited'] = 1;
            }
            if ($product->getStoreId() && $item->getStoreTitle()) {
                $tmpLinkItem['store_title'] = $item->getStoreTitle();
            }
            
            $linkArr[] = $tmpLinkItem;
        }
        unset($item);
        unset($tmpLinkItem);
        unset($links);

        $samples = $product->getTypeInstance(true)->getSamples($product)->getData();
        return ['links' => $linkArr, 'samples' => $samples];
    }

	public function getGroupedProductOptions($product)
	{
		$options = [];
		$associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
		$_minprice = NULL;
        if (count($associatedProducts)) {
            foreach ($associatedProducts as $product) {
                if ($product->isSaleable()) {
                    if ($_minprice == NULL) {
                        $_minprice = $product->getFinalPrice();
                    } else {
                        if ($_minprice > $product->getFinalPrice())
                            $_minprice = $product->getFinalPrice();
                    }
                    $options[] = [
						'option_id'    => $product->getId(),
						'option_value' => $product->getName(),
						'option_title' => $product->getName(),
						'option_type'  => 'text',
						'option_price' => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($product->getFinalPrice())
                    	];
                }
            }
        }

        return $options;
	}
    
	public function getVirtualProductOptions($product)
	{
		return [];
	}
	
	public function getRecentlyViewedProducts($data = null)
	{
		$limit = isset($data['limit'])?$data['limit']:10;
		$recentlyViewedProducts = $this->reportsProductViewed->setPageSize($limit)->getItemsCollection();
        $recentlyViewedProductsArray = [];

        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
		$catalog_helper = $object_manager->get('\Magento\Catalog\Helper\Product');

        if($recentlyViewedProducts){
        	foreach($recentlyViewedProducts as $row){
        	  	$store = $this->_getStoreId();
                $product = $this->getCoreModel('Magento\Catalog\Model\Product')->setStoreId($store)->load($row->getId());
        		$productData = $product->getData();
                
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
					"product_id"            => $productData['entity_id'],
					"type"                  => $productData['type_id'],
					"sku"                   => $productData['sku'],
					'price'                 => $price,
					"final_price"           => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount()),
					'special_price'         => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($product->getPriceInfo()->getPrice('special_price')->getAmount()->getBaseAmount()),
					
					"name"                  => $productData['name'],
					"stock_status"          => $stock,
					"status"                => $productData['status'],
					"product_thumbnail_url" => $catalog_helper->getImageUrl($product),
        			];
        		$this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Product')->addDiscount($_product);
        		$recentlyViewedProductsArray[] = $_product;
        	}
        }
        return $recentlyViewedProductsArray;
	}

	public function getRelatedProducts($product, $storeId = null)
	{
		if(empty($storeId))
			$storeId = $this->storeManager->getStore()->getId();

		$relatedProductIds = $product->getRelatedProductCollection()
			->setPositionOrder();

		$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
		$catalog_helper = $object_manager->get('\Magento\Catalog\Helper\Product');

		$relatedProducts = [];
		if($relatedProductIds){
			foreach($relatedProductIds as $_relatedProductId){
				$id = $_relatedProductId->getEntityId();
				$productData = $this->getCoreModel('Magento\Catalog\Model\Product')->load($id);

                $stockItem = $product->getExtensionAttributes()->getStockItem();
				$inventory = $this->getCoreModel('\Magento\CatalogInventory\Model\Stock\StockItemRepository')->get($stockItem->getItemId());
            	$stock = true;
                if (!$productData->isSaleable()) $stock = false;
                if(!$inventory->getIsInStock()) $stock = false;
				$productDataArray = $productData->getData();
                if($product->getPriceInfo()->getPrice('base_price')->getAmount()->getBaseAmount())
               	{
                	$price = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($product->getPriceInfo()->getPrice('base_price')->getAmount()->getBaseAmount());
               	}
               	else
               	{
                	$price = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
               	}
                
				$_product = [
					"product_id"              => $productData['entity_id'],
					"type"                    => $productData['type_id'],
					"sku"                     => $productData['sku'],
					"price"                   => $price,
					"final_price"             => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($productData->getFinalPrice()),
					"special_price"           => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($productData->getFinalPrice()),
					"name"                    => $productData['name'],
					"stock_status"            => $stock,
					"status"                  => $productData['status'],
					"product_thumbnail_url"   => $catalog_helper->getImageUrl($productData),
					'review_summary'          => $this->getReviewSummary($product, $storeId),
					];

				$_product['product_small_image_url'] = $_product['product_thumbnail_url'];

				$prices = $this->_productPrices($productData);
			    if($prices){
					$_product = array_merge($_product, $prices);
			    }
			    $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Product')->addDiscount($_product);
			    $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->addImageRatio($_product);
                
				$relatedProducts[] = $_product;
			}
		}

		return $relatedProducts;
	}

	protected function _attachCategoryIcon($categories, $appcode)
	{
		$magentoCategoryThumbnail = false;
		$iconCategories = [];
		$iconCollection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create()->addFieldToFilter('app_code', $appcode)
			->addFieldToFilter('setting_code', 'category_icons');
		if($iconCollection->getSize()){
			foreach($iconCollection as $iconrow){
				$row = $iconrow->getData();
				$row = $this->mobiadmin3Helper->_jsonUnserialize($row['value']);
				if(isset($row['MAGENTO_CATEGORY_THUMBNAIL']) && $row['MAGENTO_CATEGORY_THUMBNAIL'] == '1'){
					$magentoCategoryThumbnail = true;
				}
			}
		}
		
		if(!empty($categories)){
			foreach($categories as $key => $cat){
				if($magentoCategoryThumbnail && $cat['imageurl']){
					$categories[$key]['mobiicon'] = true;
					$categories[$key]['mobiiconurl'] = $cat['imageurl'];
				}
				else{
					$categories[$key]['mobiicon'] = false;
					$categories[$key]['mobiiconurl'] = false;
				}
			}
		}
		return $categories;
	}

	public function _getFilters($categoryId = '')
	{
		$filter = [
            "message" => "",
            "data"    => []
            ];
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $filterableAttributes = $objectManager->get(\Magento\Catalog\Model\Layer\Category\FilterableAttributeList::class);
        $attributes = $filterableAttributes->getList();

        $appState = $objectManager->get(\Magento\Framework\App\State::class);
        $layerResolver = $objectManager->get(\Magento\Catalog\Model\Layer\Resolver::class);
        $filterList = $objectManager->create(
            \Magento\Catalog\Model\Layer\FilterList::class,
            [
                'filterableAttributes' => $filterableAttributes
            ]
        );
       
        $layer = $layerResolver->get();
        
        if($categoryId) {
        	$layer->setCurrentCategory($categoryId);
        }
        else
        {
            
        }
        $filters = $filterList->getFilters($layer);
        $filterData = [];
       
        foreach($filters as $filter)
        {
            if ($filter->getItemsCount()) {
	            $fd = [];
	            if($filter->getName()=="Category")
	            {
	            	$fd['attributeCode'] = 'subcategories';
	            	$fd['code'] = 'subcategories';
	            	$fd['label'] = $filter->getName();
		            $fd['type'] = $filter->getName();
		            $fd['count'] = $filter->getItemsCount();
	            }
	            else
	            {
		            $fd['attributeCode'] = $filter->getAttributeModel()->getAttributeCode();
		            $fd['code'] = $filter->getAttributeModel()->getAttributeId();
		            $fd['label'] = $filter->getAttributeModel()->getStoreLabel();
		            $fd['type'] = $filter->getAttributeModel()->getFrontendInput();
		            $fd['count'] = $filter->getItemsCount();    
	            }
	            
	           	$j = 0;
	            foreach ($filter->getItems() as $item) {
		            if($fd['type']=="price")
		            {
		            	if($categoryId) {
		                	$fd['max'] = $layer->setCurrentCategory($categoryId)->getProductCollection()->getMaxPrice();
		                    $fd['min'] = $layer->setCurrentCategory($categoryId)->getProductCollection()->getMinPrice();
		                }
		                else
		                {
		                    $fd['max'] = $layer->getProductCollection()->getMaxPrice();
		                    $fd['min'] = $layer->getProductCollection()->getMinPrice();
		                }

		                if(!$fd['max'])
		                {
		                	$fd = [];
		                }
		                else if($fd['max'] == $fd['min'])
		                {
		                	$fd = [];
		                }
		            }
		            else
		            {
		                $fd['options'][$j]['label'] = str_replace('"', "'", $item->getLabel());
		                $fd['options'][$j]['value'] = $item->getValue();
		                $fd['options'][$j]['count'] = $item->getCount();   
		            } 
		            $j++;
	            }
	            $filterData['data'][] = $fd;
            }
        }
        
        return $filterData;
	}

	/**
	 * This function is used to get specific categories when we retrieve from cache.
	 * As we cannot query in cache data so we need to filter from them
	 */
	public function getNlevelCateories($data, $parent_id = FALSE, $categories = [], $loop = 2, $current_loop = 1)
	{
		if($parent_id === FALSE)
		{
			$parent_id = $this->storeManager->getStore()->getRootCategoryId();
		}

		foreach($data as $_category)
		{
			if($_category['parent_id'] == $parent_id || $_category['category_id'] == $parent_id)
			{
				$categories[$_category['category_id']] = $_category;
				if($current_loop < $loop){
					$categories = $this->getNlevelCateories($data, $_category['category_id'], $categories, $loop, $current_loop+1);
				}
			}
		}
		if($loop == $current_loop){
			$categories = array_values($categories);
		}
		return $categories;
	}
}
?>