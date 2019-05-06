<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Plugin\Checkout;

class ShippingInformationManagement
{
    /**
     * @var \Mobicommerce\Deliverydate\Helper\Data
     */
    protected $mobiHelper;

    public function __construct(
        \Mobicommerce\Deliverydate\Helper\Data $mobiHelper
    ) {
        $this->mobiHelper = $mobiHelper;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function aroundSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $extAttributes = $addressInformation->getExtensionAttributes();
        if ($extAttributes instanceof \Magento\Checkout\Api\Data\ShippingInformationExtension) {
            $data = [
                'date'         => $extAttributes->getMobideliverydateDate(),
                'tinterval_id' => $extAttributes->getMobideliverydateTime(),
                'comment'      => $extAttributes->getMobideliverydateComment()
            ];
            $this->mobiHelper->setDeliveryDataToSession($data);
        }

        return $proceed($cartId, $addressInformation);
    }
}
