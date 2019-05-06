<?php
namespace Mobicommerce\Mobiservices3\Model\Version3;
use \Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;

class User extends \Mobicommerce\Mobiservices3\Model\AbstractModel {
  
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
     * @var 
     */
    protected $customerFactory;
    protected $customerRepository;
    protected $encrypt;
    protected $urlBuilder;
     
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,      
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Mobicommerce\Mobiadmin3\Model\Resource\Devicetokens\CollectionFactory $mobiadmin3ResourceDevicetokensCollectionFactory,
        EncryptorInterface $encrypt,
        \Magento\Framework\UrlInterface $urlBuilder
    )
    {
        $this->customerFactory = $customerFactory;
        $this->mobiadmin3ResourceDevicetokensCollectionFactory = $mobiadmin3ResourceDevicetokensCollectionFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->encrypt = $encrypt;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $registry, $storeManager, $eventManager);
    }

    public function getInfo($data)
    {
        $is_logged_in = $this->customerSession->isLoggedIn();
        if($is_logged_in) {
            $user = $this->getCustomerData();
            $info = $this->successStatus();
            $info['data']['userdata'] = $user;
            return $info;
        }
        else {
            return $this->errorStatus();
        }
    }

	public function signIn($data)
    {
        $userInfo = []; 
        try {
	        $customer = $this->customerFactory->create();
            $customer->setWebsiteId($this->_getWebsiteId());
            
	        if ($customer->authenticate($data['username'], $data['password'])) {
	            $this->_getUserSession()->setCustomerAsLoggedIn($customer);
                $this->_loginFromMobile($data, $customer->getId());
	        }

	        $_customer = $this->_getUserSession()->getCustomer();

			$info = $this->successStatus();
            $info['data']['cart_details'] =$this->getModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart')->getCartInfo();
            $info['data']['userdata'] = $this->_getCustomerProfileData($_customer);
            $info['data']['wishlist'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Wishlist\Wishlist')->getWishlistInfo();
	        return $info;

        } catch (AuthenticationException $e) {
        	$this->logout();
            return $this->errorStatus($e->getMessage());
        }    	
    }

    public function _getCustomerProfileData($_customer)
    {
        $data['autologinid'] = $this->encrypt->encrypt($_customer->getId());
        $data['customer_id'] = $_customer->getId();
        $data['email']       = $_customer->getEmail();
        $data['firstname']   = $_customer->getFirstname();
        $data['lastname']    = $_customer->getLastname();
        $data['fullname']    = $_customer->getName();
        $data['addresses']   = $this->getAddresses($_customer);

        try {
            $data['gender'] = $_customer->getGender();
            $data['dob'] = $_customer->getDob();
            $data['taxvat'] = $_customer->getTaxvat();
        }
        catch(Exception $e) {

        }

        return $data;
    }

    public function getCustomerData()
    {
        $customer = $this->_getUserSession()->getCustomer();
        return $this->_getCustomerProfileData($customer);
    }

    public function getAddresses($customer)
    {
        $primary_billing = $customer->getPrimaryBillingAddress();
        $primary_shipping = $customer->getPrimaryShippingAddress();

        $_addresses = [];
        $addresses = $customer->getAddresses();
        foreach ($addresses as $address) {      
            $_address = $this->_formatAddress($address);
            if($_address){
                if($primary_billing && $_address['id'] == $primary_billing->getID())
                    $_address['default_billing'] = '1';

                if($primary_shipping && $_address['id'] == $primary_shipping->getID())
                    $_address['default_shipping'] = '1';

                $_addresses[] = $_address;
            }
        }
        return $_addresses;
    }

	public function _formatAddress($address)
    {
		if(!$address) return [];

        return [
            'id'               => $address->getId(),
            'firstname'        => $address->getFirstname(),
            'lastname'         => $address->getLastname(),
            'fullname'         => $address->getName(),
            'street'           => $address->getStreet(),
            'city'             => $address->getCity(),
            'region'           => $address->getRegion(),
            'region_id'        => $address->getRegionId(),
            'state_code'       => $address->getRegionCode(),
            'postcode'         => $address->getPostcode(),
            'country'          => $address->getCountryModel()->loadByCode($address->getCountry())->getName(),
            'country_id'       => $address->getCountryId(),
            'country_code'     => $address->getCountry(),
            'company'          => $address->getCompany(),
            'telephone'        => $address->getTelephone(),
            'fax'              => $address->getFax(),
            'prefix'           => $address->getPrefix(),
            'middlename'       => $address->getMiddleName(),
        ];		
	}

    public function logout()
    {
        try {
            $_REQUEST['autologinid'] = null;
            $this->_getUserSession()->logout()->setBeforeAuthUrl($this->urlBuilder->getUrl());
            $info = $this->successStatus();
            $info['data']['cart_details'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart')->getCartInfo();
            return $info;
        } catch (Exception $e) {
            return $this->errorStatus($e->getMessage());
        }
    }

    public function signUp($data)
    {
        $message = [];
        if($data['firstname']=="") return $this->errorStatus(["firstname_required_error"]);
        if($data['lastname']=="") return $this->errorStatus(["lastname_required_error"]);
        if($data['email']=="") return $this->errorStatus(["email_required_error"]);
        if($data['password']=="") return $this->errorStatus(["password_required_error"]);
        
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($this->storeManager->getWebsite()->getId());
        $customer->loadByEmail($data['email']);

        if ($customer->getId()) {
            $message[] =__('account_already_exists');
            return $this->errorStatus($message);
        } else {
	        $customer->setFirstname($data['firstname']);
	        $customer->setLastname($data['lastname']);
	        $customer->setEmail($data['email']);	        
	        $customer->setPassword($data['password']);

            if(isset($data['twitter_username']) && !empty($data['twitter_username']))
            {
                $customer->setMobiTwitterUsername($data['twitter_username']);
            }

            if(isset($data['dob'])) {
                if(!empty($data['dob'])) {
                    $data['dob'] = date('Y-m-d', strtotime($data['dob']));
                }

                $customer->setDob($data['dob']);
            }

            if(isset($data['taxvat'])) {
                $customer->setTaxvat($data['taxvat']);
            }

            if(isset($data['gender'])) {
                $customer->setGender($data['gender']);
            }

            if(isset($data['customAttributes']) && !empty($data['customAttributes'])){
                foreach($data['customAttributes'] as $key => $value){
                    $customer->setData($key, $value);
                }
            }
        }
        try {
            $customer->save();
            $customer->setConfirmation(null);
            $customer->save();
            $this->_getUserSession()->loginById($customer->getId());
			if($this->checkUserLoginSession()){
				$_customer = $this->_getUserSession()->getCustomer();

                /* added by Yash for sending welcome email to user */
                $session = $this->_getUserSession();
                if ($_customer->isConfirmationRequired()) {
                    /** @var $app Mage_Core_Model_App */
                    $app = $this->_getApp();
                    /** @var $store  Mage_Core_Model_Store*/
                    $store = $app->getStore();
                    $_customer->sendNewAccountEmail(
                        'confirmation',
                        $session->getBeforeAuthUrl(),
                        $store->getId()
                    );
                } else {
                    $session->setCustomerAsLoggedIn($_customer);
                    //$session->renewSession();                    
                    $url = $this->_welcomeCustomer($customer);
                }
                
                $this->_loginFromMobile($data, $_customer->getId());
                /* added by Yash for sending welcome email to user - upto here */

                if(isset($data['actionAfterLogin']))
                    $this->processActionAfterLogin($data['actionAfterLogin']);

				$info = $this->successStatus();
                $info['data']['cart_details'] = array();//$this->getModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart')->getCartInfo();
               
                $info['data']['userdata'] = $this->getCustomerData();
                $info['data']['wishlist'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Wishlist\Wishlist')->getWishlistInfo();
				return $info;
			} else {
				return $this->errorStatus('User is not logged in');
			}
        } catch (InputException $e) {
            return $this->errorStatus($e->getMessage());
        }
    }

    protected function _loginFromMobile($data, $customer_id)
    {
        if(isset($data['push_devicetoken']) && isset($data['appcode'])){
            $collection = $this->mobiadmin3ResourceDevicetokensCollectionFactory->create()->addFieldToFilter('md_appcode', $data['appcode'])
                ->addFieldToFilter('md_devicetoken', $data['push_devicetoken']);
            if($collection->getSize() > 0){
                foreach($collection as $_collection){
                    $_collection->setMdUserid($customer_id)->save();
                }
            }
        }
    }

    public function saveCustomerAddress($data)
    {
        $result = true;
        $errors = false;
        $customer = $this->_getUserSession()->getCustomer();
        $address = $this->getCoreModel('Magento\Customer\Model\Address');
        $addressId = $data['id'];
        $address->setData($data);
        if ($addressId && $addressId != '') {
            $existsAddress = $customer->getAddressById($addressId);
            if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                $address->setId($existsAddress->getId());
            }
        } else {
            $address->setId(null);
        }

        $addressForm = $this->getCoreModel('Magento\Customer\Model\Form');
        $addressForm->setFormCode('customer_address_edit')
                ->setEntity($address);
                
        try {
            $addressForm->compactData($data);
            $address->setCustomerId($customer->getId());
            if($data['primary_billing']=="1") $address->setIsDefaultBilling('1');
            if($data['primary_shipping']=="1") $address->setIsDefaultShipping('1');
            $address->setCustomerId($customer->getId());
            $addressErrors = $address->validate();
            if ($addressErrors !== true) {
                $errors = true;
            }

            if (!$errors) {
                $address->save();
				$info = $this->successStatus();
				$info = array_merge($info,$this->_getCustomerProfileData($customer)); 
				return $info;
            } else {
            	return $this->errorStatus('address_save_error');
            }
        } catch (Exception $e) {
            return $this->errorStatus($e->getMessage());
        }
        return true;
    }

    public function forgetPassword($data)
    {
        $email = $data['email'];
        if (is_null($email)) {
            return $this->errorStatus('email_valid_error');
        } else {
            if (!\Zend_Validate::is($email, 'EmailAddress')) {
                return $this->errorStatus([__('email_valid_error')]);
            }
        	
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $partners = $objectManager->create('\Magento\Customer\Model\Customer');
            $customer = $partners->getCollection()->addFieldToFilter('email', $email)->getFirstItem();

            if ($customer->getId()) {
                try {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $helper = $objectManager->get('\Magento\User\Helper\Data');
                    $newResetPasswordLinkToken = $helper->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
                } catch (Exception $e) {
                    return $this->errorStatus($e->getMessage());
                }
                $info = $this->successStatus();
                $info['message'] = __('If there is an account associated with %s you will receive an email with a link to reset your password.', $email);
                return $info;
            } else {
                return $this->errorStatus(__('account_not_found_error'));
            }
        }
    }

    protected function _getUserSession()
    {
        return $this->customerSession;
    }

    /**
     * Get App
     *
     * @return Mage_Core_Model_App
     */
    protected function _getApp()
    {
        return Mage::app();
    }

    /**
     * Check whether VAT ID validation is enabled
     *
     * @param \Magento\Store\Model\Store|string|int $store
     * @return bool
     */
    protected function _isVatValidationEnabled($store = null)
    {
        try{
            return  $this->_getHelper('Magento\Customer\Helper\Address')->isVatValidationEnabled($store);
        }
        catch(Exception $e){
            return false;
        }
    }

    /**
     * Get Helper
     *
     * @param string $path
     * @return \Magento\Framework\App\Helper\AbstractHelper
     */
    protected function _getHelper($path)
    {
        return $this->getCoreHelper($path);
    }

    /**
     * Add welcome message and send new account email.
     * Returns success URL
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param bool $isJustConfirmed
     * @return string
     */
    protected function _welcomeCustomer(\Magento\Customer\Model\Customer $customer, $isJustConfirmed = false)
    {
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer//Magento\Customer\Helper\Address
            $configAddressType =  $this->_getHelper('Magento\Customer\Helper\Address')->getTaxCalculationAddressType();
            $userPrompt = '';
            switch ($configAddressType) {
                case \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING:
                    $userPrompt = __('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you shipping address for proper VAT calculation',
                        $this->_getUrl('customer/address/edit'));
                    break;
                default:
                    $userPrompt = __('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you billing address for proper VAT calculation',
                        $this->_getUrl('customer/address/edit'));
            }
            //$this->_getSession()->addSuccess($userPrompt);
        }

        try{
            $customer->sendNewAccountEmail(
                $isJustConfirmed ? 'confirmed' : 'registered',
                '',
                $this->storeManager->getStore()->getId()
            );
        }
        catch(MailException $e){
            
        }

        $successUrl = $this->_getUrl('*/*/index', ['_secure' => true]);
        if ($this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }

    /**
     * Retrieve customer session model object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
       return $this->customerSession;
    }

    public function updateProfile($data)
    {
        if (!empty($data)) {
            /** @var $customer Mage_Customer_Model_Customer */
            $customer = $this->_getSession()->getCustomer();
            
            /** @var $customerForm Mage_Customer_Model_Form */
            $customerForm = $this->getCoreModel('Magento\Customer\Model\Form');
            $customerForm->setFormCode('customer_account_edit')
                ->setEntity($customer);
            //$customerData = $customerForm->extractData($data);
            $customerData = $data;

            if(isset($customerData['dob'])) {
                if(!empty($customerData['dob'])) {
                    $customerData['dob'] = date('Y-m-d', strtotime($customerData['dob']));
                }
            }

            $errors = [];
            $customerErrors = $customerForm->validateData($customerData);
            if ($customerErrors !== true) {
                $errors = array_merge($customerErrors, $errors);
            } else {
                $customerForm->compactData($customerData);

                // Validate account and compose list of errors if any
                $customerErrors = $customer->validate();
                if (is_array($customerErrors)) {
                    $errors = array_merge($errors, $customerErrors);
                }
            }

            if (!empty($errors)) {
                $errors = implode(', ', $errors);
                return $this->errorStatus($errors);
            }

            try {
                $customer->setConfirmation(null);
                $customer->save();
                $this->_getSession()->setCustomer($customer);

                $info = $this->successStatus(__('The account information has been saved.'));
                $info['data']['cart_details'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart')->getCartInfo();
                $info['data']['userdata'] = $this->getCustomerData();
                return $info;
            } catch (InputException $e) {
                return $this->errorStatus($e->getMessage());
            } catch (InputException $e) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, __('Cannot save the customer.'));
                return $this->errorStatus(__('Cannot save the customer.'));
            }
        }
        return $this->errorStatus(__('Invalid current password'));
    }

    public function updatePassword($data)
    {
        $customer = $this->_getSession()->getCustomer();
        $currPass   = isset($data['current_password']) ? $data['current_password'] : '';
        $newPass    = isset($data['password']) ? $data['password'] : '';
        $confPass   = isset($data['confirmation']) ? $data['confirmation'] : '';

        $oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
        if (strpos($oldPass, ':')) {
            list($_salt, $salt) = explode(':', $oldPass);
        } else {
            $salt = false;
        }

        $errors = [];
        if ($customer->hashPassword($currPass, $salt) == $oldPass) {
            if($newPass === $confPass){
                if (strlen($newPass)) {
                    /**
                     * Set entered password and its confirmation - they
                     * will be validated later to match each other and be of right length
                     */
                    $customer->setPassword($newPass);
                    $customer->setConfirmation($confPass);
                    $customer->setPasswordConfirmation($confPass);
    
                } else {
                    $errors[] = __('New password field cannot be empty.');
                }
            }
            else {
                    $errors[] = __('New password and Confirm password should be same.');
                }
        } else {
            $errors[] = __('Invalid current password');
        }

        if (!empty($errors)) {
            $errors = implode(', ', $errors);
            return $this->errorStatus($errors);
        }

        try {
            $customer->setConfirmation(null);
            $customer->save();
            $this->_getSession()->setCustomer($customer);

            $info = $this->successStatus(__('Password change successfully.'));
           
            $info['data']['cart_details'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart')->getCartInfo();            
            return $info;
        } catch (LocalizedException $e) {
            return $this->errorStatus($e->getMessage());
        } catch (InputException $e) {
            $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                ->addException($e, __('Cannot save the customer.'));
            return $this->errorStatus(__('Cannot save the customer.'));
        }
    }

    public function processActionAfterLogin($data)
    {
        $result = [];
        switch($data['action']) {
            case 'addToWishlist':
                $params = [
                    'product' => $data['product_id'],
                    'qty' => $data['qty']
                    ];

                $result = $this->getModel('Mobicommerce\Mobiservices3\Model\Wishlist\Wishlist')->addWishlistItem($params);
                break;

            case 'moveToWishlist':
                $params = [
                    'item_id' => $data['item_id']
                    ];

                $result = $this->getModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart')->moveToWishlist($params);
                break;

            default:
                break;
        }

        return $result;
    }

    /**
     * Created by: Yash Shah
     * Date: 04-07-2015
     * For mobile app, if session expires then also keep user login by mobile session customer id
     */
    public function autoLoginMobileUser()
    {
        //$_REQUEST['autologinid'] = false;
        if(isset($_REQUEST['autologinid']) && !empty($_REQUEST['autologinid'])){
            $autologinid = $this->encrypt->decrypt($_REQUEST['autologinid']);
            $is_logged_in = $this->customerSession->isLoggedIn();
            if(!$is_logged_in){
                $customer = $this->getCoreModel('\Magento\Customer\Model\Customer')->load($autologinid);
                if($customer){
                    $this->_getUserSession()->setCustomerAsLoggedIn($customer);
                }
            }
            $_REQUEST['autologinid'] = false;
        }
    }
}