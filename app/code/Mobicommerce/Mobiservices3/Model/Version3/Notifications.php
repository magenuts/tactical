<?php
//namespace \Mobicommerce\Mobiservices3\Model;
namespace Mobicommerce\Mobiservices3\Model\Version3;

class Notifications extends \Mobicommerce\Mobiservices3\Model\AbstractModel {

    private $limit = 50;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    protected $customerSession;
    protected $_resource;        
    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Pushhistory\CollectionFactory
     */
    protected $mobiadmin3ResourcePushhistoryCollectionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,      
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\Session $customerSession,
        \Mobicommerce\Mobiadmin3\Model\Resource\Pushhistory\CollectionFactory $mobiadmin3ResourcePushhistoryCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->mobiadmin3ResourcePushhistoryCollectionFactory = $mobiadmin3ResourcePushhistoryCollectionFactory;
       
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;

        parent::__construct($context, $registry, $storeManager, $eventManager);
        $this->_resource = $resource;
        $this->getModel('Mobicommerce\Mobiservices3\Model\User')->autoLoginMobileUser();
    }
    
	public function getNotifications($data)
    {
        $customer_id =$this->customerSession->getCustomer()->getId();
        $appcode = $data['appcode'];
        if (isset($customer_id) && $customer_id > 0)
        {
            $store = $this->storeManager->getStore()->getId();
            $customer = $this->customerSession;
            $customer_group_id = $customer->getGroupId();            
            
            $connection = $this->_resource->getConnection();         
            $tableName = $connection->getTableName('mobicommerce_pushhistory');
           
            $pushHistory = [];
            $sql = "SELECT * FROM " . $tableName . " WHERE 
                appcode = '".$appcode."' AND store_id IN ('0', '".$store."') AND (send_to_type ='all' OR ";
            $sql .= "(send_to_type = 'specific_customer' AND FIND_IN_SET( '" . $customer_id . "', send_to)) OR ";
            $sql .= "(send_to_type = 'customer_group' AND FIND_IN_SET( '" . $customer_group_id . "', send_to))) ORDER BY `id` DESC";
            $sql .= " LIMIT " . ($this->limit);
            $collection = $connection->query($sql);
        }
        else
        {
            $store = $this->storeManager->getStore()->getId();
            $collection = $this->mobiadmin3ResourcePushhistoryCollectionFactory->create()->setOrder('id', 'DESC');

            $collection->addFieldToFilter('appcode', $appcode);
            $collection->addFieldToFilter('device_type', [
                'in' => [$data['device'], 'both']
                ]);

            
            $collection->addFieldToFilter('store_id', [
                'in' => ['0', $store]
                ]);

            $collection->addFieldToFilter('send_to_type', 'all');
            $collection->getSelect()->limit($this->limit);
        }
        
        $notifications = [];
        if ($collection) {
            foreach ($collection as $_collection) {
                $send_time = $_collection['date_submitted'];
                $_notification = [
                    'id'        => $_collection['id'],
                    'heading'   => $_collection['heading'],
                    'message'   => $_collection['message'],
                    'deeplink'  => $_collection['deeplink'],
                    'image_url' => $_collection['image'],
                    'send_time' => $send_time
                    ];
                $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Mobicommerce')->addImageRatio($_notification);
                $notifications[] = $_notification;
            }
        }

        $info = $this->successStatus();
        $info['data']['notifications'] = $notifications;
        return $info;
    }
}