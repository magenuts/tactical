<?php
namespace Mobicommerce\Mobiservices3\Model\Version3;

class Home extends \Mobicommerce\Mobiservices3\Model\AbstractModel {

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

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,      
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Reports\Block\Product\Viewed $reportsProductViewed
    )
    {
        $this->reportsProductViewed = $reportsProductViewed;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        
       	parent::__construct($context, $registry, $storeManager, $eventManager);
       	$this->getModel('Mobicommerce\Mobiservices3\Model\User')->autoLoginMobileUser();
    }
    
	public function _getHomeData($data)
	{
		$homedata['customCheckoutFields'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Custom')->getCustomCheckoutFields();
		$homedata['advance_settings'] = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('advance_settings', $data['appcode']);
		$homedata['homepage_categories'] = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('homepage_categories', $data['appcode']);
		$widgets = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('homepage_widgets', $data['appcode']);
		$homedata['widgets'] = $this->_arrangeWidgetData($widgets);
		return $homedata;
	}

	public function getHomeData($data)
	{
		$info = $this->successStatus();
		$info['data'] = $this->_getHomeData($data);
		return $info;
	}

	public function _arrangeWidgetData($data)
	{
		$widgets = [];
		$store = $this->storeManager->getStore()->getId();

		$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
		$catalog_helper = $object_manager->get('\Magento\Catalog\Helper\Product');

		if(!empty($data)){
			foreach($data as $key => $value){
				if($value['widget_code'] == 'widget_product_slider'){
					if(in_array($value['widget_data']['productslider_type'], ['newarrivals', 'bestseller', 'productviewed'])){
						$limit = $value['widget_data']['limit'];
						switch ($value['widget_data']['productslider_type']) {
							case 'newarrivals':
								$collection = $this->getCoreModel('Magento\Catalog\Model\ResourceModel\Product\Collection')
                                    ->addAttributeToSelect('*')
	                                ->addAttributeToFilter('status', '1')
	                                ->addAttributeToFilter('visibility', '4')
	                                ->setStoreId($store)
	                                ->setOrder("entity_id", "DESC")
	                                ->addMinimalPrice()
	                                ->addFinalPrice()
	                                ->setPageSize($limit);
								break;
							case 'bestseller':
								$today = time();
								$last = $today - (60*60*24*180);
    							
                                $productCollection =$this->getCoreModel('\Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory'); 
                                $collection = $productCollection->create()->setModel('Magento\Catalog\Model\Product');
								break;
							case 'productviewed':
								$collection = $this->reportsProductViewed->setPageSize($limit)->getItemsCollection();
								break;
							default:
								break;
						}

						//$collection->getSelect()->limit($limit);
						$productsArray = [];
						
						if($collection->count() > 0){
                            foreach($collection as $_collection){
                            	$_product_id = $_collection->getId();
                            	// for best selling, it is not returning id, it is returning product_id
                            	if(!$_product_id) {
                            		$_product_id = $_collection->getProductId();
                            	}
                            	if($_product_id){
                            		$product = $this->getCoreModel('Magento\Catalog\Model\Product')->setStoreId($store)->load($_product_id);

                                    $stock = true;
                                    $stockItem = $product->getExtensionAttributes()->getStockItem();
                                    if($stockItem->getId()) {
                                    	$inventory = $this->getCoreModel('\Magento\CatalogInventory\Model\Stock\StockItemRepository')->get($stockItem->getItemId());
                                    	if (!$product->isSaleable()) $stock = false;
	                                	if(!$inventory->getIsInStock()) $stock = false;
                                    }

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
										'product_id'              => $product->getId(),
										'name'                    => $product->getName(),
										'type'                    => $product->getTypeId(),
										'qty_increments'          => (int) $stockItem->getQtyIncrements(),
                                        'price'                   => $price,
			                         	'special_price'           => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($product->getPriceInfo()->getPrice('special_price')->getAmount()->getBaseAmount()),
										'stock_status'            => $stock,
										'review_summary'          => $this->getModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog')->getReviewSummary($product, $store),
										'product_small_image_url' => $catalog_helper->getImageUrl($product)
	                                    ];

	                                $prices = $this->getModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog')->_productPrices($product);
                                    if ($prices) {
                                        $_product = array_merge($_product, $prices);
                                    }
                                    $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Product')->addDiscount($_product);
                                    $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->addImageRatio($_product);
					                
	                                $productsArray[] = $_product;
                            	}
                            }

                            $data[$key]['widget_data']['products'] = $productsArray;
                            $widgets[] = $data[$key];
                        }
					}
					else{
						$widgets[] = $data[$key];
					}
				}
				else{
					$widgets[] = $data[$key];
				}
			}
		}
		
		return $widgets;
	}
}