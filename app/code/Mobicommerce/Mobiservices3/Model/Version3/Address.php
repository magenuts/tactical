<?php
namespace Mobicommerce\Mobiservices3\Model\Version3;

class Address extends \Mobicommerce\Mobiservices3\Model\AbstractModel {
    
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
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,      
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
        )
    {
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->addressRepository = $addressRepository;
        parent::__construct($context, $registry, $storeManager, $eventManager);
    }

    public function deleteAddress($data)
    {
        $addressId = isset($data['id']) ? $data['id'] : false;

        if ($addressId) {
            $address = $this->getCoreModel('Magento\Customer\Model\Address')->load($addressId);
            // Validate address_id <=> customer_id
            if ($address->getCustomerId() != $this->_getSession()->getCustomer()->getId()) {
                return $this->errorStatus(__('The address does not belong to this customer.'));
            }
            
            try {
                //$address->delete();
                $this->addressRepository->deleteById($addressId);
                $info = $this->successStatus(__('The address has been deleted.'));
                $info['data']['cart_details'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart')->getCartInfo();
                $info['data']['userdata'] = $this->getModel('Mobicommerce\Mobiservices3\Model\User')->getCustomerData();
                return $info;
            } catch (Magento\Framework\Exception $e){
                return $this->errorStatus(__('An error occurred while deleting the address.'));
            }
        }
        return $this->errorStatus(__('An error occurred while deleting the address.'));
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

    public function saveAddress($data)
    {
        if (!empty($data)) {
            $customer = $this->_getSession()->getCustomer();
            /* @var $address Mage_Customer_Model_Address */
            $address  = $this->getCoreModel('Magento\Customer\Model\AddressFactory')->create();
            $addressId = isset($data['id']) ? $data['id'] : null;
            if ($addressId) {
                $existsAddress = $customer->getAddressById($addressId);
                if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId())
                {
                    $address->setId($existsAddress->getId());
                }
            }

            $errors = [];

            /* @var $addressForm Mage_Customer_Model_Form */
            $addressForm =$this->getCoreModel('Magento\Customer\Model\Form');
           
            $addressForm->setFormCode('customer_address_edit')
                ->setEntity($address);
            $addressData = $data;
            $addressErrors = $addressForm->validateData($addressData);
            
            if ($addressErrors !== true) {
                $errors = $addressErrors;
            }

            try {
                $addressForm->compactData($addressData);
                $address->setCustomerId($customer->getId())
                    ->setIsDefaultBilling(isset($data['default_billing'][0]) ? $data['default_billing'][0] : false)
                    ->setIsDefaultShipping(isset($data['default_shipping'][0]) ? $data['default_shipping'][0] : false);

                $addressErrors = $address->validate();
                if ($addressErrors !== true) {
                    $errors = array_merge($errors, $addressErrors);
                }
                
                if (count($errors) === 0) {
                    $address->save();
                    if(isset($data['default_billing'][0]) && $data['default_billing'][0] == '1') {
                        $customer->setDefaultBilling($address->getId())->save();
                    }
                    if(isset($data['default_shipping'][0]) && $data['default_shipping'][0] == '1') {
                        $customer->setDefaultShipping($address->getId())->save();
                    }

                    $info = $this->successStatus(__('The address has been saved.'));
                    $info['data']['cart_details'] = $this->getModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart')->getCartInfo();
                    $info['data']['userdata'] =$this->getModel('Mobicommerce\Mobiservices3\Model\User')->getCustomerData();
                    $info['data']['address_id'] = $address->getId();
                    return $info;
                } else {
                    return $this->errorStatus($errors);
                }
            } catch (Mage_Core_Exception $e) {
                return $this->errorStatus($e->getMessage());
            } catch (Exception $e) {
                return $this->errorStatus(__('Cannot save address.'));
            }
        }
        return $this->errorStatus(__('Cannot save address.'));
    }
}