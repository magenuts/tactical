<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Observer\Admin\Order;

use Mobicommerce\Deliverydate\Model\ResourceModel\Deliverydate;
use Magento\Framework\Event\ObserverInterface;

class Save implements ObserverInterface
{

    /**
     * @var \Mobicommerce\Deliverydate\Model\DeliverydateFactory
     */
    protected $deliverydateFactory;

    /**
     * @var \Mobicommerce\Deliverydate\Model\TintervalFactory
     */
    protected $tintervalFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var Deliverydate
     */
    protected $deliverydateResourceModel;

    /**
     * @var \Mobicommerce\Deliverydate\Model\ResourceModel\Tinterval
     */
    protected $tintervalResourceModel;

    public function __construct(
        \Mobicommerce\Deliverydate\Model\DeliverydateFactory $deliverydateFactory,
        \Mobicommerce\Deliverydate\Model\TintervalFactory $tintervalFactory,
        \Magento\Framework\App\RequestInterface $request,
        Deliverydate $deliverydateResourceModel,
        \Mobicommerce\Deliverydate\Model\ResourceModel\Tinterval $tintervalResourceModel
    ) {
        $this->deliverydateFactory = $deliverydateFactory;
        $this->tintervalFactory = $tintervalFactory;
        $this->request = $request;
        $this->deliverydateResourceModel = $deliverydateResourceModel;
        $this->tintervalResourceModel = $tintervalResourceModel;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();

        $data = $this->request->getParam('mobideliverydate');
        if (is_array($data) && !empty($data)) {
            $deliveryDate = $this->deliverydateFactory->create();
            $deliveryDate->prepareForSave($data, $order);
        }
    }
}
