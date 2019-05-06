<?php

namespace Telr\TelrPayments\Controller;

abstract class TelrPayments extends \Magento\Framework\App\Action\Action {

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote = false;

    protected $_telrModel;

    protected $_telrHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Telr\TelrPayments\Model\TelrPayments $telrModel
     * @param \Telr\TelrPayments\Helper\TelrPayments $telrHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Telr\TelrPayments\Model\TelrPayments $telrModel,
        \Telr\TelrPayments\Helper\TelrPayments $telrHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;

        $this->logger = $logger;
        $this->_telrModel = $telrModel;
        $this->_telrHelper = $telrHelper;

        parent::__construct($context);
    }

    /**
     * Cancel order, return quote to customer
     *
     * @param string $errorMsg
     * @return false|string
     */
    protected function _cancelPayment($errorMsg = '') {
        $gotoSection = false;
        $this->_telrHelper->cancelCurrentOrder($errorMsg);
        if ($this->_checkoutSession->restoreQuote()) {
            //Redirect to payment step
            $gotoSection = 'paymentMethod';
        }
        return $gotoSection;
    }

    /**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrderById($order_id) {
        $order=$this->_orderFactory->create()->load($order_id);
	return $order;
    }

    /**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrder() {
        return $this->_orderFactory->create()->loadByIncrementId(
            $this->_checkoutSession->getLastRealOrderId()
        );
    }

    protected function getQuote() {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

    protected function getCheckoutSession() {
        return $this->_checkoutSession;
    }

    protected function getCustomerSession() {
        return $this->_customerSession;
    }

    protected function getTelrModel() {
        return $this->_telrModel;
    }

    protected function getTelrHelper() {
        return $this->_telrHelper;
    }
}
