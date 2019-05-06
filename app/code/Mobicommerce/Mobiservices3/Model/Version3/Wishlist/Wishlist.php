<?php
namespace Mobicommerce\Mobiservices3\Model\Version3\Wishlist;

class Wishlist extends \Mobicommerce\Mobiservices3\Model\AbstractModel {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    protected $customerSession;
    protected $wishlistProvider;
    protected $wishlist;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,      
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\Session $customerSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Wishlist\Model\Wishlist $wishlist
    )
	{
        $this->customerSession = $customerSession;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->registry = $registry;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
		$this->customerSession = $customerSession;
        $this->wishlistProvider = $wishlistProvider;
        $this->wishlist = $wishlist;
        
       	parent::__construct($context, $registry, $storeManager, $eventManager);
        $this->getModel('Mobicommerce\Mobiservices3\Model\User')->autoLoginMobileUser();
	}

	public function wishlistList($data)
	{
		$info = $this->successStatus();
		$info['data']['wishlist'] = $this->getWishlistInfo();
		return $info;
	}
	
	public function _getWishlist($wishlistId = null)
	{
		$wishlist = $this->registry->registry('wishlist');
		if ($wishlist) {
		    return $wishlist;
		}

		try {
		    $customerId = $this->customerSession->getCustomerId();
		    /* @var Mage_Wishlist_Model_Wishlist $wishlist */
		    $wishlist = $this->wishlistProvider->getWishlist();
		    if ($wishlistId) {
				$wishlist->load($wishlistId);
		    } else {
				//$wishlist->loadByCustomer($customerId, true);
		    }

		    if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
				$wishlist = null;
				throw new \Magento\Framework\Exception\LocalizedException(
				    Mage::helper('wishlist')->__("Requested wishlist doesn't exist")
				);
		    }

		    $this->registry->register('wishlist', $wishlist);
		} catch (Mage_Core_Exception $e) {
		    Mage::getSingleton('wishlist/session')->addError($e->getMessage());
		    return false;
		} catch (Exception $e) {
		    Mage::getSingleton('wishlist/session')->addException($e,
			Mage::helper('wishlist')->__('Wishlist could not be created.')
		    );
		    return false;
		}

		return $wishlist;
	}		
	
	public function addWishlistItem($data)
	{
		$action = 'add';
		$session = $this->customerSession;
		
		if(!$session->isLoggedIn()){
			return $this->errorStatus("Please_Login_To_Continue");
		}

		$params = $data;
		$wishlist = $this->_getWishlist();
		if (!$wishlist) {
		    return $this->norouteAction();
		}
		
		$productId = (int)$params['product'];
		if (!$productId) {			
			return $this->errorStatus("product_not_available");
		}

		// if item is already there is wishlist, delete that item
		$customer = $this->customerSession->getCustomerId(); 		
        $currentUserWishlist = $this->wishlistProvider->getWishlist();
        if ($currentUserWishlist) {
            $itemCollection = $currentUserWishlist->getItemCollection();
        }
        
        foreach($itemCollection as $item) {
 			if($item->getProductId() == $productId) {
 				$action = 'delete';
 				$item->delete();
 				$currentUserWishlist->save();
 				//$this->removeItem(['item_id' => $item->getId()]);
 				//print_r($removeData);exit;
 			}
		}

		$product = $this->getCoreModel('Magento\Catalog\Model\Product')->load($productId);
		$result = [];
		if($action == 'add') {
			if (!$product->getId() || !$product->isVisibleInCatalog()) {
				return $this->errorStatus("product_not_available");
			}

		    $requestParams = $params;
		    $buyRequest = $this->dataObjectFactory->create($requestParams);
		    
		    $result = $wishlist->addNewItem($product, $buyRequest);
		    if (is_string($result)) {
				throw new \Magento\Framework\Exception\LocalizedException($result);
		    }
		    $wishlist->save();
		}
		else {
			$wishlist = $currentUserWishlist;
		}

	    $this->eventManager->dispatch(
			'wishlist_add_product',
			[
				'wishlist' => $wishlist,
				'product'  => $product,
				'item'     => $result
			]
	    );

	    $this->getCoreHelper('\Magento\Wishlist\Helper\Data')->calculate();
		$info = $this->successStatus();
	    $info['data']['wishlist'] = $this->getWishlistInfo();
	    $info['data']['action'] = $action;
		return $info;
	}
	
	public function getWishlistInfo() 
	{
		$customerId = $this->customerSession->getCustomerId();
		$wishlist = $this->wishlistProvider->getWishlist();
		$storeId = $this->storeManager->getStore()->getId();
		
		$list = [];
		if($customerId) {
            //this will return wishlist of current login user
            $wishlist = $this->wishlistProvider->getWishlist();
		}
		else{
			return $list;
		}
		    
	    $items = $wishlist->getItemCollection();
		$wishlistItems = [];
	    if(count($items) > 0){
		    foreach($items as $item){
		    	$_item = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getProductCache($item->getProductId(), $storeId);
		    	$_item['product_small_image_url'] = $_item['product_thumbnail_url'];
				$_item['wishlist_id']  = $item->getWishlistId();
				$_item['wishlist_item_id'] = $item->getWishlistItemId();

				$this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->addImageRatio($_item);
				$list[] = $_item;
		    }
		}
		return $list;
	}
	
	public function removeItem($data)
	{
		$session = $this->customerSession;
		if(!$session->isLoggedIn()){
			return $this->errorStatus("please_login_to_continue");
		}
		
		$item_id = (int) $data['item_id'];
		
		if(!$item_id){
			return $this->errorStatus("invalid_data");
		}
		
		$customer = $this->customerSession->getCustomerId();
        $wishlist = $this->wishlist->loadByCustomerId($customer);
        $items = $wishlist->getItemCollection();
        
         /** @var \Magento\Wishlist\Model\Item $item */
        foreach ($items as $item) {
            if ($item->getId() == $item_id) {
                $item->delete();
                $wishlist->save();
            }
        }
        
        $this->getCoreHelper('\Magento\Wishlist\Helper\Data')->calculate();
        
		$info = $this->successStatus();
		$info['data']['wishlist'] = $this->getWishlistInfo();
		return $info;
	}

	public function removeAll($data)
	{
		$session = $this->customerSession;
		if(!$session->isLoggedIn()){
			return $this->errorStatus("please_login_to_continue");
		}
		
        $customer = $this->customerSession->getCustomerId();
        $wishlist = $this->wishlist->loadByCustomerId($customer);
        $items = $wishlist->getItemCollection();
        
         /** @var \Magento\Wishlist\Model\Item $item */
        foreach ($items as $item) {
            $item->delete();
        }
        $wishlist->save();
	
		$this->getCoreHelper('\Magento\Wishlist\Helper\Data')->calculate();
		
		$info = $this->successStatus();
		$info['data']['wishlist'] = $this->getWishlistInfo();
		return $info;
	}
	
	public function getOptions($type, $options) 
	{ 
		$list = [];
		if ($type == 'bundle') {
		    foreach ($options['bundle_options'] as $option) {
				foreach ($option['value'] as $value) {
				    $list[] = [
						'option_title' => $option['label'],
						'option_value' => $value['title'],
						'option_price' => $value['price'],
					    ];
				}
		    }
		}else{
		    if (isset($options['additional_options'])) {
				$optionsList = $options['additional_options'];
		    } elseif (isset($options['attributes_info'])) {
				$optionsList = $options['attributes_info'];
		    } elseif (isset($options['options'])) {
				$optionsList = $options['options'];
		    }
		    foreach ($optionsList as $option) {
				$list[] = [
				    'option_title' => $option['label'],
				    'option_value' => $option['value'],
				    'option_price' => isset($option['price']) == true ? $option['price'] : 0,
				];
		    }
		}
		return $list;
	}
	
	public function addtocartWishlistItem($data)
	{
		$session = $this->customerSession;
		
		if(!$session->isLoggedIn()){
			return $this->errorStatus("please_login_to_continue");
		}
		
		$itemId = $data['item_id'];
        $storeId = $this->storeManager->getStore()->getId();
        $item = $this->getCoreModel('Magento\Catalog\Model\Product')->setStoreId($storeId)->load($itemId);
        
        if (!$item->getId()) {
		    return $this->errorStatus("invalid_data");
		}
        $wishlist = $this->_getWishlist($item->getWishlistId());

		if (!$wishlist) {
		    return $this->errorStatus("wishlist_not_found_error");
		}

		// Set qty
		$qty = (int)$data['qty'];
		if(empty($qty)){
			$qty = 1;
		}
		
        $cart = $this->getCoreModel("Magento\Checkout\Model\Cart");
        
		try{
            $options = $this->getModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog')->getProductOptions($item);

		    $item->setOptions($options->getOptionsByItem($itemId));

		    $buyRequest = $this->getCoreModel('Magento\Catalog\Model\ResourceModel\Product\Collection')->addParamsToBuyRequest(
			$data,
			['current_config' => $item->getBuyRequest()]
		    );

		    $item->mergeBuyRequest($buyRequest);
		    if ($item->addToCart($cart, true)) {
				$cart->save()->getQuote()->collectTotals();
		    }

		    $wishlist->save();
		    $this->getCoreHelper('\Magento\Wishlist\Helper\Data')->calculate();

		} catch (Mage_Core_Exception $e) {
		    if ($e->getCode() == \Magento\Wishlist\Model\Item::EXCEPTION_CODE_NOT_SALABLE) {
				return $this->errorStatus(Mage::helper('wishlist')->__('This product(s) is currently out of stock'));
		    } else if ($e->getCode() == \Magento\Wishlist\Model\Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
				return $this->errorStatus($e->getMessage());
		    } else {
				return $this->errorStatus($e->getMessage());
		    }
		} catch (Exception $e) {
		    return $this->errorStatus(__('Cannot add item to shopping cart'));
		}

		$this->getCoreHelper('\Magento\Wishlist\Helper\Data')->calculate();
		
		$info = $this->successStatus();
		$info['data']['cart_details']= $this->getModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart')->getCartInfo();
		$info['data']['wishlist'] = $this->getWishlistInfo();
		return $info;
	}

	public function updateWishlistItem($data)
	{
		$session = $this->customerSession;
		if(!$session->isLoggedIn())
		{
			return $this->errorStatus("please_login_to_continue");
		}

        $productId = (int) $data['product'];
        if (!$productId) {
            return $this->errorStatus("invalid_data");
        }

        $product = $this->getCoreModel('Magento\Catalog\Model\Product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $info = $this->errorStatus(Mage::helper('wishlist')->__('Cannot specify product.'));
			$info['data']['cart_details'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart')->getCartInfo(); 
			return $info;
        }

        try {
            $id = (int) $data['id'];
            $storeId = $this->storeManager->getStore()->getId();
            $item = $this->getCoreModel('Magento\Catalog\Model\Product')->setStoreId($storeId)->load($id);
            
            if (!$item->getId()) {
    		    return $this->errorStatus("invalid_data");
    		}
            $wishlist = $this->_getWishlist($item->getWishlistId());
            
            if (!$wishlist) {
                return $this->errorStatus("wishlist_not_found_error");
            }

            $buyRequest = $this->dataObjectFactory->create($data);
            $wishlist->updateItem($id, $buyRequest)->save();

            $this->getCoreHelper('\Magento\Wishlist\Helper\Data')->calculate();
            $this->eventManager->dispatch('wishlist_update_item', [
                'wishlist' => $wishlist, 'product' => $product, 'item' => $wishlist->getItem($id)]
            );

            $this->getCoreHelper('\Magento\Wishlist\Helper\Data')->calculate();

            $message = __('%1$s has been updated in your wishlist.', $product->getName());
            $info = $this->successStatus($message);
			$info['data']['wishlist'] = $this->getWishlistInfo();
			return $info;
        } catch (Mage_Core_Exception $e) {
            return $this->errorStatus($e->getMessage());
        } catch (Exception $e) {
            $session->addError(__('An error occurred while updating wishlist.'));
            $this->logger->critical($e);
            return $this->errorStatus(__('An error occurred while updating wishlist.'));
        }
	}

	protected function _getWishlistOptionsWithValues($_product = null, $wihlistOptions = null, $productOptions = null)
	{
		$options = [];
		switch($_product->getTypeID()){
			case 'bundle':
				$bundleOptions = $wihlistOptions['info_buyRequest']['bundle_option'];
				$bundleOptionsQty = $wihlistOptions['info_buyRequest']['bundle_option_qty'];
				$bundleProductOptions = $this->_setProductOptionArray($_product->getTypeID(), $productOptions['bundle']);
				if(!empty($bundleOptions)){
					foreach ($bundleOptions as $key => $value) {
						$_option = [];
						if(is_array($value)){
							foreach($value as $mkey => $mvalue) {
								$_option[] = [
									"option_title" => $bundleProductOptions[$key]['option_title'],
									"option_value" => (isset($bundleOptionsQty[$key]) ? $bundleOptionsQty[$key] : '1') . " x " . $bundleProductOptions[$key]['options'][$mvalue]['option_value']
									];
							}
						}
						else{
							//echo '<pre>';print_r($bundleOptionsQty);exit;
							$_option[] = [
								"option_title" => $bundleProductOptions[$key]['option_title'],
								"option_value" => (isset($bundleOptionsQty[$key]) ? $bundleOptionsQty[$key] : '1') . " x " . $bundleProductOptions[$key]['options'][$value]['option_value']
								];
						}
						$options[] = $_option;
					}
				}
				break;
			case 'grouped':
				$groupedOptions = $wihlistOptions['info_buyRequest']['super_group'];
				$groupedProductOptions = $this->_setProductOptionArray($_product->getTypeID(), $productOptions['super_group']);
				if(!empty($groupedOptions)){
					foreach ($groupedOptions as $key => $value) {
						$_option = [];
						if(!is_array($value)){
							$_option = [
								"option_title" => $groupedProductOptions[$key]['option_title'],
								"option_value" => $value
								];
						}
						$options[] = $_option;
					}
				}
				break;
			case 'configurable':
				$configurableOptions = $wihlistOptions['info_buyRequest']['super_attribute'];
				$configurableProductOptions = $this->_setProductOptionArray($_product->getTypeID(), $productOptions['product_super_attributes']);
				//print_r($productOptions['product_super_attributes']);exit;
				if(!empty($configurableOptions)){
					foreach ($configurableOptions as $key => $value) {
						$_option = [];
						if(!is_array($value)){
							$_option = [
								"option_title" => $configurableProductOptions[$key]['label'],
								"option_value" => $configurableProductOptions[$key]['prices'][$value]['store_label']
								];
						}
						$options[] = $_option;
					}
				}
				break;
			case 'downloadable':
				//print_r($wihlistOptions['info_buyRequest']);exit;
				$downloadableOptions = $wihlistOptions['info_buyRequest']['links'];
				$downloadableProductOptions = $this->_setProductOptionArray($_product->getTypeID(), $productOptions['link']);
				//print_r($downloadableProductOptions);exit;
				if(!empty($downloadableOptions)){
					$_option = [];
					foreach ($downloadableOptions as $key => $value) {
						if(!is_array($value)){
							$_option[] = $downloadableProductOptions[$value]['title'];
						}
					}
					$options[] = [
						"option_title" => "Links",
						"option_value" => implode(", ", $_option),
						];
				}
				break;
			default:
				break;
		}

		$simpleOptions = isset($wihlistOptions['info_buyRequest']['options']) ? $wihlistOptions['info_buyRequest']['options'] : null;
		$simpleProductOptions = $this->_setProductOptionArray('simple', $productOptions['product_options']);
		if(!empty($simpleOptions)){
			foreach ($simpleOptions as $key => $value) {
				$_option = array();
				if(in_array($simpleProductOptions[$key]['type'], ['field', 'area', 'date', 'date_time', 'time'])){
					$_option = [
						"option_title" => $simpleProductOptions[$key]['title'],
						"option_value" => $value
						];
				}
				elseif(in_array($simpleProductOptions[$key]['type'], ['drop_down', 'radio'])){
					$_option = [
						"option_title" => $simpleProductOptions[$key]['title'],
						"option_value" => $simpleProductOptions[$key]['options'][$value]['title']
						];
				}
				elseif(in_array($simpleProductOptions[$key]['type'], ['checkbox', 'multiple'])){
					if(is_array($value)){
						foreach($value as $mkey => $mvalue) {
							$_option[] = $simpleProductOptions[$key]['options'][$mvalue]['title'];
						}
						$_option = [
							"option_title" => $simpleProductOptions[$key]['title'],
							"option_value" => implode(", ", $_option)
							];
					}
					else{
						$_option = [
							"option_title" => $simpleProductOptions[$key]['title'],
							"option_value" => $simpleProductOptions[$key]['options'][$value]['title']
							];
					}
				}
				$options[] = $_option;
			}
		}
		return $options;
	}

	protected function _setProductOptionArray($productType, $options = array())
	{
		if(empty($options))
			return false;

		$outputOptions = [];
		if($productType == 'configurable'){
			foreach($options as $key => $value){
				$innerOptions = [];
				if(!empty($value['prices'])){
					foreach($value['prices'] as $ikey => $ivalue){
						$innerOptions[$ivalue['value_index']] = $ivalue;
					}
				}
				$outputOptions[$value['attribute_id']] = $value;
				$outputOptions[$value['attribute_id']]['prices'] = $innerOptions;
				unset($outputOptions[$value['attribute_id']]['product_attribute']);
			}
		}
		else if($productType == 'downloadable'){
			foreach($options as $key => $value){
				$innerOptions = [];
				if(!empty($value['options'])){
					foreach($value['options'] as $ikey => $ivalue){
						$innerOptions[$ivalue['link_id']] = $ivalue;
					}
				}
				$outputOptions[$value['link_id']] = $value;
				$outputOptions[$value['link_id']]['options'] = $innerOptions;
			}
		}
		else{
			foreach($options as $key => $value){
				$innerOptions = [];
				if(!empty($value['options'])){
					foreach($value['options'] as $ikey => $ivalue){
						if(isset($ivalue['option_type_id']))
							$innerOptions[$ivalue['option_type_id']] = $ivalue;
						else
							$innerOptions[$ivalue['option_id']] = $ivalue;
					}
				}
				$outputOptions[$value['option_id']] = $value;
				$outputOptions[$value['option_id']]['options'] = $innerOptions;
			}
		}
		//print_r($outputOptions);exit;
		return $outputOptions;
	}
}
?>