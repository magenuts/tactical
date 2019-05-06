<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\Deliverydate\Model\ResourceModel\Tinterval;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected function _construct()
    {
        $this->_init('Mobicommerce\Deliverydate\Model\Tinterval', 'Mobicommerce\Deliverydate\Model\ResourceModel\Tinterval');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ){
        $this->storeManager = $storeManager;
        return parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    public function toOptionArray()
    {
        $options = [];

        $currentStoreId = $this->storeManager->getStore()->getId();
        $this->getSelect()
            ->order('sorting_order');

        foreach ($this as $item) {
            $storeIds = trim($item->getData('store_ids'), ',');
            $storeIds = explode(',', $storeIds);
            if (!in_array($currentStoreId, $storeIds) && !in_array(0, $storeIds)) {
                continue;
            }
            $option = [
                'label' => $item->getTimeFrom() . ' - ' . $item->getTimeTo(),
                'value' => $item->getId()
            ];
            $options[] = $option;
        }

        return $options;
    }

    public function getOlderThan($start)
    {
        $this->getSelect()
            ->where('dd.date <> \'0000-00-00\'')
            ->where('dd.date <> \'1970-01-01\'')
            ->where('dd.date >= ?', $start)
            ->where('dd.active = 1');
        return $this;
    }
}
