<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Grid\Renderer;

class Store extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $val = $row->getAppStoreid();
        $out = $this->storeManager->getStore($val)->getCode();
        return $out;
    }
}