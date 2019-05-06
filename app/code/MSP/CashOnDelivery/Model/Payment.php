<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@idealiagroup.com so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_CashOnDelivery
 * @copyright  Copyright (c) 2016 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\CashOnDelivery\Model;

use Magento\Payment\Model\Method\AbstractMethod;

class Payment extends AbstractMethod
{
    const CODE = 'msp_cashondelivery';
    const XML_PATH_EXCLUDE_REGIONS = 'payment/msp_cashondelivery/exclude_regions';

    protected $_code = self::CODE;

    protected $_formBlockType = 'Magento\OfflinePayments\Block\Form\Checkmo';
    protected $_infoBlockType = 'MSP\CashOnDelivery\Block\Info\CashOnDelivery';

    protected $_isOffline = true;

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (!parent::isAvailable($quote)) {
            return false;
        }

				if ($quote->getItemVirtualQty() > 0) {
            return false; //can't use this method if cart contains virtual products
        }

        $excludeRegions = $this->_scopeConfig->getValue(static::XML_PATH_EXCLUDE_REGIONS);

        if (!empty($excludeRegions)) {
            $excludeRegions = explode(',', $excludeRegions);
            foreach ($quote->getAllShippingAddresses() as $shippingAddress) {
                if (in_array($shippingAddress->getRegionId(), $excludeRegions)) {
                    return false;
                }
            }
        }

        return true;
    }
}
