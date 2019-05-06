<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Plugin\Order;

use Mobicommerce\Deliverydate\Model\DeliverydateRepository;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Model\OrderRepository;

/**
 * @since @1.4.0
 */
class OrderRepositoryPlugin
{
    /**
     * @var DeliverydateRepository
     */
    private $deliverydateRepository;

    /**
     * @var OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * OrderRepositoryPlugin constructor.
     *
     * @param DeliverydateRepository $deliverydateRepository
     * @param OrderExtensionFactory  $orderExtensionFactory
     */
    public function __construct(
        DeliverydateRepository $deliverydateRepository,
        OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->deliverydateRepository = $deliverydateRepository;
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    /**
     * @param OrderRepository   $subject
     * @param OrderInterface    $order
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepository $subject, OrderInterface $order)
    {
        $this->loadDeliveryDateExtensionAttributes($order);

        return $order;
    }

    /**
     * @param OrderRepository               $subject
     * @param OrderSearchResultInterface    $orderCollection
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepository $subject, OrderSearchResultInterface $orderCollection)
    {
        foreach ($orderCollection->getItems() as $order) {
            $this->loadDeliveryDateExtensionAttributes($order);
        }

        return $orderCollection;
    }

    /**
     * @param OrderInterface $order
     */
    private function loadDeliveryDateExtensionAttributes(OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->orderExtensionFactory->create();
        }
        if ($extensionAttributes->getMobideliverydateDate() !== null) {
            // Delivery Date entity is already loaded; no actions required
            return;
        }
        try {
            $deliveryDate = $this->deliverydateRepository->getByOrder($order->getEntityId());

            $extensionAttributes->setMobideliverydateDate($deliveryDate->getDate());
            $extensionAttributes->setMobideliverydateTime($deliveryDate->getTime());
            $extensionAttributes->setMobideliverydateComment($deliveryDate->getComment());

            $order->setExtensionAttributes($extensionAttributes);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Delivery Date entity cannot be loaded for current order; no actions required
            return;
        }
    }
}
