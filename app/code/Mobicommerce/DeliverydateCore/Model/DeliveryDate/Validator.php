<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.Mobicommerce.com)
 * @package Mobicommerce_DeliverydateCore
 */

namespace Mobicommerce\DeliverydateCore\Model\DeliveryDate;

use \Mobicommerce\Deliverydate\Helper\Data as DeliverydateHelper;
use \Mobicommerce\Deliverydate\Model\DeliverydateConfigProvider;
use \Magento\Framework\Stdlib\DateTime as DateTimeConverter;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Framework\Data\Form\Filter\DateFactory;
use \Mobicommerce\Deliverydate\Model\DeliveryDate\DateDataObjectFactory;

class Validator extends \Mobicommerce\Deliverydate\Model\DeliveryDate\Validator
{
    public function __construct(
        DeliverydateHelper $helper,
        DateTime $dateLib,
        DateFactory $dateFactory,
        DeliverydateConfigProvider $configProvider,
        DateDataObjectFactory $dataObjectFactory
    ) {
        $this->dateLib = $dateLib;
        $this->configProvider = $configProvider;
        $this->currentDeliveryDate = $dataObjectFactory->create();
        parent::__construct($helper,$dateLib,$dateFactory,$configProvider,$dataObjectFactory);
    }

    /**
     * @param string $deliveryDate
     */
    private function setCurrentDeliveryDate($deliveryDate)
    {
        $this->currentDeliveryDate->setDate($deliveryDate);
        $timestamp = $this->dateLib->timestamp($deliveryDate);
        $this->currentDeliveryDate->setObject(new \Zend_Date($timestamp, \Zend_Date::TIMESTAMP));
        $this->currentDeliveryDate->setTimestamp($timestamp);
        $this->currentDeliveryDate->setYear($this->dateLib->date('Y', $timestamp));
        $this->currentDeliveryDate->setMonth($this->dateLib->date('n', $timestamp));
        $this->currentDeliveryDate->setDay($this->dateLib->date('d', $timestamp));
    }

    /**
     * Validate Delivery Date
     *
     * @param string $deliveryDate date in mysql format YYYY-mm-dd
     *
     * @return bool
     */
    public function validate($deliveryDate)
    {
        $this->setCurrentDeliveryDate($deliveryDate);
        return parent::validate($deliveryDate) && !$this->daysOfWeek($deliveryDate);
    }

    /**
     * Is current day of the week restricted
     *
     * @return bool
     */
    private function daysOfWeek()
    {
        $days = $this->configProvider->getDisabledDays(); 
        return $days && in_array($this->dateLib->date('w', $this->currentDeliveryDate->getTimestamp()), $days);
    }
}
