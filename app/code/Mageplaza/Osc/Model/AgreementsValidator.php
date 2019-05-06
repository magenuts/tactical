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

/**
 * Class AgreementsValidator
 * @package Mageplaza\Osc\Model
 */
class AgreementsValidator extends \Magento\CheckoutAgreements\Model\AgreementsValidator
{
    /**
     * @var \Mageplaza\Osc\Helper\Data
     */
    protected $_oscHelperConfig;

    /**
     * AgreementsValidator constructor.
     * @param \Mageplaza\Osc\Helper\Config $oscHelperConfig
     * @param null $list
     */
    public function __construct(\Mageplaza\Osc\Helper\Config $oscHelperConfig, $list = null)
    {
        parent::__construct($list);
        $this->_oscHelperConfig = $oscHelperConfig;
    }

    /**
     * @param array $agreementIds
     * @return bool
     */
    public function isValid($agreementIds = [])
    {
        if (!$this->_oscHelperConfig->isEnabledTOC()) {
            return true;
        } else {
            return parent::isValid($agreementIds);
        }
    }
}