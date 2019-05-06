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

namespace Mageplaza\Osc\Model\Plugin\Authorization;

use Magento\Authorization\Model\UserContextInterface;

class UserContext
{
    /**
     * @var \Mageplaza\Osc\Helper\Data
     */
    protected $_oscHelperData;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * UserContext constructor.
     * @param \Mageplaza\Osc\Helper\Data $oscHelperData
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Mageplaza\Osc\Helper\Data $oscHelperData,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_oscHelperData = $oscHelperData;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param UserContextInterface $userContext
     * @param $result
     * @return int
     */
    public function afterGetUserType(UserContextInterface $userContext, $result)
    {
        if($this->_oscHelperData->isFlagOscMethodRegister()) {
            return UserContextInterface::USER_TYPE_CUSTOMER;
        }

        return $result;
    }

    /**
     * @param UserContextInterface $userContext
     * @param $result
     * @return int
     */
    public function afterGetUserId(UserContextInterface $userContext, $result)
    {
        if($this->_oscHelperData->isFlagOscMethodRegister()) {
            return $this->_checkoutSession->getQuote()->getCustomerId();
        }

        return $result;
    }
}