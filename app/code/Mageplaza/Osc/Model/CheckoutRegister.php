<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Osc\Model;

use Magento\Quote\Model\CustomerManagement;
use Magento\Quote\Model\Quote;

/**
 * Class CheckoutRegister
 * @package Mageplaza\Osc\Model
 */
class CheckoutRegister
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @type \Magento\Framework\DataObject\Copy
     */
    protected $_objectCopyService;

    /**
     * @type \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @type \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var CustomerManagement
     */
    protected $customerManagement;

    /**
     * @var bool
     */
    protected $_isCheckedRegister = false;

    /**
     * @var \Mageplaza\Osc\Helper\Data
     */
    protected $_oscHelperData;

    /**
     * CheckoutRegister constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Quote\Model\CustomerManagement $customerManagement
     * @param \Mageplaza\Osc\Helper\Data $oscHelperData
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        CustomerManagement $customerManagement,
        \Mageplaza\Osc\Helper\Data $oscHelperData
    )
    {
        $this->checkoutSession    = $checkoutSession;
        $this->_objectCopyService = $objectCopyService;
        $this->dataObjectHelper   = $dataObjectHelper;
        $this->accountManagement  = $accountManagement;
        $this->customerManagement = $customerManagement;
        $this->_oscHelperData     = $oscHelperData;
    }

    /**
     * @return $this
     */
    public function checkRegisterNewCustomer()
    {
        if ($this->isCheckedRegister()) {
            return $this;
        }
        $this->setIsCheckedRegister(true);

        /** @type \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        /** Validate address */
        $this->validateAddressBeforeSubmit($quote);

        /** One step check out additional data */
        $oscData = $this->checkoutSession->getOscData();

        /** Create account when checkout */
        if (isset($oscData['register']) && $oscData['register']
            && isset($oscData['password']) && $oscData['password']
        ) {
            $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER)
                ->setCustomerIsGuest(false)
                ->setCustomerGroupId(null)
                ->setPasswordHash($this->accountManagement->getPasswordHash($oscData['password']));

            $this->_prepareNewCustomerQuote($quote, $oscData);
        }

        return $this;
    }

    /**
     * Prepare quote for customer registration and customer order submit
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return void
     */
    protected function _prepareNewCustomerQuote(Quote $quote, $oscData)
    {
        $billing  = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $customer  = $quote->getCustomer();
        $dataArray = $billing->getData();
        if (isset($oscData['customerAttributes']) && $oscData['customerAttributes']) {
            $dataArray = array_merge($dataArray, $oscData['customerAttributes']);
        }
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $dataArray,
            '\Magento\Customer\Api\Data\CustomerInterface'
        );

        $quote->setCustomer($customer);
        $this->_oscHelperData->setFlagOscMethodRegister(true);

        /** Init customer address */
        $customerBillingData = $billing->exportCustomerAddress();
        $customerBillingData->setIsDefaultBilling(true)
            ->setData('should_ignore_validation', true);

        if ($shipping) {
            if (isset($oscData['same_as_shipping']) && $oscData['same_as_shipping']) {
                $shipping->setCustomerAddressData($customerBillingData);
                $customerBillingData->setIsDefaultShipping(true);
            } else {
                $customerShippingData = $shipping->exportCustomerAddress();
                $customerShippingData->setIsDefaultShipping(true)
                    ->setData('should_ignore_validation', true);
                $shipping->setCustomerAddressData($customerShippingData);
                // Add shipping address to quote since customer Data Object does not hold address information
                $quote->addCustomerAddress($customerShippingData);
            }
        } else {
            $customerBillingData->setIsDefaultShipping(true);
        }
        $billing->setCustomerAddressData($customerBillingData);
        // Add billing address to quote since customer Data Object does not hold address information
        $quote->addCustomerAddress($customerBillingData);

        /** Create customer */
        $this->customerManagement->populateCustomerInfo($quote);

        // If customer is created, set customerId for address to avoid create more address when checkout
        if ($customerId = $quote->getCustomerId()) {
            $quote->getBillingAddress()->setCustomerId($customerId);
            if (!$quote->isVirtual()) {
                $quote->getShippingAddress()->setCustomerId($customerId);
            }
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function validateAddressBeforeSubmit(\Magento\Quote\Model\Quote $quote)
    {
        /** Remove address validation */
        if (!$quote->isVirtual()) {
            $quote->getShippingAddress()->setShouldIgnoreValidation(true);
        }
        $quote->getBillingAddress()->setShouldIgnoreValidation(true);

        // TODO : Validate address (depend on field require, show on osc or not)

        return $this;
    }

    /**
     * @return bool
     */
    public function isCheckedRegister()
    {
        return $this->_isCheckedRegister;
    }

    /**
     * @param bool $isCheckedRegister
     */
    public function setIsCheckedRegister($isCheckedRegister)
    {
        $this->_isCheckedRegister = $isCheckedRegister;
    }
}