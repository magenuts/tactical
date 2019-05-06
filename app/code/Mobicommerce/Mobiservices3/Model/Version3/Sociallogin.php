<?php
namespace Mobicommerce\Mobiservices3\Model\Version3;

class Sociallogin extends \Mobicommerce\Mobiservices3\Model\AbstractModel {

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    protected $customerSession;
    protected $_customerModel;
    protected $_customerFactory;
    protected $_encrypt;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,      
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encrypt
    ) {
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        parent::__construct($context, $registry, $storeManager, $eventManager);

        $this->_customerModel = $customerModel;
        $this->_customerFactory = $customerFactory;
        $this->_encrypt = $encrypt;
    }
    
    public function doSocialLogin($data)
    {
        switch ($data['type']) {
            case 'facebook':
                $result = $this->doFacebookLogin($data);
                break;

            case 'google':
                $result = $this->doGoogleLogin($data);
                break;

            case 'twitter':
                $result = $this->doTwitterLogin($data);
                break;

            default:
                $result = $this->errorStatus("oops");
                break;
        }

        $result['data']['type'] = $data['type'];
        return $result;
    }

    public function doFacebookLogin($data)
    {
        $user_data = [
            'firstname' => $data['first_name'],
            'lastname'  => $data['last_name'],
            'email'     => $data['email']
            ];

        $actionAfterLogin = [];
        if(isset($data['actionAfterLogin'])) $actionAfterLogin = $data['actionAfterLogin'];

        return $this->loginProcess($user_data, $actionAfterLogin);
    }

    public function doGoogleLogin($data)
    {
        $user_data = [
            'firstname' => $data['givenName'],
            'lastname'  => $data['familyName'],
            'email'     => $data['email']
            ];

        $actionAfterLogin = [];
        if(isset($data['actionAfterLogin'])) $actionAfterLogin = $data['actionAfterLogin'];

        return $this->loginProcess($user_data, $actionAfterLogin);
    }

    public function doTwitterLogin($data)
    {
        $customer = $this->_customerModel->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('mobi_twitter_username', $data['userName'])
            ->getData();

        if($customer) {
            $actionAfterLogin = [];
            if(isset($data['actionAfterLogin'])) $actionAfterLogin = $data['actionAfterLogin'];

            return $this->loginProcess(['email' => $customer[0]['email']], $actionAfterLogin);
        }
        else {
            return $this->errorStatus("No twitter account found");
        }
    }

    function loginProcess($data, $actionAfterLogin)
    {
        $store_id = $this->storeManager->getStore()->getStoreId();
        $website_id = $this->storeManager->getStore()->getWebsiteId();

        if ($data['email']) {
            $customer = $this->getCoreModel('\Magento\Customer\Model\Customer')
                ->setWebsiteId($website_id)
                ->loadByEmail($data['email']);

            if (!$customer->getData()) {
                $customer = $this->createCustomerMultiWebsite($data, $website_id, $store_id);
            }
            // fix confirmation
            if ($customer->getConfirmation()) {
                /*
                try {
                    $customer->setConfirmation(null);
                    $customer->save();
                } catch (Exception $e) {
                    
                }
                */
            }
            
            $session = $this->_getUserSession();
            $session->setCustomerAsLoggedIn($customer);
            //$session->renewSession();
            
            $this->getModel('Mobicommerce\Mobiservices3\Model\User')->processActionAfterLogin($actionAfterLogin);

            $info = $this->successStatus();
            $info['data']['cart_details'] =$this->getModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart')->getCartInfo($data);
            $info['data']['userdata'] =$this->getModel('Mobicommerce\Mobiservices3\Model\User')->_getCustomerProfileData($customer);
        } else {
            $info = $this->errorStatus("The email field is required");
        }
        return $info;
    }

    protected function _getUserSession()
    {
        return $this->customerSession;
    }

    public function createCustomerMultiWebsite($data, $website_id, $store_id)
    {
        //$customer = $this->getCoreModel('\Magento\Customer\Model\CustomerFactory')->setId(NULL);
        $customer = $this->_customerFactory->create();
        $customer->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($data['email'])
            ->setWebsiteId($website_id)
            ->setStoreId($store_id)
            ->save();

        $password = $this->_encrypt->encrypt(uniqid());
        $customer->setPassword($password);
        try {
            $customer->save();
        } catch (Exception $e) {
            
        }
        return $customer;
    }
}