<?php
namespace Mobicommerce\Mobiadmin3\Model;

class Widget extends \Magento\Framework\Model\AbstractModel {
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        $this->_init('Mobicommerce\Mobiadmin3\Model\Resource\Widget');
    }
}