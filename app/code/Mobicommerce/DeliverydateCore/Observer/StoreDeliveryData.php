<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.Mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\DeliverydateCore\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Checkout\Model\Session as CheckoutSession;
use \Mobicommerce\Deliverydate\Helper\Data as DeliveryData;
use Magento\Framework\Session\Storage;

class StoreDeliveryData implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    protected $session;
   
    public function __construct(
        DeliveryData $deliveryHelper,
        CheckoutSession $checkoutSession,
        Storage $sessionStorage
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->deliveryHelper = $deliveryHelper;
        $this->session = $sessionStorage;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->deliveryHelper->moduleEnabled())
        {
            $request = $observer->getRequest();
            $delivery_data = $request->getParam("amdeliverydate");

            $data = [];
            if(!empty($delivery_data['date']))
            {
                 $data = [
                    'date'         => $delivery_data['date'],
                    'tinterval_id' => $delivery_data['tinterval_id'],
                    'comment'      => $delivery_data['comment']
                ];

                $this->session->setData($this->deliveryHelper->getOrderAttributesSessionKey(), $data);
            }
        }
    }
}