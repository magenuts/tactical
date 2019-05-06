<?php
namespace Mobicommerce\Mobiservices3\Model\Version3;

class Order extends \Mobicommerce\Mobiservices3\Model\AbstractModel {
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    protected $customerSession;
    protected $_countryFactory;
    protected $_orderConfig;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,      
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Sales\Model\Order\Config $orderConfig
        )
    {
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->_countryFactory = $countryFactory;
        $this->_orderConfig =  $orderConfig;
        
       	parent::__construct($context, $registry, $storeManager, $eventManager);
        $this->getModel('Mobicommerce\Mobiservices3\Model\User')->autoLoginMobileUser();
    }

    /**
     * Retrieve customer session model object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
    }
    
    public function getOrders($data)
    {
    	$session = $this->customerSession;
    	if(!$session->isLoggedIn()){
    		return $this->errorStatus("please_login_to_continue");
    	}

        $list = $this->_getOrders($data);
        $info = $this->successStatus();
        $info['data']['ordersCount'] = $list['ordersCount'];
        $info['data']['orders'] = $list['orders'];
        $info['data']['token'] = $data['token'];
        return $info;
    }

    /* added by yash */
    public function _getOrders($data=null)
    {
        $page = 1;
        $limit = 30;

        if(isset($data['page']) && !empty($data['page'])) $page = $data['page'];
        if(isset($data['limit']) && !empty($data['limit'])) $limit = $data['limit'];

        $collection = $this->getCoreModel('Magento\Sales\Model\Order')->getCollection()->addFieldToFilter('customer_id', $this->_getSession()->getCustomer()->getId())
            ->setOrder('entity_id', 'DESC');

        $ordersCount = $collection->getSize();

        $collection->getSelect()->limit($limit, ($page - 1) * $limit);
        $orders = [];
        if(count($collection) > 0){
            foreach ($collection as $_collection){
                $_order = $_collection->getData();
                $_order = [
                    'order_id'          => $_collection->getEntityId(),
                    'increment_id'      => $_collection->getIncrementId(),
                    'total_qty_ordered' => $_collection->getTotalQtyOrdered(),
                    'created_at'        => $_collection->getCreatedAt(),
                    'grand_total'       => $_collection->getGrandTotal(),
                    'status'            => $_collection->getStatus(),
                    'statusLabel'       => $_collection->getStatusLabel(),
                    'order_items'       => $this->getProductFromOrderDetail($_collection),
                    ];

                $orders[] = $_order;
            }
        }
        return [
            'ordersCount' => $ordersCount,
            'orders' => $orders
            ];
    }
     /* added by yash - upto here */

    public function getOrderDetail($data)
    {
    	$session = $this->customerSession;
    	if(!$session->isLoggedIn()){
    		return $this->errorStatus("please_login_to_continue");
    	}
    	
    	$id = $data['order_id'];
        $order = $this->getCoreModel('Magento\Sales\Model\Order')->load($id);
	
        if (count($order->getData()) == 0) {
            return $this->errorStatus();
        }
        $shipping = $order->getShippingAddress();
        $billing  = $order->getBillingAddress();

        $detail = [
            'order_id'          => $id,
            'created_at'        => $order->getCreatedAt(),
            'order_date'        => $order->getUpdatedAt(),
            'status'            => $order->getStatusLabel(),
            'statusLabel'       => $order->getStatusLabel(),
            'increment_id'      => $order->getIncrementId(),
            'total_qty_ordered' => $order->getTotalQtyOrdered(),
            'grand_total'       => $order->getGrandTotal(),
            'subtotal'          => $order->getSubtotal(),
            'tax'               => $order->getTaxAmount(),
            's_fee'             => $order->getShippingAmount(),
            'order_gift_code'   => $order->getCouponCode(),
            'discount'          => abs($order->getDiscountAmount()),
            'order_note'        => $order->getCustomerNote(),
            //'order_items'       => $this->getProductFromOrderDetail($order, $width, $height),
            'order_items'       => $this->getProductFromOrderDetail($order),
            'payment_method'    => $order->getPayment()->getMethodInstance()->getTitle(),
            'shipping_method'   => $order->getShippingDescription(),
        ];
        
        if($shipping){
            $shipping_street = $shipping->getStreetFull();
            $detail['shippingAddress'] = [
                'name'         => $shipping->getName(),
                'street'       => $shipping->getStreet(),
                'city'         => $shipping->getCity(),
                'region'       => $shipping->getRegion(),
                'state_code'   => $shipping->getRegionCode(),
                'postcode'     => $shipping->getPostcode(),
                //'country'      => $shipping->getCountryModel()->loadByCode($billing->getCountry())->getName(),
                'country'      => $this->_countryFactory->create()->loadByCode($shipping->getCountryId())->getName(),
                'country_code' => $shipping->getCountry(),
                'telephone'    => $shipping->getTelephone(),
                'email'        => $order->getCustomerEmail(),
            ];
        }
        if($billing){
            $billing_street  = $billing->getStreetFull();
            $detail['billingAddress'] = [
                'name'         => $billing->getName(),
                'street'       => $billing->getStreet(),
                'city'         => $billing->getCity(),
                'region'       => $billing->getRegion(),
                'state_code'   => $billing->getRegionCode(),
                'postcode'     => $billing->getPostcode(),
                'country'      => $this->_countryFactory->create()->loadByCode($billing->getCountryId())->getName(),
                'country_code' => $billing->getCountry(),
                'telephone'    => $billing->getTelephone(),
                'email'        => $order->getCustomerEmail(),
            ];
        }

        /**
         * Added by Yash
         * Added on: 16-12-2014
         * For getting tracking number for aftership extension
         */
        $tracking_info = [];
        $shipmentCollection = $this->getCoreModel('Magento\Sales\Model\ResourceModel\Order\Shipment\Collection')
            ->setOrderFilter($order)->load();
            
        if($shipmentCollection){
            foreach ($shipmentCollection as $shipment){
                foreach($shipment->getAllTracks() as $tracknum){
                    $tracking_info[] = $tracknum->getData();
                }
            }
        }
        $detail['tracking_info'] = $tracking_info;
        /* upto here */

        $info = $this->successStatus();
        $info['data']['order_details'] = $detail;
        return $info;
    }

    public function getProductFromOrderDetail($order)
    {        
        $productInfo = [];
        $itemCollection = $order->getAllVisibleItems();
        foreach ($itemCollection as $item) {
            $options = [];
            
            $product_id = $item->getProductId();
            $product = $item->getProduct();
            if ($item->getProductOptions()) {
                $options = $this->getOptions($item->getProductType(), $item->getProductOptions());
    	    }
    	    
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $thumbnailimage = $mediaUrl.'catalog/product'.$product->getThumbnail();
            $_product = [
                'product_id'              => $product_id,
                'product_name'            => $item->getName(),
                'product_type'            => $item->getProductType(),
                'product_price'           => $item->getPrice(),
                'product_subtotal'        => $item->getRowTotal(),
                'product_subtotal_inctax' => $item->getRowTotalInclTax(),
                'product_image'           => $thumbnailimage,
                'product_qty'             => $item->getQtyOrdered(),
                'options'                 => $options,
            ];

            $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->addImageRatio($_product);
            $productInfo[] = $_product;
        }
	    
        return $productInfo;
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
        } else {
            if (isset($options['additional_options'])) {
                $optionsList = $options['additional_options'];
            } elseif (isset($options['attributes_info'])) {
                $optionsList = $options['attributes_info'];
            } elseif (isset($options['options'])) {
                $optionsList = $options['options'];
            }

            if(isset($optionsList)) {
                foreach ($optionsList as $option) {
                    $list[] = [
                        'option_title' => $option['label'],
                        'option_value' => $option['value'],
                        'option_price' => isset($option['price']) == true ? $option['price'] : 0,
                    ];
                }
            }
        }
        return $list;
    }

    public function reorder($data)
    {
        $orderId = $data['order_id'];
        if(empty($orderId))
        {
            return $this->errorStatus("invalid_order_id");
        }

        $order = $this->getCoreModel('Magento\Sales\Model\Order')->load($orderId);
        if (!$this->_canViewOrder($order)) {
            return $this->errorStatus("cannot_reorder_this_order");
        }

        $cart = $this->getCoreModel("Magento\Checkout\Model\Cart");
        
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (Mage_Core_Exception $e){
                if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                    Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                }
                else {
                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
                }
                return $this->errorStatus($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addException($e,
                    Mage::helper('checkout')->__('Cannot add the item to shopping cart.')
                );
            }
        }

        $cart->save();
        $info = $this->successStatus();
        $info['data']['cart_details'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart')->getCartInfo();
        return $info;
    }

    /**
     * Check order view availability
     *
     * @param   \Magento\Sales\Model\Order $order
     * @return  bool
     */
    protected function _canViewOrder($order)
    {
        $customerId = $this->customerSession->getCustomerId();
        $availableStates  = $this->_orderConfig->getVisibleOnFrontStatuses();
        $availableStates[] = 'new';
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)
            && in_array($order->getState(), $availableStates, $strict = true)
            ) {
            return true;
        }
        return false;
    }
}