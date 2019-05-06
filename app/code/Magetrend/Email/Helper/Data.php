<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Helper;

class Data
{
    const XML_PATH_DIRECTION = 'mtemail/general/direction';

    const XML_PATH_SINGLE_TEMPLATE_MODE = 'mtemail/general/single_template_mode';

    const XML_PATH_EMAIL_HIDE_SKU = 'mtemail/email/hide_sku';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    public $subscriber;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\Track
     */
    public $track;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface $orderInterface
     */
    public $orderInterface;

    /**
     * @var \Magento\Sales\Api\Data\InvoiceInterface
     */
    public $invoiceInterface;

    /**
     * @var \Magento\Sales\Api\Data\ShipmentInterface
     */
    public $shipmentInterface;

    /**
     * @var \Magento\Sales\Api\Data\CreditmemoInterface
     */
    public $creditmemoInterface;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManagerInterface
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param \Magento\Sales\Model\Order\Shipment\Track $track
     * @param \Magento\Sales\Api\Data\OrderInterface $orderInterface
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoiceInterface
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipmentInterface
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemoInterface
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Magento\Sales\Model\Order\Shipment\Track $track,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Sales\Api\Data\InvoiceInterface $invoiceInterface,
        \Magento\Sales\Api\Data\ShipmentInterface $shipmentInterface,
        \Magento\Sales\Api\Data\CreditmemoInterface $creditmemoInterface
    ) {
        $this->objectManager = $objectManagerInterface;
        $this->storeManager = $storeManagerInterface;
        $this->scopeConfig = $scopeConfigInterface;
        $this->subscriber = $subscriber;
        $this->track = $track;
        $this->orderInterface = $orderInterface;
        $this->invoiceInterface = $invoiceInterface;
        $this->shipmentInterface = $shipmentInterface;
        $this->creditmemoInterface = $creditmemoInterface;
    }

    public function isActive($storeId = null)
    {
        return true;
    }

    public function getHash($key, $blockName, $blockId, $templateId)
    {
        //@codingStandardsIgnoreStart
        $hash = md5('var_'.$key.'_'.$blockName.'_'.$blockId.'_'.$templateId);
        //@codingStandardsIgnoreEnd
        return $hash;
    }

    public function getUniqueBlockId()
    {
        return time();
    }

    public function getDemoVars($template)
    {
        $store = $this->storeManager->getStore($template->getStoreId());
        $order = $this->getDemoOrder($store);
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $customer = $this->getDemoCustomer();
        $payment = $order->getPayment();
        $paymentMethod = __('Payment method is not available');
        if ($payment) {
            $paymentMethod = $payment->getMethodInstance()->getTitle();
        }

        return [
            'customer' => $customer,
            'checkoutType' => (string)__('One Step Checkout'),
            'reason' => (string)__('Suspected Fraud'),
            'customerEmail' => $customer->getEmail(),
            'comment' => 'Lorem ipsum dolor sit, consectetuer adipiscing elit. Aenean commodo ligula eget dolor.',
            'subscriber' => $this->getDemoSubscriber(),
            'store' => $store,
            'order' => $order,
            'billing' => $billingAddress,
            'billingAddress' => $billingAddress,
            'shippingAddress' => $billingAddress,
            'shippingMethod' => $order->getShippingDescription(),
            'paymentMethod' => $paymentMethod,
            'dateAndTime' => $order->getCreatedAt(),
            'creditmemo' => $this->getDemoCreditMemo($store),
            'invoice' => $this->getDemoInvoice($store),
            'shipment' => $this->getDemoShipment($store),
            'data' => $this->getDemoContactRequest(),
            'product_name' => (string)__('Product Name'),
            'email' => 'john@doe.com',
            'name' => (string)__('John Doe'),
            'message' => (string)__('Lorem ipsum dolor sit, consectetuer adipiscing elit.'),
            'sender_email' => (string)__('sener@doe.com'),
            'sender_name' => (string)__('Sender Name'),
            'product_url' => 'http://store.demo.store.com/product.url.html',
            'product_image' => '',
            'customerName' => (string)__('John Doe'),
            'viewOnSiteLink' => 'http://store.demo.store.com/product.url.html',
            'items' => '*ITEMS*',
            'total' => '*TOTALS*',
            'alertGrid' => '*GRID*',
        ];
    }

    public function getDemoSubscriber()
    {
        $subscriber = $this->objectManager->create('Magento\Newsletter\Model\Subscriber')
            ->setSubscriberEmail('jd1@ex.com');
        return $subscriber;
    }

    public function getDemoCustomer()
    {
        $customer = $this->objectManager->create('Magento\Customer\Model\Customer')
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setEmail('jd1@ex.com')
            ->setPassword('soMepaSswOrd');

        return $customer;
    }

    public function getDemoOrder($store)
    {
        $id = $this->scopeConfig
            ->getValue('mtemail/demo/order_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());

        $order = $this->orderInterface->loadByIncrementId((string)$id);
        if (!$order->getId()) {
            $order = $this->orderInterface->load($id);
        }
        return $order;
    }

    public function getDemoInvoice($store)
    {
        $id = $this->scopeConfig
            ->getValue('mtemail/demo/invoice_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());

        $invoice = $this->invoiceInterface->loadByIncrementId((string)$id);
        if (!$invoice->getId()) {
            $invoice = $this->invoiceInterface->load($id);
        }
        return $invoice;
    }

    public function getDemoShipment($store)
    {
        $id = $this->scopeConfig
            ->getValue('mtemail/demo/shipment_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());

        $shipment = $this->shipmentInterface->loadByIncrementId((string)$id);
        if (!$shipment->getId()) {
            $shipment = $this->shipmentInterface->load($id);
        }

        if (!$shipment->getAllTracks()) {
            $track = $this->track->setData([
                    'title' => 'DHL',
                    'track_number' => '2040RR89S1'
                ]);
            $shipment->addTrack($track);
        }

        return $shipment;
    }

    public function getDemoCreditMemo($store)
    {
        $id = $this->scopeConfig
            ->getValue('mtemail/demo/creditmemo_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());

        $creditMemo = $this->creditmemoInterface->load((string)$id, 'increment_id');
        if (!$creditMemo->getId()) {
            $creditMemo = $this->creditmemoInterface->load($id);
        }

        return $creditMemo;
    }

    public function getDemoContactRequest()
    {
        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData([
            'name' => 'John Smith',
            'email'=> 'john.smith@magetrend.com',
            'telephone' => '0037060000001',
            'comment' => 'Hello, I need help with order process. Can you help?'
        ]);
        return $postObject;
    }

    /**
     * Return store code by using store id
     *
     * @param int $storeId
     * @return string
     */
    public function getStoreCode($storeId = 0)
    {
        $storeCode = $this->storeManager->getStore($storeId)->getCode();
        if ($storeCode == 'admin') {
            $storeCode = 'Default';
        }
        $storeCode = ucfirst($storeCode);

        return $storeCode;
    }

    /**
     * Returns email template code
     *
     * @param $template
     */
    public function getTheme($template)
    {
        $origCode = $template->getOrigTemplateCode();
        $origCode = explode('_', $origCode);
        return $origCode[2];
    }

    /**
     * Get block list from template content
     *
     * @param \Magento\Email\Model\Template $template
     * @return array
     */
    public function parseBlockList($template)
    {
        $content = $template->getTemplateText();
        if (substr_count($content, '{{layout handle="') == 0) {
            return [];
        }

        $result = [];
        $blockList = explode('{{layout handle="', $content);
        foreach ($blockList as $block) {
            $blockTmp = explode('}}', $block);
            if (isset($blockTmp[0]) && isset($blockTmp[1])) {
                $result[] = '{{layout handle="'.$blockTmp[0].'}}';
            }
        }

        return $result;
    }

    /**
     * get block data from string format
     *
     * @param $block
     * @return array
     */
    public function parseBlockData($block)
    {
        $blockTmp = str_replace(['{{', '}}', 'layout', "'", '"'], '', $block);
        $blockTmp = explode(' ', $blockTmp);
        $result = [];
        foreach ($blockTmp as $attribute) {
            if (substr_count($attribute, 'block_name') == 1) {
                $result['block_name'] = str_replace(['block_name', ' ', '='], '', $attribute);
            }

            if (substr_count($attribute, 'block_id') == 1) {
                $result['block_id'] = str_replace(['block_id', ' ', '='], '', $attribute);
            }
        }

        return $result;
    }

    /**
     * Get Block Name List
     *
     * @param $blockList
     * @return array
     */
    public function getBlockNameList($blockList)
    {
        if (count($blockList) == 0) {
            return [];
        }

        $result = [];
        foreach ($blockList as $block) {
            $blockData = $this->parseBlockData($block);
            $result[] = $blockData['block_name'];
        }

        return $result;
    }

    public function isSingleTemplateMode()
    {
        return $this->scopeConfig->getValue(
            \Magetrend\Email\Helper\Data::XML_PATH_SINGLE_TEMPLATE_MODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            0
        );
    }

    public function hideSku($storeId = 0)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_HIDE_SKU,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
