<?php
namespace Mobicommerce\Mobiservices3\Model\Version3\Shoppingcart;

use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
//use Magento\CheckoutAgreements\Block\Agreements;

class Cart extends \Mobicommerce\Mobiservices3\Model\AbstractModel {

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;
    protected $_checkoutSession;
    protected $customerSession;
    protected $productModel;
    protected $_dir;
    protected $messageManager;
    protected $_catalogProductTypeConfigurable;

    /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var \Magento\Quote\Model\ShippingAssignmentFactory
     */
    protected $shippingAssignmentFactory;

    /**
     * Shipping method converter
     *
     * @var \Magento\Quote\Model\Cart\ShippingMethodConverter
     */
    protected $converter;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     */
    private $dataProcessor;

    /**
     * @var Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,      
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        CartExtensionFactory $cartExtensionFactory = null,
        ShippingAssignmentFactory $shippingAssignmentFactory = null,
        \Magento\Quote\Model\Cart\ShippingMethodConverter $converter,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\CheckoutAgreements\Block\Agreements $Agreements
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->productModel = $productModel;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        parent::__construct($context, $registry, $storeManager, $eventManager);
        $this->getModel('Mobicommerce\Mobiservices3\Model\User')->autoLoginMobileUser();
        $this->_checkoutSession = $_checkoutSession;
        $this->customerSession = $customerSession;
        $this->_dir = $dir;
        $this->messageManager = $messageManager;

        $this->cartExtensionFactory = $cartExtensionFactory ?: ObjectManager::getInstance()
            ->get(CartExtensionFactory::class);
        $this->shippingAssignmentFactory = $shippingAssignmentFactory ?: ObjectManager::getInstance()
            ->get(ShippingAssignmentFactory::class);

        $this->converter = $converter;
        $this->totalsCollector = $totalsCollector;
        $this->quoteRepository = $quoteRepository;
        $this->Agreements = $Agreements;
    }

    public function addtoCart($productData)
    {
        $cart   = $this->getCoreModel("Magento\Checkout\Model\Cart");
        $params = $productData;

        try{
            if(isset($params->qty['qty'])){
                $params['qty'] = (int)($params['qty']);
            } 

            $product = null;
            $productId = (int) $params['product'];
            if ($productId) {
                $_product =$this->productModel->setStoreId($this->storeManager->getStore()->getId())->load($productId);
                if ($_product->getId()) {
                    $product = $_product;
                }
            }
            $related = isset($params['related_product'])?$params['related_product']:NULL;
            $info = $this->successStatus();
            if (!$product) {
                return $this->errorStatus('product_not_available');
            }            
            
            if ($product->getTypeId()==\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $request = $this->_getProductRequest($params);
                $qty = isset($params['qty']) ? $params['qty'] : 0;
                $requestedQty = ($qty > 1) ? $qty : 1;
                $subProduct = $product->getTypeInstance(true)
                    ->getProductByAttributes($request->getSuperAttribute(), $product);

                if (!empty($subProduct)
                    && $requestedQty < ($requiredQty = $subProduct->getStockItem()->getMinSaleQty())
                ){
                    $requestedQty = $requiredQty;
                }

                $params['qty'] = $requestedQty;
            }
           
            $cart->addProduct($product, $params);
            
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();
            $this->_getCheckoutSession()->setCartWasUpdated(true);

            $info['data']['cart_details'] = $this->getCartInfo();
        }
        catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->errorStatus($e->getMessage());
        }  catch (Exception $e) {
            return $this->errorStatus($e->getMessage());
        }

        return $info;
    }

    public function setDiscountCode($data)
    {
        $return = [];        
        if (isset($data['remove']) && $data['remove'] == 1) {
            $couponCode = '';
        }
        else
        {
            $couponCode = $data['coupon_code'];
        }
        
        try {
            $this->_getCart()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getCart()->getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();
            
            if ($this->_getCart()->getQuote()->isVirtual()) {
                $total = $this->_getCart()->getQuote()->getBillingAddress();
            } else {
                $total = $this->_getCart()->getQuote()->getShippingAddress();
            }
          
            $return['discount'] = 0;
            $return['discount_amount'] = abs($total->getDiscountAmount());
            $return['grand_total'] = $this->_getCart()->getQuote()->getGrandTotal();
            $return['subtotal'] = $total->getSubtotal();
            
            if ($total->getTaxAmount()) {
                $tax = $total->getTaxAmount(); //Tax value if present
            } else {
                $tax = 0;
            }
            $return['tax'] = $tax;

            if (strlen($couponCode)) {
                if ($couponCode == $this->_getCart()->getQuote()->getCouponCode()) {
                    $return['coupon_code'] = (string) $data['coupon_code'];
                    $event_name = $this->getControllerName();
                    $event_value = [
                        'object' => $this,
                        ];
                    $data_change = $this->changeData($return, $event_name, $event_value);
                    $info = $this->successStatus();
                    $info['data'] = [['fee' => $data_change]];
                    $info['message'] = __('Coupon code "%s" was applied.', $couponCode);
                    $info['data']['cart_details']= $this->getCartInfo(); 
                    return $info;
                } else {
                    $return['coupon_code'] = '';
                    $event_name = $this->getControllerName();
                    $event_value = [
                        'object' => $this,
                        ];
                    $data_change = $this->changeData($return, $event_name, $event_value);
                    $info = $this->errorStatus();
                    $info['data'] = [['fee' => $data_change]];
                    $info['message'] =__('Coupon code "%s" is not valid.', $couponCode);
                    $info['data']['cart_details']= $this->getCartInfo(); 
                    return $info;
                }
            } else { 
                $event_name = $this->getControllerName();
                $event_value = [
                    'object' => $this,
                    ];
                $data_change = $this->changeData($return, $event_name, $event_value);
                $info = $this->successStatus();
                $info['data'] = [['fee' => $data_change]];
                $info['message'] = __('Coupon code was canceled.');
                $info['data']['cart_details']= $this->getCartInfo(); 
                return $info;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $info = $this->errorStatus($e->getMessage());
        } catch (Exception $e) {
            $info = $this->errorStatus($e->getMessage());
        }
        $info['data']['cart_details'] = $this->getCartInfo();
        return $info;
    }

    public function addProduct($productInfo, $requestInfo = null)
    {
        $product = $this->_getProduct($productInfo);
        $request = $this->_getProductRequest($requestInfo);

        $productId = $product->getId();

        if ($product->getStockItem()) {
            $minimumQty = $product->getStockItem()->getMinSaleQty();
            //If product was not found in cart and there is set minimal qty for it
            if ($minimumQty && $minimumQty > 0 && $request->getQty() < $minimumQty
                    && !$this->_getCart()->getQuote()->hasProductId($productId)
            ) {
                $request->setQty($minimumQty);
            }
        }

        if ($productId) {
            try {
                $result = $this->_getCart()->getQuote()->addProduct($product, $request);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_getCheckoutSession()->setUseNotice(false);
                $result = $e->getMessage();
            }
            /**
             * String we can get if prepare process has error
             */
            if (is_string($result)) {
                $redirectUrl = ($product->hasOptionsValidationFail()) ? $product->getUrlModel()->getUrl(
                                $product, ['_query' => ['startcustomization' => 1]]
                        ) : $product->getProductUrl();
                $this->_getCheckoutSession()->setRedirectUrl($redirectUrl);
                if ($this->_getCheckoutSession()->getUseNotice() === null) {
                    $this->_getCheckoutSession()->setUseNotice(true);
                }
                throw new \Magento\Framework\Exception\LocalizedException($result);
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('The product does not exist.'));
        }

        $this->eventManager->dispatch('checkout_cart_product_add_after', ['quote_item' => $result, 'product' => $product]);
        $this->_getCart()->getCheckoutSession()->setLastAddedProductId($productId);
        return $result;
    }

    public function getCartDetails($data)
    {
        $info = $this->successStatus();
        $info['data']['cart_details'] = $this->getCartInfo();
        if(isset($data['wishlist']) && $data['wishlist'] == '1'){
            $info['data']['wishlist'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Wishlist\Wishlist')->getWishlistInfo();
        }

        if(isset($data['syncUser']) && $data['syncUser'] == '1') {
            $is_logged_in = $this->customerSession->isLoggedIn();
            if($is_logged_in) {
                $info['data']['userdata'] = $this->getModel('Mobicommerce\Mobiservices3\Model\User')->getCustomerData();
            }
        }
        return $info;
    }

    public function getProductOptions($item)
    {
        $options = [];   
        $options = $item->getOptions();
        $product = $item->getProduct();
            
        if ($item->getProductType() == "simple") {
            // added for grocery solution
            $helper = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Shoppingcart');
            // added for grocery solution  - upto here
            $options = $helper->formatOptionsCart($helper->getCustomOptions($item));
        } elseif ($item->getProductType() == "configurable") {
            $helper =  $this->getCoreHelper('Magento\Catalog\Helper\Product\Configuration\Interceptor');                           
            $options = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Shoppingcart')->formatOptionsCart($helper->getOptions($item));
            
        } elseif ($item->getProductType() == "grouped") {
            $options =$this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Shoppingcart')->getOptions($item);
        } elseif ($item->getProductType() == "virtual") {
            $options = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Shoppingcart')->getOptions($item);
        } elseif ($item->getProductType() == "downloadable") {
            $options = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Shoppingcart')->getDownloadableOptions($item);
        }
           
        return $options;
    }

    public function getCartInfo()
    {
        $quote = $this->_getCart()->getQuote();//$this->_checkoutSession->getQuote();
        $quote->collectTotals()->save();
        $quote->save();

        $store = $this->storeManager->getStore()->getId();

        $list = [];
        $allItems = $quote->getAllVisibleItems();
        $shippingRequired = true;
        $nonShippingRequiredProducts = 0;
        if(!empty($allItems)){
            foreach ($allItems as $item) {
                $product = $item->getProduct();
                $options = $this->getProductOptions($item);
                $getHasError = $item->getHasError();
                $errorDescription = false;
                if ($getHasError) {
                    $errorDescription = $this->_remove_cart_duplicate_error($item->getErrorInfos());
                }

                $inventory = $this->getModel('\Magento\CatalogInventory\Api\StockStateInterface');
                $stockItem = $product->getExtensionAttributes()->getStockItem();
                $product_thumbnail_url = $product->getThumbnail();
                if ($item->getProductType() == "grouped") {
                    $parentIds = $this->_catalogProductTypeConfigurable->getParentIdsByChild($product->getId());
                    if (!empty($parentIds)) {
                        $parent_product = $this->productModel->setStoreId($store)->load($parentIds[0]);
                        $product_thumbnail_url = $parent_product->getThumbnail();
                    }
                }

                $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $thumbnailimage = $mediaUrl . 'catalog/product' . $product_thumbnail_url;
                $list[] = [
                    'item_id' => $item->getId(),
                    'product_id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($item->getPrice()),
                    'regular_price' => $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getBaseAmount(),
                    'price_incl_tax' => $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($item->getPriceInclTax()),
                    'product_type' => $item->getProductType(),
                    'row_total' => $item->getRowTotal(),
                    'row_total_incl_tax' => $item->getRowTotalInclTax(),
                    'product_thumbnail_url' => $thumbnailimage,
                    'review_summary' => $this->getModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog')->getReviewSummary($product, $store),
                    'qty' => $item->getQty(),
                    'max_qty' => (int) $inventory->getStockQty($product->getId(), $product->getStore()->getWebsiteId()),
                    //'qty_increments'        => (int) $stockItem->getQtyIncrements(),
                    'options' => $options,
                    'hasError' => $getHasError,
                    'errorDescription' => $errorDescription,
                ];
            }
            $cartTotal = $this->getCartTotals();
        }else{
            $cartTotal = 0;
        }

        $info['items'] = $list;        
        $cart_array = array_merge($info, $cartTotal, $this->getCartAddresses());
        
        if(empty($list))
            $cart_array['cart_qty'] = 0;

        if($nonShippingRequiredProducts == count($list) && $nonShippingRequiredProducts > 0){
            $shippingRequired = false;
        }
        $cart_array['shippingRequired'] = $shippingRequired;
        
        $is_session = false;
        $sessionCustomer = $this->customerSession;
        if($sessionCustomer->isLoggedIn()) {
            $is_session = true;
        }
        $cart_array['is_session'] = $is_session;
        $cart_array['minimum_order_amount'] = [
            'active' => $this->scopeConfig->getValue('sales/minimum_order/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'amount' => $this->scopeConfig->getValue('sales/minimum_order/amount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'description' => $this->scopeConfig->getValue('sales/minimum_order/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            ];
        
        return $cart_array;
    }

    protected function _remove_cart_duplicate_error($errors = null)
    {
        $final_errors = [];
        $codes_array = [];
        if(!empty($errors)){
            foreach($errors as $err){
                if(!in_array($err['code'], $codes_array)){
                    $final_errors[] = $err;
                    $codes_array[] = $err['code'];
                }
            }
        }
        return $final_errors;
    }

    public function getCartAddresses()
    {
        $cartShippingAddress = $this->_getQuote()->getShippingAddress();
        if($cartShippingAddress) {
            $addr['shipping_address'] = $this->_getAddress($cartShippingAddress);
        }

        $cartBillingAddress = $this->_getQuote()->getBillingAddress();
        if($cartBillingAddress) {
            $addr['billing_address'] = $this->_getAddress($cartBillingAddress);
        }
        return $addr;
    }

    protected function _getAddress($address)
    {
        $info = [
            'ID'                   => $address->getID(),
            'customer_address_id'  => $address->getCustomerAddressId(),
            'firstname'            => $address->getFirstname(),
            'lastname'             => $address->getLastname(),
            'company'              => $address->getCompany(),
            'street'               => $address->getStreet(),
            'city'                 => $address->getCity(),
            'region'               => $address->getRegion(),
            'region_id'            => $address->getRegionId(),
            'postcode'             => $address->getPostcode(),
            'country_id'           => $address->getCountryId(),
            'telephone'            => $address->getTelephone(),
            'fax'                  => $address->getFax(),
            'shipping_method'      => $address->getShippingMethod(),
            'shipping_description' => $address->getShippingDescription(),
            'shipping_amount'      => $address->getShippingAmount(),
            ];

        if($address->getEmail()) {
            $info['email'] = $address->getEmail();
        }
        return $info;
    }

    public function getCartTotals()
    {
        $this->_getQuote()->collectTotals()->save();
        $total = $this->_getCart()->getQuote()->getTotals();
        $cartdata = $this->_getCart()->getQuote()->getData();
        
        if ($this->_getCart()->getQuote()->isVirtual()) {
           $total = $this->_getCart()->getQuote()->getBillingAddress();
        } else {
           $total = $this->_getCart()->getQuote()->getShippingAddress();
        }

        $return['discount'] = 0;
        $return['couponcode'] = $this->_getCart()->getQuote()->getCouponCode();
        $return['cart_qty'] = $this->getCoreHelper('\Magento\Checkout\Helper\Cart')->getSummaryCount();
        
        $return['discount_amount'] = abs($total->getDiscountAmount());
        $return['grand_total'] = $this->_getCart()->getQuote()->getGrandTotal();
        $return['subtotal'] = $total->getSubtotal();
        
        if ($total->getTaxAmount()) {
            $tax = $total->getTaxAmount(); //Tax value if present
        } else {
            $tax = 0;
        }
        $return['tax'] = $tax;
        
        $return['tax_amount'] = $tax;
        
        if(!empty($cartdata) && $cartdata['items_qty'] > 0){
            try{
                $return['paymentinfo'] = [
                    //'code'  => strtoupper($this->_getCart()->getQuote()->getPayment()->getMethodInstance()->getCode()),
                    //'title' => $this->_getCart()->getQuote()->getPayment()->getMethodInstance()->getTitle(),
                    'fee'   => isset($cartdata['cod_fee'])?$cartdata['cod_fee']:0,
                    ];
                    
                if(isset($cartdata['codfee']) && !empty($cartdata['codfee'])){
                    $return['paymentinfo']['fee'] = $cartdata['codfee'];
                }

                if(\Mobicommerce\Mobiservices3\Model\Version3\Custom::ROUNDUP_CART_VALUES){
                    $return['grand_total'] = round($return['grand_total']);
                    $return['tax_amount'] = round($return['tax_amount']);
                    $return['subtotal'] = round($return['subtotal']);
                }
            }
            catch(Exception $e){}
        }
        return $return;
    }

    public function updateCart($data)
    {
        $cartData = $data['cart'];
        $info = $this->successStatus();
        try {
            if (count($cartData)) {
                foreach($cartData as $index => $data){
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = (int) trim($data['qty']);
                    }
                }
                $cart = $this->_getCart();
                $cart->updateItems($cartData)->save();                       
            }           
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->errorStatus($e->getMessage());
        } catch (Exception $e) {
            return $this->errorStatus($e->getMessage());
        }

        $this->_getCheckoutSession()->setCartWasUpdated(true);
        $info['data']['cart_details'] = $this->getCartInfo();
        $info['data']['shipping_methods'] = $this->_getShippingMethods();
        return $info;
    }

    public function deleteItem($data)
    {
        $id = (int) $data['item_id'];
        // change for grocery
        $items = [];
        if(isset($data['items']))
            $items = $data['items'];
        if ($id) {
            try {                
                $this->_getCart()->removeItem($id)->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                return $this->errorStatus($e->getMessage());
            } catch (Exception $e) {
                return $this->errorStatus($e->getMessage());
            }
        }
        // change for grocery
        else if($items) {
            foreach($items as $id) {
                try {                
                    $this->_getCart()->removeItem($id);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    return $this->errorStatus($e->getMessage());
                } catch (Exception $e) {
                    return $this->errorStatus($e->getMessage());
                }
            }
            $this->_getCart()->save();
        }

        $info = $this->successStatus('Item_Has_Been_Deleted_From_Cart');
        $this->_getCheckoutSession()->setCartWasUpdated(true);
        $info['data']['cart_details'] = $this->getCartInfo();
        return $info;
    }

    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof \Magento\Framework\DataObject) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = $this->dataObjectFactory->create();
            $request->setQty($requestInfo);
        } else {
            $request = $this->dataObjectFactory->create($requestInfo);
        }

        if (!$request->hasQty()) {
            $request->setQty(1);
        }
        return $request;
    }

    /**
     * added by yash
     * coz in mobile app checkoutflow is modified.
     * added on 2018-01-09
     */
    public function saveShipping($data)
    {
        $info = $this->successStatus();
        $info['data']['cart_details'] = $this->getCartInfo();
        $info['data']['shipping_methods'] = $this->_getShippingMethods($data);
        $is_logged_in = $this->customerSession->isLoggedIn();
        if($is_logged_in) {
            $info['data']['userdata'] = $this->getModel('Mobicommerce\Mobiservices3\Model\User')->getCustomerData();
        }
     
        return $info;    
    }

    public function prepareShippingAssignment(CartInterface $quote, AddressInterface $address, $method)
    {
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }

        $shippingAssignments = $cartExtension->getShippingAssignments();
        if (empty($shippingAssignments)) {
            $shippingAssignment = $this->shippingAssignmentFactory->create();
        } else {
            $shippingAssignment = $shippingAssignments[0];
        }

        $shipping = $shippingAssignment->getShipping();
        if ($shipping === null) {
            $shipping = $this->shippingFactory->create();
        }

        $shipping->setAddress($address);
        $shipping->setMethod($method);
        $shippingAssignment->setShipping($shipping);
        $cartExtension->setShippingAssignments([$shippingAssignment]);
        return $quote->setExtensionAttributes($cartExtension);
    }

    public function _getPaymentMethos()
    {
        $quote = $this->_getCheckoutSession()->getQuote();
        $store = $quote->getStoreId();
        $total = $quote->getBaseSubtotal() + $quote->getShippingAddress()->getBaseShippingAmount();
        $methodsResult = [];
        $methods = $this->getCoreHelper('\Magento\Payment\Helper\Data')->getStoreMethods($store, $quote);
        foreach ($methods as $key => $method) {
            if ($this->_canUsePaymentMethod($method, $quote) && 
                    (!in_array($method->getCode(), $this->_getRestrictedMethods()) &&
                    (array_key_exists($method->getCode(), $this->_getAllowedMethods()) || $method->getConfigData('cctypes')))
                    && ($total != 0
                    || $method->getCode() == 'free'
                    || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles()))){
            } else {
                if(!($this->_canUsePaymentMethod($method, $quote) && $method->getCode() == 'mobipayments')){
                    unset($methods[$key]);
                }
            }
        }

        foreach ($methods as $method) {
            $list[] = $this->getPaymentMethodDetail($method);
        }
        return $list;
    }

    public function getPaymentMethodDetail($method)
    {
        $code = $method->getCode();
        $list = $this->_getAllowedMethods();
        if (array_key_exists($code, $this->_getAllowedMethods())){
            $type = $list[$code];
        }else{
            $type = 1;
        }
        $detail = [];
        $detail['title'] = $method->getTitle();
        $detail['_code'] = $method->getCode();
        $detail['code'] = strtoupper($method->getCode());
        if ($type == 0){
            if ($code == "checkmo"){
                $detail['payable_to']            = $method->getConfigData('payable_to');
                $detail['payable_to_label']      = "Payable To";
                $detail['mailing_address']       = $method->getConfigData('mailing_address');
                $detail['mailing_address_label'] = "Mailing Address";
                $detail['show_type']             = 0;
            }else if(in_array($code, ['banktransfer', 'cashondelivery', 'mobipaypaloffline'])){
                $detail['instructions'] = $method->getConfigData('instructions');
                $detail['show_type']    = 0;
            }
            else if(in_array($code, ['bankpayment'])){
                $instructions = $method->getCustomText();
                $custom_text = $method->getCustomText();
                $accounts = unserialize($method->getConfigData('bank_accounts'));
                if($accounts){
                    $account_holder = $accounts['account_holder'];
                    if($account_holder){
                        foreach($account_holder as $ah_key => $ah){
                            if(!empty($ah)){
                                if(!empty($instructions))
                                    $instructions .= "<br />";
                                $instructions .= "Intestatario: ".$ah;
                                if(!empty($accounts['account_number'][$ah_key]))
                                    $instructions .= "<br />Account Number: ".$accounts['account_number'][$ah_key];
                                if(!empty($accounts['account_number'][$ah_key]))
                                    $instructions .= "<br />Bank Name: ".$accounts['bank_name'][$ah_key];
                                if(!empty($accounts['iban'][$ah_key]))
                                    $instructions .= "<br />IBAN: ".$accounts['iban'][$ah_key];
                                if(!empty($accounts['bic'][$ah_key]))
                                    $instructions .= "<br />BIC: ".$accounts['bic'][$ah_key];
                            }
                        }
                    }
                }
                $detail['instructions'] = $instructions;
                $detail['show_type']    = 0;
            }
            else if(in_array($code, ['cashondeliverypayment'])){
                $detail['cost_default'] = $method->getConfigData('cost');
                $detail['instructions'] = $method->getConfigData('cost');
                $detail['show_type']    = 0;
            }
            else {
                $detail['show_type'] = 0;
            }
        }elseif($type == 1){
            if($code == 'paymill_creditcard'){
                try{
                    $detail['configData'] = Mage::getModel('mobipayments/paymill')->getConfigData();
                }
                catch(Exception $e){
                    $detail['configData'] = null;
                }
            }
            $detail['cc_types'] = $this->_getPaymentMethodAvailableCcTypes($method);
            $detail['useccv']    = $method->getConfigData('useccv');
            $detail['show_type'] = 1;
        }elseif ($type == 2){
            $detail['email']      = $method->getConfigData('business_account');
            $detail['client_id']  = $method->getConfigData('client_id');
            $detail['is_sandbox'] = $method->getConfigData('is_sandbox');
            $detail['bncode']     = "Magestore_SI_MagentoCE";
            $detail['show_type']  = 2;
        }elseif($type == 9){
            $detail['show_type'] = 9;
            $detail['urls'] = [
                'redirect_url' => $method->getOrderPlaceRedirectUrl(),
                'success_url'  => $method->getPaidSuccessUrl(),
                'cancel_url'   => $method->getPaidCancelUrl(),
                'notify_url'   => $method->getPaidNotifyUrl(),
                'condition'    => 'EQUAL',
                ];

            if(in_array($method->getCode(), [
                'msp_ideal',
                'msp_deal', 
                'msp_banktransfer', 
                'msp_visa', 
                'msp_mastercard',
                'msp_maestro',
                'msp_babygiftcard'
                ])){
                $detail['urls']['success_url'] = $this->_getUrl("msp/standard/return", ["_secure" => true]);
                $detail['urls']['cancel_url'] = $this->_getUrl("msp/standard/cancel", ["_secure" => true]);
                $detail['urls']['condition'] = 'LIKE';
            }
            else if(in_array($method->getCode(), ['payuapi'])){
                $detail['cc_types']  = $this->_getPaymentMethodAvailableCcTypes($method);
                $detail['note_1']    = $method->getConfigData('VG6YYEN1');
                $detail['note_2']    = $method->getConfigData('VG6YYEN2');
                
                $cartTotals = $this->getCartTotals();
                $grandTotal = $cartTotals['grand_total'];

                $installment_options = [];
                $installment_key_pair = [
                    [
                        "name"      => "Axess",
                        "keycode"   => "V7H1993D1",
                        "valuecode" => "VGD8UEY31",
                        "is_active" => false,
                        "options"   => []
                        ],
                    [
                        "name"      => "Bonus",
                        "keycode"   => "V7H1993D2",
                        "valuecode" => "VGD8UEY32",
                        "is_active" => false,
                        "options"   => []
                        ],
                    [
                        "name"      => "Maximum",
                        "keycode"   => "V7H1993D3",
                        "valuecode" => "VGD8UEY33",
                        "is_active" => false,
                        "options"   => []
                        ],
                    [
                        "name"      => "Finans",
                        "keycode"   => "V7H1993D4",
                        "valuecode" => "VGD8UEY34",
                        "is_active" => false,
                        "options"   => []
                        ],
                    [
                        "name"      => "World",
                        "keycode"   => "V7H1993D5",
                        "valuecode" => "VGD8UEY35",
                        "is_active" => false,
                        "options"   => []
                        ],
                    [
                        "name"      => "Asya",
                        "keycode"   => "V7H1993D6",
                        "valuecode" => "VGD8UEY36",
                        "is_active" => false,
                        "options"   => []
                        ],
                    [
                        "name"      => "Halkbank",
                        "keycode"   => "V7H1993D7",
                        "valuecode" => "VGD8UEY37",
                        "is_active" => false,
                        "options"   => []
                        ]
                    ];
                foreach($installment_key_pair as $installment_key => $installment_pair){
                    if($method->getConfigData($installment_pair['keycode'])){
                        $installment_key_pair[$installment_key]['is_active'] = true;
                        $installment_key_pair[$installment_key]['options_str'] = $method->getConfigData($installment_pair['valuecode']);
                        $installment_key_pair[$installment_key]['options'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Version3\Custom')->_processPayuapiInstallmentOptionsString($installment_key_pair[$installment_key]['options_str'], $grandTotal, $interest_type = 'simple', $force_yearly_interest = true);
                        $installment_options[$installment_pair['keycode']] = $installment_key_pair[$installment_key];
                    }
                }
                $detail['installment_options'] = $installment_options;
            }
            else if(in_array($method->getCode(), ['paypal_standard'])){
                $detail['urls']['success_url'] = $this->_getUrl('paypal/standard/success');
                $detail['urls']['cancel_url'] = $this->_getUrl('paypal/standard/cancel');
            }
            else if(in_array($method->getCode(), ['tapcheckout_shared'])){
                $detail['urls']['redirect_url'] = $this->_getUrl('tapcheckout/shared/redirect/');
                $detail['urls']['success_url'] = $this->_getUrl('checkout/onepage/success');
                $detail['urls']['cancel_url'] = $this->_getUrl('checkout/onepage/failure');
            }
            else if(in_array($method->getCode(), ['paypal_express'])){
                $detail['urls']['redirect_url'] = $this->_getUrl('paypal/express/start');
                $detail['urls']['success_url'] = $this->_getUrl('paypal/express/review');
                $detail['urls']['update_order_url'] = $this->_getUrl('paypal/express/updateOrder');
                $detail['urls']['cancel_url'] = $this->_getUrl('checkout/cart');
            }
            else if(in_array($method->getCode(), ['atos_standard'])){
                $cc_types = explode(',', $method->getCctypes());
                $detail['cc_types'] = [];
                if(!empty($cc_types)){
                    foreach($cc_types as $_cctype){
                        $detail['cc_types'][$_cctype] = $_cctype;
                    }
                }
                $detail['urls']['cancel_url'] = $this->_getUrl("atos/payment/cancel", ["_secure" => true]);
            }
            else if(in_array($method->getCode(), ['systempay_standard'])){
                $detail['instructions'] = "<img src='" . $this->_dir->getUrlPath("media") . "systempay/logos/" . $method->getConfigData('module_logo') . "' alt='logo'>";
                $detail['urls']['redirect_url'] = $method->getOrderPlaceRedirectUrl() . '?mobile=1';
                $detail['urls']['cancel_url'] = $this->_getUrl("checkout/cart", ["_secure" => true]);
            }
            else if(in_array($method->getCode(), ['paybox_system'])){
                $detail['urls']['condition'] = 'LIKE';
                $detail['urls']['cancel_url'] = $this->_getUrl("paybox/system/decline", ["_secure" => true]);
            }
            else if(in_array($method->getCode(), ['avenues_standard'])){
                $detail['urls']['condition'] = 'LIKE';
                $detail['urls']['cancel_url'] = "cCAVENUE/standard/success";
                $detail['urls']['success_url'] = "minicheckout/shipper/success";
            }
			else if(in_array($method->getCode(), ['cashu_prepaid'])){
				$detail['urls']['cancel_url'] = $this->_getUrl("checkout/onepage/failure", ["_secure" => true]);
				$detail['logo'] = $this->_dir->getUrlPath("media").'mobi_commerce/payment_icon/'.strtoupper($method->getCode()).'.png';
            }
            else if(in_array($method->getCode(), ['paytabs_server'])){
               $detail['urls']['cancel_url'] = $this->_getUrl("paytabs/server/response", ["_secure" => true]);
			   $detail['logo'] =$this->_dir->getUrlPath("media").'mobi_commerce/payment_icon/'.strtoupper($method->getCode()).'.png';
            }
            else if(in_array($method->getCode(), ['payucheckout_shared'])){
                $detail['urls']['condition'] = 'LIKE';
                $detail['urls']['cancel_url'] = "payucheckout/shared/canceled";
                $detail['urls']['success_url'] = "payucheckout/shared/success";
            }
            else if(in_array($method->getCode(), ['cardpay_payment'])){
                $detail['cc_types'] = $this->_getPaymentMethodAvailableCcTypes($method);
                $detail['useccv']    = $method->getConfigData('useccv');
                $detail['urls']['cancel_url'] = $this->_getUrl("creditcardpay/payment/failure", ["_secure" => true]);
            }

            if(empty($detail['urls']['success_url']))
                $detail['urls']['success_url'] = $this->_getUrl("checkout/onepage/success", ["_secure" => true]);
            if(empty($detail['urls']['cancel_url']))
                $detail['urls']['cancel_url'] = $this->_getUrl("checkout/onepage/failure", ["_secure" => true]);
        }
        return $detail;
    }

    protected function _getRestrictedMethods()
    {
        return ['authorizenet_directpost'];
    }

    protected function _getAllowedMethods()
    {
        return [
            'paypal_standard'           => 9,
            'paypal_express'            => 9,
            'paypal_direct'             => 1,
            'ccavenue'                  => 9,
            'ccavenuepay'               => 9,
            'zooz'                      => 2,
            'transfer_mobile'           => 0,
            'cashondelivery'            => 0,
            'i4mrwes_cashondelivery'    => 0,
            'phoenix_cashondelivery'    => 0,
            'cashondeliverypayment'     => 0,
            'ig_cashondelivery'         => 0,
            'checkmo'                   => 0,
            'banktransfer'              => 0,
            'bankpayment'               => 0,
            'mobipaypaloffline'         => 0,
            'paymill_creditcard'        => 1,
            'payfast'                   => 9,
            'payuapi'                   => 9,
            'payucheckout_shared'       => 9,
            'msp_ideal'                 => 9,
            'msp_deal'                  => 9,
            'msp_banktransfer'          => 9,
            'msp_visa'                  => 9,
            'msp_mastercard'            => 9,
            'msp_maestro'               => 9,
            'msp_babygiftcard'          => 9,
            'atos_standard'             => 9,
            'atos_euro'                 => 9,
            'atos_cofidis3x'            => 9,
            'mgntpasat4b_standard'      => 9,
            'systempay_standard'        => 9,
            'samanpayment'              => 9,
            'paytm_cc'                  => 9,
            'tco'                       => 9,
            'servired_standard'         => 9,
            'trustly'                   => 9,
            'TWOCTWOP'                  => 9,
            'PayU'                      => 9,
            'iyzicocheckout_creditcard' => 9,
            'paybox_system'             => 9,
            'avenues_standard'          => 9,
            'epay_standard'             => 9,
			'cashu_prepaid'				=> 9,
			'paytabs_server'			=> 9,
			'payfortcc'					=> 9,
			'payfortsadad'				=> 9,
            'redsys'                    => 9,
            'cardpay_payment'           => 9,
            'tapcheckout_shared'        => 9,
        ];
    }

    protected function _assignMethod($method, $quote)
    {
        $method->setInfoInstance($quote->getPayment());
    }

    protected function _canUsePaymentMethod($method, $quote)
    {
        //if (!($method->isGateway() || $method->canUseInternal())) {
        if (!($method->isGateway() || $method->canUseCheckout())) {
            return false;
        }

        if (!$method->canUseForCountry($quote->getBillingAddress()->getCountry())) {
            return false;
        }

        if (!$method->canUseForCurrency($this->storeManager->getStore($quote->getStoreId())->getBaseCurrencyCode())) {
            return false;
        }

        /**
         * Checking for min/max order total for assigned payment method
         */
        $total = $quote->getBaseGrandTotal();
        $minTotal = $method->getConfigData('min_order_total');
        $maxTotal = $method->getConfigData('max_order_total');

        if ((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
            return false;
        }

        return true;
    }

    protected function _getPaymentMethodAvailableCcTypes($method)
    {
        $ccTypes = $this->getCoreModel('Magento\Payment\Model\Config')->getCcTypes();
        $methodCcTypes = explode(',', $method->getConfigData('cctypes'));
        foreach ($ccTypes as $code => $title){
            if(!in_array($code, $methodCcTypes)){
                unset($ccTypes[$code]);
            }
        }
        if (empty($ccTypes)) {
            return null;
        }

        return $ccTypes;
    }

    /**
     * Get list of available shipping methods
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Framework\Api\ExtensibleDataInterface $address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    private function _getShippingMethods($data = [])
    {
        $quote = $this->_getOnepage()->getQuote();

        $shippingAddress = $data['shipping'];
        $address = $this->getCoreModel('Magento\Quote\Api\Data\AddressInterface');
        $address
            ->setRegion($shippingAddress['region'])
            ->setRegionId($shippingAddress['region_id'])
            //->setRegionCode($billingAddress[''])
            ->setCountryId($shippingAddress['country_id'])
            ->setStreet($shippingAddress['street'])
            ->setCompany($shippingAddress['company'])
            ->setTelephone($shippingAddress['telephone'])
            ->setFax($shippingAddress['fax'])
            ->setPostcode($shippingAddress['postcode'])
            ->setCity($shippingAddress['city'])
            ->setFirstname($shippingAddress['firstname'])
            ->setLastname($shippingAddress['lastname'])
            //->setMiddlename($shippingAddress['middlename'])
            ->setCustomerId($shippingAddress['customer_id'])
            ->setSaveInAddressBook(null);
            
        if(isset($shippingAddress['id']) && $shippingAddress['id'])
        {
            $address->setCustomerAddressId($shippingAddress['id']);
        }

        if(isset($shippingAddress['save_in_address_book']) && $shippingAddress['save_in_address_book'])
        {
            $address->setSaveInAddressBook(1);
        }

        $output = [];
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->addData($this->extractAddressData($address));
        $shippingAddress->setCollectShippingRates(true);

        $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();

        $list = [];
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                //$output[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
                $r = $rate->getData();
                $r['price'] = $this->getMobiHelper('Mobicommerce\Mobiservices3\Helper\Mobicommerce')->getProductPriceByCurrency($r['price']);
                $list[] = $r;
            }
        }

        $method_group = [];
        if(!empty($list)){
            foreach($list as $key => $value){
                if(array_key_exists($value['carrier'], $method_group)){
                    $method_group[$value['carrier']]++;
                }
                else{
                    $method_group[$value['carrier']] = 1;
                }
                $list[$key]['carrier_index'] = $method_group[$value['carrier']];
            }
        }
        
        return $list;
    }

    /**
     * Gets the data object processor
     *
     * @return \Magento\Framework\Reflection\DataObjectProcessor
     * @deprecated 100.2.0
     */
    private function getDataObjectProcessor()
    {
        if ($this->dataProcessor === null) {
            $this->dataProcessor = ObjectManager::getInstance()->get(DataObjectProcessor::class);
        }
        return $this->dataProcessor;
    }

    /**
     * Get transform address interface into Array
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface  $address
     * @return array
     */
    private function extractAddressData($address)
    {
        $className = \Magento\Customer\Api\Data\AddressInterface::class;
        if ($address instanceof \Magento\Quote\Api\Data\AddressInterface) {
            $className = \Magento\Quote\Api\Data\AddressInterface::class;
        } elseif ($address instanceof EstimateAddressInterface) {
            $className = EstimateAddressInterface::class;
        }
        return $this->getDataObjectProcessor()->buildOutputDataArray(
            $address,
            $className
        );
    }

    public function getShippingMethods()
    {
        $info = $this->successStatus();
        $info['data']['shipping_methods'] = $this->_getShippingMethods();
        $info['data']['cart_details'] = $this->getCartInfo(); 
        return $info;
    }

    public function getPaymentMethos()
    {
        $info = $this->successStatus();
        $info['data']['agreements'] = $this->getCustomAgreements();
        $info['data']['payment_methods'] = $this->_getPaymentMethos();
        $info['data']['cart_details'] = $this->getCartInfo();
        return $info;
    }

    public function getCustomAgreements()
    {
        foreach ($this->Agreements->getAgreements() as $agreement) {
            $info['title'] = $agreement->getCheckboxText();
            $info['content'] = $agreement->getContent();
        }
        return $info;
    }

    public function saveShippingMethod($data)
    {
        $method = $data['shipping_method'];

        $shippingAddress = $data['shipping'];
        if($shippingAddress)
        {
            $address = $this->getCoreModel('Magento\Quote\Api\Data\AddressInterface');
            $address
                ->setRegion($shippingAddress['region'])
                ->setRegionId($shippingAddress['region_id'])
                //->setRegionCode($billingAddress[''])
                ->setCountryId($shippingAddress['country_id'])
                ->setStreet($shippingAddress['street'])
                ->setCompany($shippingAddress['company'])
                ->setTelephone($shippingAddress['telephone'])
                ->setFax($shippingAddress['fax'])
                ->setPostcode($shippingAddress['postcode'])
                ->setCity($shippingAddress['city'])
                ->setFirstname($shippingAddress['firstname'])
                ->setLastname($shippingAddress['lastname'])
                //->setMiddlename($shippingAddress['middlename'])
                ->setCustomerId($shippingAddress['customer_id'])
                ->setSaveInAddressBook(null);
                
            if(isset($shippingAddress['id']) && $shippingAddress['id'])
            {
                $address->setCustomerAddressId($shippingAddress['id']);
            }

            if(isset($shippingAddress['save_in_address_book']) && $shippingAddress['save_in_address_book'])
            {
                $address->setSaveInAddressBook(1);
            }
            
            $quote = $this->_getCheckoutSession()->getQuote();
            $quote->setShippingAddress($address);

            $billingAddress = $quote->getBillingAddress();
            if(!$billingAddress->getCountryId())
            {
                $quote->setBillingAddress($address);
            }
            
            $quote = $this->prepareShippingAssignment($quote, $address, $method);
        }

        $quote->setIsMultiShipping(false);

        try {
            //$this->_getOnepage()->getQuote()->save($quote);
            $this->quoteRepository->save($quote);
            $info = $this->successStatus();
            $info['data']['cart_details'] = $this->getCartInfo();
        } catch (\Exception $e) {
            $info = $this->errorStatus(__('Unable to save shipping information. Please check input data.'));
        }
        return $info;
    }

    public function _savePaymentMethod($data)
    {
        try {        
            $data = $data['payment'];
            if($data == "") return false;
            $result = $this->_getOnepage()->savePayment($data);
            return true;
        
        } catch (Exception $e) {
            if (is_array($e->getMessage())) {
                Mage::getSingleton('core/session')->setErrorPayment($e->getMessage());
                return false;
            } else {
                Mage::getSingleton('core/session')->setErrorPayment([$e->getMessage()]);
                return false;
            }
        }
    }

    public function savePaymentMethod($data)
    {
        $paymentStatus = $this->_savePaymentMethod($data);
        if(!$paymentStatus){
            $error = Mage::getSingleton('core/session')->getErrorPayment();    
            $info = $this->getModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart')->errorStatus($error);
            return $info;
        }
        $info = $this->successStatus();
        $info['data']['agreements'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Config')->_getAgreements();
        $info['data']['cart_details'] = $this->getCartInfo();
        return $info;
    }

    public function validateOrder($data)
    {
        if (!$this->getCoreHelper('Magento\Checkout\Helper\Data')->canOnepageCheckout()) {
            $this->_getCheckoutSession()->addError($this->__('The onepage checkout is disabled.'));
            return $this->errorStatus('The onepage checkout is disabled.');
        }

        $quote = $this->_getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            if(!$quote->hasItems()){
                $error = $this->errorStatus('Not_All_Products_Are_Available_In_The_Requested_Quantity');
                $error['data']['cart_details'] = $this->getCartInfo();
                return $error;
            }
            else if($quote->getHasError())
            {
                $error = $this->errorStatus('Not_All_Products_Are_Available_In_The_Requested_Quantity');
                $error['data']['cart_details'] = $this->getCartInfo();
                return $error;
            }
        }
        if (!$quote->validateMinimumAmount()) {
            $error = $this->scopeConfig->getValue('sales/minimum_order/error_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $this->_getCheckoutSession()->addError($error);
            $error['data']['cart_details'] = $this->getCartInfo();
            return $error;
        }
        return null;
    }

    public function saveOrder($data)
    {
        $information = null;
        $redirectUrl = null;
        try {
            $payment = $data['payment'];
            $quote = $this->_getOnepage()->getQuote();
            
            $billingAddress = $data['billing'];
            if($billingAddress)
            {
                //$quote->removeAddress($quote->getBillingAddress()->getId());
                //$customerAddress = $this->getCoreModel('Magento\Customer\Api\AddressRepositoryInterface')->getById($billingAddress);
                /*
                echo '<pre>';
                print_r($customerAddress->getCustomerId());
                exit;
                */
                //echo '<pre>';print_r($billingAddress);exit;
                $address = $this->getCoreModel('Magento\Quote\Api\Data\AddressInterface');
                $address
                    ->setRegion($billingAddress['region'])
                    ->setRegionId($billingAddress['region_id'])
                    //->setRegionCode($billingAddress[''])
                    ->setCountryId($billingAddress['country_id'])
                    ->setStreet($billingAddress['street'])
                    ->setCompany($billingAddress['company'])
                    ->setTelephone($billingAddress['telephone'])
                    ->setFax($billingAddress['fax'])
                    ->setPostcode($billingAddress['postcode'])
                    ->setCity($billingAddress['city'])
                    ->setFirstname($billingAddress['firstname'])
                    ->setLastname($billingAddress['lastname'])
                    //->setMiddlename($billingAddress['middlename'])
                    //->setPrefix($customerAddress->getPrefix())
                    //->setSuffix($customerAddress->getSuffix())
                    //->setVatId($customerAddress->getVatId())
                    ->setCustomerId($billingAddress['customer_id'])
                    //->setEmail($customerAddress->getEmail())
                    ->setSaveInAddressBook(null);
                    
                if(isset($billingAddress['id']) && $billingAddress['id'])
                {
                    $address->setCustomerAddressId($billingAddress['id']);
                }

                if(isset($billingAddress['save_in_address_book']) && $billingAddress['save_in_address_book'])
                {
                    $address->setSaveInAddressBook(1);
                }

                $quote->setBillingAddress($address);
            }

            $quote->getPayment()->importData($payment);
            $this->_getOnepage()->saveOrder();
            $redirectUrl = $this->_getOnepage()->getCheckout()->getRedirectUrl();
        } catch (Exception $e) {
            $_error = $this->errorStatus($e->getMessage());
            $this->_getOnepage()->getCheckout()->setUpdateSection(null);
            return $_error;
        }
        $this->_getOnepage()->getQuote()->save();
        $_result = $this->successStatus();
        $_returndata = [
            'order_id' => $this->_getCheckoutSession()->getLastOrderId(),
            'invoice_number' => $this->_getCheckoutSession()->getLastRealOrderId(),
            'redirectUrl' => $redirectUrl
        ];
        $_result['data'] = $_returndata;
        $_result['message'] = __('Your order has been received.   Thank you for your purchase!');

        $cart_session = $this->_getOnepage()->getCheckout();
        $lastOrderId = $cart_session->getLastOrderId();
        $this->_oldQuote = $cart_session->getData('old_quote');
        $this->eventManager->dispatch('checkout_onepage_controller_success_action', ['order_ids' => [$lastOrderId]]);
        return $_result;
    }

    public function clearCartData($data = null)
    {
        $cart_session = $this->_getOnepage()->getCheckout();
        $cart_session->clear();
        $info = $this->successStatus();
        return $info;
    }

    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    protected function _getCheckoutSession()
    {
       return $this->_checkoutSession;
    }

    protected function _getCart()
    {
        return $this->getCoreModel('Magento\Checkout\Model\Cart');
    }

    public function _getOnepage()
    {
        return $this->getCoreModel('Magento\Checkout\Model\Type\Onepage');
    }
    
    public function changeData($data_change, $event_name, $event_value)
    {
        $this->_data = $data_change;
        // dispatchEvent to change data
        $this->eventChangeData($event_name, $event_value);
        return $this->getCacheData();
    }

    public function setEstimateShipping($data)
    {
        $country  = (string) isset($data['country_id'])?$data['country_id']:null;
        $postcode = (string) isset($data['estimate_postcode'])?$data['estimate_postcode']:null;
        $city     = (string) isset($data['estimate_city'])?$data['estimate_city']:null;
        $regionId = (string) isset($data['region_id'])?$data['region_id']:null;
        $region   = (string) isset($data['region'])?$data['region']:null;

        $this->_getQuote()->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setCollectShippingRates(true);
        $this->_getQuote()->save();

        $info = $this->successStatus();
        $info['data']['cart_details'] = $this->getCartInfo();
        $info['data']['shipping_methods'] = $this->_getShippingMethods();
        return $info;
    }

    public function updateEstimateShipping($data)
    {
        $code = (string) isset($data['estimate_method'])?$data['estimate_method']:null;
        if (!empty($code)) {
            $this->_getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();
        }
        $info = $this->successStatus();
        $info['data']['cart_details'] = $this->getCartInfo();
        $info['data']['shipping_methods'] = $this->_getShippingMethods();
        return $info;
    }

    public function moveToWishlist($data)
    {
        $item_id = $data['item_id'];

        if(!$item_id) {
            return $this->errorStatus("invalid_data");
        }

        $item = $this->_getCart()->getQuote()->getItemById($item_id);

        if($item) {
            $wishlist = $this->getModel('Mobicommerce\Mobiservices3\Model\Wishlist\Wishlist')->_getWishlist();
            $product = $item->getProduct();
            $productId = $item->getProductId();
            $buyRequest = $this->dataObjectFactory->create([
                'product' => $productId,
                'qty' => $item->getQty()
                ]);
          
            try {
                $result = $wishlist->addNewItem($productId, $buyRequest);
                if (is_string($result)) {
                    throw new \Magento\Framework\Exception\LocalizedException($result);
                }
            }
            catch(Exception $e) {
                return $this->errorStatus($e->getMessage());
            }

            try {
                $this->_getCart()->removeItem($item_id)->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                return $this->errorStatus($e->getMessage());
            } catch (Exception $e) {
                return $this->errorStatus($e->getMessage());
            }
        }

        $info = $this->successStatus();
        $this->_getCheckoutSession()->setCartWasUpdated(true);
        $info['data']['cart_details'] = $this->getCartInfo();
        $info['data']['wishlist'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Wishlist\Wishlist') ->getWishlistInfo();
        return $info;
    }
}