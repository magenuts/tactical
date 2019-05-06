<?php
namespace Mobicommerce\Mobiadmin3\Model;

class Observer {

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Licence\CollectionFactory
     */
    protected $mobiadmin3ResourceLicenceCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Mobicommerce\Mobiadmin3\Helper\Data
     */
    protected $mobiadmin3Helper;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\LicenceFactory
     */
    protected $mobiadmin3LicenceFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory
     */
    protected $mobiadmin3ResourceAppsettingCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\AppsettingFactory
     */
    protected $mobiadmin3AppsettingFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Widget\CollectionFactory
     */
    protected $mobiadmin3ResourceWidgetCollectionFactory;

    public function __construct(
        \Mobicommerce\Mobiadmin3\Model\Resource\Licence\CollectionFactory $mobiadmin3ResourceLicenceCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Mobicommerce\Mobiadmin3\Helper\Data $mobiadmin3Helper,
        \Mobicommerce\Mobiadmin3\Model\LicenceFactory $mobiadmin3LicenceFactory,
        \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory $mobiadmin3ResourceAppsettingCollectionFactory,
        \Mobicommerce\Mobiadmin3\Model\AppsettingFactory $mobiadmin3AppsettingFactory,
        \Mobicommerce\Mobiadmin3\Model\Resource\Widget\CollectionFactory $mobiadmin3ResourceWidgetCollectionFactory
    ) {
        $this->mobiadmin3ResourceLicenceCollectionFactory = $mobiadmin3ResourceLicenceCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->mobiadmin3Helper = $mobiadmin3Helper;
        $this->mobiadmin3LicenceFactory = $mobiadmin3LicenceFactory;
        $this->mobiadmin3ResourceAppsettingCollectionFactory = $mobiadmin3ResourceAppsettingCollectionFactory;
        $this->mobiadmin3AppsettingFactory = $mobiadmin3AppsettingFactory;
        $this->mobiadmin3ResourceWidgetCollectionFactory = $mobiadmin3ResourceWidgetCollectionFactory;
    }

	public function sales_convert_quote_to_order(\Magento\Framework\Event\Observer $observer)
	{
		/*
		$platform = Mage::app()->getRequest()->getParam('platform');
		$collection = Mage::getModel('sales/order')->getCollection();
		if($collection->getSize() > 0){
			$firstOrderCollection = $collection->getFirstItem()->getData();
			if(array_key_exists('orderfromplatform', $firstOrderCollection)){
				if($platform){
					$observer->getEvent()->getOrder()->setOrderfromplatform($platform);
				}else{
					$observer->getEvent()->getOrder()->setOrderfromplatform('');
				}
			}
		}
		*/
	}

    /**
	 * When adding new store, it will insert settings fro banners, cms and product slider
	 * When updating store info, it will delete existing app and re insert new details
     */
    public function __addAppsForStore($store)
    {
    	if(!empty($store)){
    		/* for cms */
    		$settings = ["cms_settings"];
    		foreach($settings as $_setting){
	    		$collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create()->addFieldToFilter('setting_code', $_setting);

				$collection->getSelect()->group('app_code');
				if($collection->getSize()){
					foreach($collection as $_collection){
						$data = $_collection->getData();
						$this->mobiadmin3AppsettingFactory->create()->setData([
							'app_code'     => $data['app_code'],
							'storeid'      => $store,
							'setting_code' => $data['setting_code'],
							'value'        => $data['value'],
							])->save();
					}
				}
			}
			/* for cms - upto here */
    	}
    }

    /**
	 * When deleting store view, all records for that store view will be deleted from following 2 tables
	 * mobicommerce_applications_settings
	 * mobi_app_widgets
     */
    public function __deleteAppsForStore($store)
    {
    	if(!empty($store)){
    		/* for cms */
    		$settings = ["cms_settings", "homepage_categories"];
    		foreach($settings as $_setting){
	    		$collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create()->addFieldToFilter('setting_code', $_setting)
					->addFieldToFilter('storeid', $store);
				if($collection->getSize()){
					foreach($collection as $_collection){
						$_collection->delete();
					}
				}
			}
			/* for cms - upto here */

			/* for widgets */
			$collection = $this->mobiadmin3ResourceWidgetCollectionFactory->create()->addFieldToFilter('widget_store_id', $store);
				
			if($collection->getSize()){
				foreach($collection as $_collection){
					$_collection->delete();
				}
			}
			/* for widgets - upto here */
    	}
    }
}
?>