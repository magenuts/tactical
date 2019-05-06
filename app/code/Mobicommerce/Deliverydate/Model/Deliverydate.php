<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\Deliverydate\Model;

use Mobicommerce\Deliverydate\Api\Data\DeliverydateInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime as DateTimeConverter;
use Magento\Store\Model\Store;

class Deliverydate extends \Magento\Framework\Model\AbstractModel implements DeliverydateInterface
{
    /**
     * @var ResourceModel\Tinterval
     */
    protected $tintervalResourceModel;

    /**
     * @var TintervalFactory
     */
    protected $tintervalFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Mobicommerce\Deliverydate\Helper\Data
     */
    protected $deliveryHelper;

    /**
     * @var \Magento\Framework\Data\Form\Filter\DateFactory
     */
    private $dateFactory;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    private $orderRepository;
    /**
     * @var \Mobicommerce\Deliverydate\Model\DeliverydateConfigProvider
     */
    private $configProvider;
    /**
     * @var DeliveryDate\Validator
     */
    private $dateValidator;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messageManager;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Mobicommerce\Deliverydate\Model\ResourceModel\Deliverydate');
        $this->setIdFieldName('deliverydate_id');
    }

    /**
     * Deliverydate constructor.
     *
     * @param \Magento\Framework\Model\Context                $context
     * @param \Magento\Framework\Registry                     $registry
     * @param \Magento\Sales\Model\OrderRepository            $orderRepository
     * @param TintervalFactory                                $tintervalFactory
     * @param ResourceModel\Tinterval                         $tintervalResourceModel
     * @param \Magento\Framework\Data\Form\Filter\DateFactory $dateFactory
     * @param \Mobicommerce\Deliverydate\Helper\Data                $deliveryHelper
     * @param array                                           $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Mobicommerce\Deliverydate\Model\ResourceModel\Deliverydate $resource,
        \Mobicommerce\Deliverydate\Model\ResourceModel\Deliverydate\Collection $resourceCollection,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Mobicommerce\Deliverydate\Model\TintervalFactory $tintervalFactory,
        \Mobicommerce\Deliverydate\Model\ResourceModel\Tinterval $tintervalResourceModel,
        \Magento\Framework\Data\Form\Filter\DateFactory $dateFactory,
        \Mobicommerce\Deliverydate\Helper\Data $deliveryHelper,
        DeliverydateConfigProvider $configProvider,
        \Mobicommerce\Deliverydate\Model\DeliveryDate\Validator $dateValidator,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->tintervalResourceModel = $tintervalResourceModel;
        $this->tintervalFactory       = $tintervalFactory;
        $this->deliveryHelper         = $deliveryHelper;
        $this->dateFactory            = $dateFactory;
        $this->orderRepository        = $orderRepository;
        $this->configProvider         = $configProvider;
        $this->dateValidator          = $dateValidator;
        $this->messageManager         = $messageManager;
    }

    /**
     * Prepare and save data
     *
     * @param array                      $data
     * @param \Magento\Sales\Model\Order $order
     *
     * @return bool
     */
    public function prepareForSave($data, $order)
    {
        $data = $this->preparePostData($data);
        if (!$data) {
            return false;
        }

        if ($order->getId()) {
            $this->_getResource()->load($this, $order->getId(), 'order_id');
        } elseif ($order->getIncrementId()) {
            $this->_getResource()->load($this, $order->getIncrementId(), 'increment_id');
        }
        if ($this->getId() && !isset($data['form_key'])) {
            return false;
        }

        if ($data['date']) {
            // convert from js date format to php date format Y-m-d
            $convertedDate = $this->covertDate($data['date'], DeliverydateConfigProvider::OUTPUT_DATE_FORMAT);
            $data['date'] = $convertedDate;
        }

        if ($data['tinterval_id']) {
            /* load Time interval by ID and combine it to string */
            $tint = $this->tintervalFactory->create();
            $this->tintervalResourceModel->load($tint, (int)$data['tinterval_id']);
            $data['time'] = $tint->getTimeFrom() . " - " . $tint->getTimeTo();
        }
        $this->addData($data);
        $this->setData('order_id', $order->getId());
        $this->setData('increment_id', $order->getIncrementId());

        return true;
    }

    /**
     * @param $date
     * @param $format
     *
     * @return null|string
     */
    public function covertDate($date, $format)
    {
        $filter         = $this->dateFactory->create(['format' => $format]);
        $convertedDate  = $filter->inputFilter($date);
        $dateValidation = \DateTime::createFromFormat(DateTimeConverter::DATE_PHP_FORMAT, $convertedDate);
        if (!$dateValidation && $format == DeliverydateConfigProvider::OUTPUT_DATE_FORMAT) {
            return $this->covertDate($date, $this->configProvider->getPickerDateFormat());
        }
        if (!$dateValidation) {
            // data is invalid
            return null;
        }

        return $convertedDate;
    }

    /**
     * Prepare and validate data
     *
     * @param array $data
     *
     * @return false|array    false if no need to save
     */
    public function preparePostData(array $data)
    {
        if (!isset($data['date']) || $data['date'] == '0000-00-00' || !$data['date']) {
            $data['date'] = null;
        }
        if (!isset($data['tinterval_id']) || !$data['tinterval_id']) {
            $data['tinterval_id'] = null;
        }
        if (!isset($data['comment']) || !$data['comment']) {
            $data['comment'] = null;
        }

        if (!$data['date'] && !$data['tinterval_id'] && !$data['comment']) {
            return false;
        }

        return $data;
    }

    /**
     * Validate Deliverydate Data
     *
     * @param \Magento\Sales\Model\Order $order
     */
    public function validate($order)
    {
        $shippingMethod = $order->getShippingMethod();
        $customerGroup = $order->getCustomerGroupId();

        if (!$this->getDate()
            && $this->deliveryHelper->isFieldEnabled('date', $shippingMethod, $customerGroup)
            && $this->deliveryHelper->getStoreScopeValue('date_field/required')
        ) {
            $this->throwValidatorException(__('Delivery Date is required'));
        }

        if (!$this->getTintervalId()
            && $this->deliveryHelper->isFieldEnabled('time', $shippingMethod, $customerGroup)
            && $this->deliveryHelper->getStoreScopeValue('time_field/required')
        ) {
            $this->throwValidatorException(__('Delivery Time is required'));
        }

        if (!$this->getComment()
            && $this->deliveryHelper->getStoreScopeValue('comment_field/required')
            && $this->deliveryHelper->isFieldEnabled('comment', $shippingMethod, $customerGroup)
        ) {
            $this->throwValidatorException(__('Delivery Comment is required'));
        }

        if ($this->getDate() && !$this->dateValidator->validate($this->getDate())) {
            $this->throwValidatorException(__('Delivery Date is invalid, please choose another date'));
        }
    }

    /**
     * @param Phrase $message
     *
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    private function throwValidatorException($message)
    {
        $this->messageManager->addErrorMessage($message);
        throw new \Magento\Framework\Exception\ValidatorException($message);
    }

    /**
     * Check rules is Delivery Date field can be edited by customer
     *
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isCanEditOnFront($store = null)
    {
        if ($this->deliveryHelper->getStoreScopeValue('editable/is_editable', $store)) {
            switch ($this->deliveryHelper->getStoreScopeValue('editable/rule_activation', $store)) {
                case 'both':
                    return $this->checkDateRule() && $this->checkOrderStatus();
                case 'one_of':
                    return $this->checkDateRule() || $this->checkOrderStatus();
                case 'status':
                    return $this->checkOrderStatus();
                case 'date':
                    return $this->checkDateRule();
            }
        }
        return false;
    }

    /**
     * Delivery Date can be edited up tp $period days
     * If order don't have Delivery Date then allow to edit
     *
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function checkDateRule($store = null)
    {
        $deliveryDate = strtotime($this->getDate());
        if ($deliveryDate) {
            $period = (int)$this->deliveryHelper->getStoreScopeValue('editable/period', $store);

            /**
             * DD - today                      the remaining time for delivering in seconds;
             * ceil(seconds / 60 / 60 / 24)    convert to days with round up;
             * Days > $period                  is Delivery Date can be edited;
             */
            return (ceil(($deliveryDate - time()) / 60 / 60 / 24) > $period);
        }

        return true;
    }

    /**
     * Is Order have status for reschedule Delivery Date
     *
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function checkOrderStatus($store = null)
    {
        $status = $this->getOrder()->getStatus();
        $rescheduleStatus = explode(',', $this->deliveryHelper->getStoreScopeValue('editable/order_status', $store));

        return is_array($rescheduleStatus) && in_array($status, $rescheduleStatus);
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        if (!$this->_getData('order')) {
            $order = $this->orderRepository->get($this->getOrderId());
            $this->setData('order', $order);
        }

        return $this->_getData('order');
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->setData('order', $order);
        return $this;
    }

    /**
     * Get formatted date for display
     *
     * @return string
     */
    public function getFormattedDate()
    {
        if ($this->_getData('formatted_date') === null && $this->getDate() && $this->getDate() != '0000-00-00') {
            $format = $this->deliveryHelper->getStoreScopeValue('date_field/format');
            $date = $this->deliveryHelper->convertDateOutput($this->getDate(), $format);
            $this->setData('formatted_date', $date);
        }

        return $this->_getData('formatted_date');
    }

    /**
     * Prepare Comment for display
     *
     * @return string
     */
    public function getFormattedComment()
    {
        if ($this->_getData('formatted_comment') === null) {
             $comment = nl2br(htmlentities(
                 preg_replace('/\$/', '\\\$', $this->getComment()),
                 ENT_COMPAT,
                 "UTF-8"
             ));
             $this->setData('formatted_comment', $comment);
        }

        return $this->_getData('formatted_comment');
    }

    /**
     * Returns Deliverydate ID
     *
     * @return int
     */
    public function getDeliverydateId()
    {
        return $this->_getData(self::DELIVERYDATE_ID);
    }

    /**
     * @param int $deliverydateId
     *
     * @return $this
     */
    public function setDeliverydateId($deliverydateId)
    {
        $this->setData(self::DELIVERYDATE_ID, $deliverydateId);

        return $this;
    }

    /**
     * Returns Order ID
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->_getData(self::ORDER_ID);
    }

    /**
     * @param int $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId)
    {
        $this->setData(self::ORDER_ID, $orderId);

        return $this;
    }

    /**
     * Returns Order IncrementId
     *
     * @return string
     */
    public function getIncrementId()
    {
        return $this->_getData(self::INCREMENT_ID);
    }

    /**
     * @param string $incrementId
     *
     * @return $this
     */
    public function setIncrementId($incrementId)
    {
        $this->setData(self::INCREMENT_ID, $incrementId);

        return $this;
    }

    /**
     * Returns date
     *
     * @return string
     */
    public function getDate()
    {
        $date = $this->_getData(self::DATE);
        if ($date == '0000-00-00') {
            return null;
        }
        return $date;
    }

    /**
     * @param string $date
     *
     * @return $this
     */
    public function setDate($date)
    {
        $this->setData(self::DATE, $date);

        return $this;
    }

    /**
     * Returns Time
     *
     * @return string
     */
    public function getTime()
    {
        return $this->_getData(self::TIME);
    }

    /**
     * @param string $time
     *
     * @return $this
     */
    public function setTime($time)
    {
        $this->setData(self::TIME, $time);

        return $this;
    }

    /**
     * Returns comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->_getData(self::COMMENT);
    }

    /**
     * @param string $comment
     *
     * @return $this
     */
    public function setComment($comment)
    {
        $this->setData(self::COMMENT, $comment);

        return $this;
    }

    /**
     * Returns reminder
     *
     * @return int
     */
    public function getReminder()
    {
        return $this->_getData(self::REMINDER);
    }

    /**
     * @param int $reminder
     *
     * @return $this
     */
    public function setReminder($reminder)
    {
        $this->setData(self::REMINDER, $reminder);

        return $this;
    }

    /**
     * Returns time interval ID
     *
     * @return int
     */
    public function getTintervalId()
    {
        return $this->_getData(self::TINTERVAL_ID);
    }

    /**
     * @param int $tintervalId
     *
     * @return $this
     */
    public function setTintervalId($tintervalId)
    {
        $this->setData(self::TINTERVAL_ID, $tintervalId);

        return $this;
    }

    /**
     * Returns is active
     *
     * @return int
     */
    public function getActive()
    {
        return $this->_getData(self::ACTIVE);
    }

    /**
     * @param int $active
     *
     * @return $this
     */
    public function setActive($active)
    {
        $this->setData(self::ACTIVE, $active);

        return $this;
    }
}
