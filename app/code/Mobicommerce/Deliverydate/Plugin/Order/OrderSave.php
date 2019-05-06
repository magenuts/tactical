<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Plugin\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderSave
{
    /**
     * @var \Mobicommerce\Deliverydate\Helper\Data
     */
    private $mobiHelper;

    /**
     * @var \Mobicommerce\Deliverydate\Model\DeliverydateFactory
     */
    private $deliverydateFactory;

    /**
     * @var \Mobicommerce\Deliverydate\Model\ResourceModel\Deliverydate
     */
    private $deliverydateResource;

    public function __construct(
        \Mobicommerce\Deliverydate\Helper\Data $mobiHelper,
        \Mobicommerce\Deliverydate\Model\DeliverydateFactory $deliverydateFactory,
        \Mobicommerce\Deliverydate\Model\ResourceModel\Deliverydate $deliverydateResource
    ) {
        $this->mobiHelper = $mobiHelper;
        $this->deliverydateFactory = $deliverydateFactory;
        $this->deliverydateResource = $deliverydateResource;
    }

    /**
     * Validate Order Delivery Date before place order
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface      $order
     *
     * @return OrderInterface
     */
    public function beforeSave(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $data = $this->mobiHelper->getDeliveryDataFromSession();
        if (is_array($data)) {
            /** @var \Mobicommerce\Deliverydate\Model\Deliverydate $deliveryDate */
            $deliveryDate = $this->deliverydateFactory->create();
            $deliveryDate->prepareForSave($data, $order);
            $deliveryDate->validate($order);
        }

        return [$order];
    }

    /**
     * Save Order Delivery Date from session
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface      $order
     *
     * @return OrderInterface
     */
    public function afterSave(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $data = $this->mobiHelper->getDeliveryDataFromSession();
        if (is_array($data)) {
            /** @var \Mobicommerce\Deliverydate\Model\Deliverydate $deliveryDate */
            $deliveryDate = $this->deliverydateFactory->create();
            if ($deliveryDate->prepareForSave($data, $order)) {
                $this->deliverydateResource->save($deliveryDate);
            }
        }
        return $order;
    }
}
