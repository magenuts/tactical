<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Helper;

use Mobicommerce\Deliverydate\Model\ResourceModel\Tinterval\CollectionFactory as TintervalCollectionFactory;
use Mobicommerce\Deliverydate\Model\Holidays;
use Magento\Framework\Session\Storage;
use Magento\Store\Model\Store;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_MODULE_ENABLE = "general/enabled";
    /**
     * Store mintDays
     * @var int|null
     */
    protected $minDays = null;

    protected $dictionary = [
            'dd'   => '%d',
            'd'    => '%j',
            'MM'   => '%m',
            'M'    => '%n',
            'yyyy' => '%Y',
            'yy'   => '%y',
        ];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Mobicommerce\Deliverydate\Model\TintervalFactory
     */
    private $tintervalFactory;

    /**
     * @var Storage
     */
    private $session;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;
    /**
     * @var \Magento\Framework\Data\Form\Filter\DateFactory
     */
    private $dateFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        TintervalCollectionFactory $tintervalFactory,
        Storage $sessionStorage,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Data\Form\Filter\DateFactory $dateFactory
    ) {
        parent::__construct($context);
        $this->date = $date;
        $this->objectManager = $objectManager;
        $this->session = $sessionStorage;
        $this->tintervalFactory = $tintervalFactory;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->dateFactory = $dateFactory;
    }

    /**
     * @return bool
     */
    public function isModuleEnable()
    {
        return (bool)$this->getStoreScopeValue(Self::CONFIG_MODULE_ENABLE);
    }

    /**
     * @return array
     */
    public function getDays()
    {
        $days = [];
        for ($i = 1; $i <= 31; $i++) {
            $days[$i] = $i;
        }
        return $days;
    }

    /**
     * @param bool $addEach
     *
     * @return array
     */
    public function getMonths($addEach = false)
    {
        $months = [
            1  => __('January'),
            2  => __('February'),
            3  => __('March'),
            4  => __('April'),
            5  => __('May'),
            6  => __('June'),
            7  => __('July'),
            8  => __('August'),
            9  => __('September'),
            10 => __('October'),
            11 => __('November'),
            12 => __('December')
        ];

        if ($addEach) {
            $months[0] = __('Each month');
        }

        return $months;
    }

    /**
     * Convert date by format
     *
     * @param string $date in mysql format 'yyyy-MM-dd'
     * @param string|null $format
     *
     * @return string
     */
    public function convertDateOutput($date, $format = null)
    {
        if ($date == '0000-00-00') {
            return '';
        }
        if ($format === null) {
            $format = $this->getStoreScopeValue('date_field/format');
        }
        return $this->dateFactory->create(['format' => $format])->outputFilter($date);
    }

    /**
     * Get array of year start from current Year
     *
     * @since 1.3.0 Grid functionality moved to DateCollectionAbstract
     * @return array
     */
    public function getYears($addEach = false)
    {
        $years = [];
        if ($addEach) {
            $years = [['value' => 0, 'label' => __("Each year")]];
        }
        $curYear = $this->date->date('Y');
        for ($i = 0; $i <= 4; $i++) {
            $years[$curYear + $i] = $curYear + $i;
        }

        return $years;
    }

    /**
     * @return array
     */
    public function getTypeDay()
    {
        return [
            Holidays::HOLIDAY    => __('Holiday'),
            Holidays::WORKINGDAY => __('Working day'),
        ];
    }

    /**
     * Is Delivery Date Module enabled from Configuration
     *
     * @return bool
     */
    public function moduleEnabled()
    {
        return (bool)$this->scopeConfig->getValue('mobideliverydate/general/enabled');
    }

    /**
     * Get array of Delivery Date fields name which can be shown on $place
     *
     * @param string $place
     * @param string $include
     *
     * @return array
     */
    public function whatShow($place = 'order_view', $include = 'show')
    {
        $fields = [];

        if (in_array($place, explode(',', $this->scopeConfig->getValue('mobideliverydate/date_field/' . $include)))) {
            $fields[] = 'date';
        }
        if (in_array($place, explode(',', $this->scopeConfig->getValue('mobideliverydate/time_field/' . $include)))) {
            $fields[] = 'time';
        }
        if (in_array($place, explode(',', $this->scopeConfig->getValue('mobideliverydate/comment_field/' . $include)))) {
            $fields[] = 'comment';
        }

        return $fields;
    }

    /**
     * @param int $currentStore
     *
     * @return array
     */
    public function getTIntervals($currentStore = 0)
    {
        $tIntervals = ['' => __('Please select time interval.')];

        $collection = $this->tintervalFactory->create();
        $collection->getSelect()->order('sorting_order');

        foreach ($collection as $tInterval) {
            $storeIds = trim($tInterval->getData('store_ids'), ',');
            $storeIds = explode(',', $storeIds);
            if (!in_array($currentStore, $storeIds) && !in_array(0, $storeIds)) {
                continue;
            }

            $value = $tInterval->getData('time_from') . ' - ' . $tInterval->getData('time_to');
            $tIntervals[$tInterval->getId()] = $value;
        }

        return $tIntervals;
    }

    /**
     * Get default config value.
     * Use it only if config can have only Default scope or from admin
     *
     * @param string $path
     *
     * @return mixed
     */
    public function getDefaultScopeValue($path)
    {
        return $this->scopeConfig->getValue('mobideliverydate/' . $path);
    }

    /**
     * Get config value for Website
     *
     * @param string $path
     *
     * @return mixed
     */
    public function getWebsiteScopeValue($path, $website = null)
    {
        return $this->scopeConfig->getValue(
            'mobideliverydate/' . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $website
        );
    }

    /**
     * Get config value for Store
     *
     * @param string  $path
     * @param null|string|bool|int|Store $store
     *
     * @return mixed
     */
    public function getStoreScopeValue($path, $store = null)
    {
        return $this->scopeConfig->getValue(
            'mobideliverydate/' . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Save Data to session
     *
     * @param array $data
     */
    public function setDeliveryDataToSession($data)
    {
        $this->session->setData($this->getOrderAttributesSessionKey(), $data);
    }

    /**
     * load Data to sassion
     *
     * @return array
     */
    public function getDeliveryDataFromSession()
    {
        return $this->session->getData($this->getOrderAttributesSessionKey());
    }

    /**
     * get Session Key
     *
     * @return string
     */
    public function getOrderAttributesSessionKey()
    {
        return 'mobicommerce_delivery_date';
    }

    /**
     * Get Date format
     *
     * @return string
     */
    public function getPhpFormat()
    {
        return str_replace('%', '', $this->convert($this->getDefaultScopeValue('date_field/format')));
    }

    /**
     * Convert js date format for php date format
     *
     * @param string $value
     *
     * @return string
     */
    protected function convert($value)
    {
        foreach ($this->dictionary as $search => $replace) {
            $value = preg_replace('/(^|[^%])' . $search . '/', '$1' . $replace, $value);
        }
        return $value;
    }

    /**
     * Get Minimal Delivery Interval.
     * Can be by product attribute
     * return 0 - same day delivery
     *
     * @return int
     */
    public function getMinDays()
    {
        if ($this->minDays === null) {
            $this->minDays     = (int)$this->getStoreScopeValue('general/min_days');
            $attrCode = $this->getStoreScopeValue('general/min_days_attr');
            if ($attrCode) {
                $items      = $this->checkoutSession->getQuote()->getAllItems();
                $productIds = [];
                foreach ($items as $item) {
                    // collect product ids in cart
                    $productIds[] = $item->getProductId();
                }
                $attributeValues = $this->productCollectionFactory->create()
                    ->addIdFilter($productIds)
                    ->addAttributeToSelect($attrCode)
                    ->getColumnValues($attrCode);

                foreach ($attributeValues as $minByProduct) {
                    if ($minByProduct && $minByProduct > $this->minDays) {
                        $this->minDays = $minByProduct;
                    }
                }
            }
        }

        return $this->minDays;
    }

    public function getTimeOffset()
    {
        return (int)$this->getStoreScopeValue('general/offset');
    }

    /**
     * @deprecated
     * @since 1.3.0 method moved to DeliverydateConfigProvider
     * @return array
     */
    public function getDeliveryConfig()
    {
        return $this->objectManager->create('Mobicommerce\Deliverydate\Model\DeliverydateConfigProvider')
            ->getDeliveryDateFieldConfig();
    }

    public function isEnabledForShippingMethod($field, $method)
    {
        if (!$this->getStoreScopeValue($field . '_field/enabled_carriers')) {
            return true;
        }

        if (in_array($method, explode(',', $this->getStoreScopeValue($field . '_field/carriers')))) {
            return true;
        } else {
            return false;
        }
    }

    public function isEnabledForCustomerGroup($field, $customerGroup)
    {
        if (!$this->getStoreScopeValue($field . '_field/enabled_customer_groups')) {
            return true;
        }

        if (in_array($customerGroup, explode(',', $this->getStoreScopeValue($field . '_field/customer_groups')))) {
            return true;
        } else {
            return false;
        }
    }

    public function isFieldEnabled($field, $shippingMethod, $customerGroup)
    {
        $isEnabled = $this->isEnabledForShippingMethod($field, $shippingMethod)
            && $this->isEnabledForCustomerGroup($field, $customerGroup);

        return $isEnabled;
    }
}
