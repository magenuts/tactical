<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\Deliverydate\Model\Config\Source;

class Show implements \Magento\Framework\Option\ArrayInterface
{

    const ORDER_VIEW = 'order_view';
    const ORDER_CREATE = 'order_create';
    const INVOICE_VIEW = 'invoice_view';
    const SHIPMENT_VIEW = 'shipment_view';
    const ORDER_INFO = 'order_info';

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::ORDER_VIEW,
                'label' => __('Order View Page (Backend)')
            ),
            array(
                'value' => self::ORDER_CREATE,
                'label' => __('New/Edit/Reorder Order Page (Backend)')
            ),
            array(
                'value' => self::INVOICE_VIEW,
                'label' => __('Invoice View Page (Backend)')
            ),
            array(
                'value' => self::SHIPMENT_VIEW,
                'label' => __('Shipment View Page (Backend)')
            ),
            array(
                'value' => self::ORDER_INFO,
                'label' => __('Order Info Page (Frontend)')
            ),
        );
    }
}
