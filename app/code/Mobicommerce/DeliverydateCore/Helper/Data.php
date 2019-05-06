<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\DeliverydateCore\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_MODULE_ENABLE = "general/enabled";

    protected $deliverydateFactory;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Mobicommerce\Deliverydate\Model\DeliverydateFactory $deliverydateFactory
    ) {
        parent::__construct($context);
        $this->deliverydateFactory = $deliverydateFactory;
    }

    /**
     * @return bool
     */
    public function isModuleEnable()
    {
        return (bool)$this->getStoreScopeValue(Self::CONFIG_MODULE_ENABLE);
    }

    /**
     * Get config value for Store
     *
     * @param string  $path
     * @param null|string|bool|int|Store $store
     *
     * @return mixed
     */
    public function getStoreScopeValue($path, $store = null)
    {
        return $this->scopeConfig->getValue(
            'mobideliverydate/' . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getOrderDeliveryParameters($order_id)
	{
        $deliveryDate = [];
        $record = $this->deliverydateFactory->create();
        $result = $record->getCollection();
        $result->addFieldToFilter('order_id', $order_id);

        if($result->count()) {
            foreach($result as $row) {
                $deliveryDate = [
                    'comment' => $row->getComment(),
                    'date' => $row->getDate(),
                    'time' => $row->getTime(),
                ];
            }
        }
	    
	    return $deliveryDate;
	}
}