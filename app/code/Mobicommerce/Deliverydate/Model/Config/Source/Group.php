<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\Deliverydate\Model\Config\Source;

class Group implements \Magento\Framework\Option\ArrayInterface
{

    protected $_options;
    protected $_groupFactory;

    public function __construct(\Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupFactory)
    {
        $this->_groupFactory = $groupFactory;
    }

    public function toOptionArray()
    {
        if (!$this->_options) {
            /** @var $stores \Magento\Customer\Model\ResourceModel\Group\Collection */
            $stores = $this->_groupFactory->create();
            $this->_options = $stores->load()->toOptionArray();
        }
        return $this->_options;
    }
}
