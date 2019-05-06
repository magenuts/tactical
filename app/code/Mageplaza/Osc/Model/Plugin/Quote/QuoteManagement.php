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

namespace Mageplaza\Osc\Model\Plugin\Quote;

use Magento\Quote\Model\Quote as QuoteEntity;

class QuoteManagement
{
    /**
     * @var \Mageplaza\Osc\Model\CheckoutRegister
     */
    protected $checkoutRegister;

    public function __construct(\Mageplaza\Osc\Model\CheckoutRegister $checkoutRegister)
    {
        $this->checkoutRegister = $checkoutRegister;
    }

    /**
     * @param \Magento\Quote\Model\QuoteManagement $subject
     * @param QuoteEntity $quote
     * @param array $orderData
     * @return array
     */
    public function beforeSubmit(\Magento\Quote\Model\QuoteManagement $subject, QuoteEntity $quote, $orderData = [])
    {
        $this->checkoutRegister->checkRegisterNewCustomer();

        return [$quote, $orderData];
    }
}