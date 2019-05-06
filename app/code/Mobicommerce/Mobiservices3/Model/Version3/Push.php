<?php
namespace Mobicommerce\Mobiservices3\Model\Version3;

class Push extends \Mobicommerce\Mobiservices3\Model\AbstractModel {

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    protected $customerSession;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Devicetokens\CollectionFactory
     */
    protected $mobiadmin3ResourceDevicetokensCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\DevicetokensFactory
     */
    protected $mobiadmin3DevicetokensFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,      
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\Session $customerSession,
        \Mobicommerce\Mobiadmin3\Model\Resource\Devicetokens\CollectionFactory $mobiadmin3ResourceDevicetokensCollectionFactory,
        \Mobicommerce\Mobiadmin3\Model\DevicetokensFactory $mobiadmin3DevicetokensFactory
    )
    {
        $this->mobiadmin3ResourceDevicetokensCollectionFactory = $mobiadmin3ResourceDevicetokensCollectionFactory;
        $this->mobiadmin3DevicetokensFactory = $mobiadmin3DevicetokensFactory;
       
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        
        parent::__construct($context, $registry, $storeManager, $eventManager);
        $this->getModel('Mobicommerce\Mobiservices3\Model\User')->autoLoginMobileUser();
    }
    
	public function saveDeviceToken($data = [])
    {
        $appcode     = isset($data['appcode']) ? $data['appcode'] : NULL;
        $platform    = isset($data['platform']) ? $data['platform'] : NULL;
        $devicetoken = isset($data['devicetoken']) ? $data['devicetoken'] : NULL;

        if(empty($appcode) || empty($platform) || empty($devicetoken)){
            return $this->errorStatus("oops");
        }
        else{
            $store = $this->storeManager->getStore()->getId();
            $collection = $this->mobiadmin3ResourceDevicetokensCollectionFactory->create()->addFieldToFilter('md_appcode', $appcode)
                ->addFieldToFilter('md_devicetype', $platform)
                ->addFieldToFilter('md_devicetoken', $devicetoken);

            if($collection->count() == 0){
                $this->mobiadmin3DevicetokensFactory->create()->setData([
                    'md_appcode'     => $appcode,
                    'md_devicetype'  => $platform,
                    'md_devicetoken' => $devicetoken,
                    'md_enable_push' => '1',
                    'md_store_id'    => $store
                    ])->save();
            }
            else{
                $sessionCustomer = $this->customerSession;
                if($sessionCustomer->isLoggedIn()){
                    $userid = (int)$sessionCustomer->getCustomer()->getId();
                    foreach($collection as $_collection){
                        $_collection->setMdUserId($userid)
                            ->setMdStoreId($store)
                            ->save();
                    }
                }
            }

            return $this->successStatus();
        }
    }

    public function updatePreference($data)
    {
        if(!empty($data['appcode']) && !empty($data['devicetoken'])){
            $collection = $this->mobiadmin3ResourceDevicetokensCollectionFactory->create()->addFieldToFilter('md_appcode', $data['appcode'])
                ->addFieldToFilter('md_devicetoken', $data['devicetoken']);

            if($collection) {
                $store = $this->storeManager->getStore()->getId();
                foreach($collection as $_collection) {
                    $_collection->setMdEnablePush($data['pushpreference'])
                        ->setMdStoreId($store)
                        ->save();
                }
            }
        }
    }
}