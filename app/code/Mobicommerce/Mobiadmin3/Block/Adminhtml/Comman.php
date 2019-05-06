<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml;

class Comman extends \Magento\Framework\View\Element\Template {
    
    protected $registry;
    protected $storeManager;

	public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
	{
        $this->registry = $registry;
        $this->storeManager = $storeManager;
		parent::__construct($context);
	}

    public function getWidgetData()
    {
        return $widgetdata = $this->registry->registry('widgetdata');
    }

    public function getStoreMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}