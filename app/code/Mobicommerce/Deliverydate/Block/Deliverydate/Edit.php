<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\Deliverydate\Block\Deliverydate;

/**
 * Delivery Date Edit
 */
class Edit extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'edit.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Mobicommerce\Deliverydate\Helper\Data
     */
    private $deliveryHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Mobicommerce\Deliverydate\Model\DeliverydateConfigProvider
     */
    private $configProvider;

    /**
     * Edit constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Framework\Json\EncoderInterface         $jsonEncoder
     * @param \Mobicommerce\Deliverydate\Helper\Data                 $deliveryHelper
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Mobicommerce\Deliverydate\Helper\Data $deliveryHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Mobicommerce\Deliverydate\Model\DeliverydateConfigProvider $configProvider,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->jsonEncoder = $jsonEncoder;
        $this->deliveryHelper = $deliveryHelper;
        $this->customerSession = $customerSession;
        $this->configProvider = $configProvider;
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Order # %1', $this->getOrder()->getRealOrderId()));
    }

    /**
     * @return string json
     */
    public function getCalendarJsonConfig()
    {
        return $this->jsonEncoder->encode([
            'minDate' => $this->deliveryHelper->getMinDays(),
            'maxDate' => $this->deliveryHelper->getStoreScopeValue('general/max_days'),
            'dateFormat' => $this->getCalendarDateFormat(),
        ]);
    }

    /**
     * @return string
     */
    protected function getCalendarDateFormat()
    {
        $format = $this->deliveryHelper->getStoreScopeValue('date_field/format');
        return preg_replace(['/D/s','/M/s'], ['d','m'], $format);

    }

    /**
     * @return string json
     */
    public function getMobicommerceCalendarJsonConfig()
    {
        return $this->jsonEncoder->encode([
            'amdeliveryconf' => $this->configProvider->getDeliveryDateFieldConfig()
        ]);
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Retrieve current DD model instance
     *
     * @return \Mobicommerce\Deliverydate\Model\Deliverydate
     */
    public function getDeliveryDate()
    {
        return $this->coreRegistry->registry('current_mobicommerce_deliverydate');
    }

    /**
     * Return url for form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->getUrl('mobicommerce_deliverydate/deliverydate/save', ['order_id' => $this->getOrder()->getId()]);
        }
        return $this->getUrl('mobicommerce_deliverydate/guest/save', ['order_id' => $this->getOrder()->getId()]);
    }

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->getUrl('sales/order/view', ['order_id' => $this->getOrder()->getId()]);
        }
        return $this->getUrl('sales/guest/view');
    }

    /**
     * Return back title for logged in and guest users
     *
     * @return \Magento\Framework\Phrase
     */
    public function getBackTitle()
    {
        return __('Back to Order # %1', $this->getOrder()->getRealOrderId());
    }
}
