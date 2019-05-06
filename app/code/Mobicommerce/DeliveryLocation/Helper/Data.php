<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.Mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\DeliveryLocation\Helper;

use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\Session\Storage;
use \Magento\Store\Model\Store;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Store\Model\ScopeInterface;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_MODULE_ENABLE = "general/enable";
    const CONFIG_ADMIN_VALIDATION_ENABLE = "general/admin_validation_enable";
    const CONFIG_COUNTRY_ENABLE = "validate/country_enable";
    const CONFIG_COUNTRY = "validate/country";
    const CONFIG_STATE_ENABLE = "validate/state_enable";
    const CONFIG_STATE = "validate/state";
    const CONFIG_CITY_ENABLE = "validate/city_enable";
    const CONFIG_CITY = "validate/city";
    const CONFIG_ZIPCODE_ENABLE = "validate/zipcode_enable";    
    const CONFIG_ZIPCODE = "validate/zipcode";
    const CONFIG_ADV_VALIDATION_ENABLE = "validate/adv_validation_enable";    
    const CONFIG_ADV_VALIDATION = "validate/adv_validation";
    const CONFIG_ERROR_MESSAGE = "validate/errormessage";
    /**
     * Get config value for Store
     *
     * @param string  $path
     * @param null|string|bool|int|Store $store
     *
     * @return mixed
     */
    public function getStoreScopeValue($path,$explode = false, $store = null)
    {
        $data = $this->scopeConfig->getValue(
            'mobi_deliverylocation/' . $path,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        if($explode)
        {
            $data = explode(",", $data);
        }

        return $data;
    }

    /**
     * @return bool
     */
    public function isModuleEnable()
    {
        return (bool)$this->getStoreScopeValue(Self::CONFIG_MODULE_ENABLE);
    }
    
    /**
     * @return bool
     */
    public function isAdminValidationEnable()
    {
        return (bool)$this->getStoreScopeValue(Self::CONFIG_ADMIN_VALIDATION_ENABLE);
    }
    /**
     * @return bool
     */
    public function isCountryEnable()
    {
        return (bool)$this->getStoreScopeValue(Self::CONFIG_COUNTRY_ENABLE);
    }
    /**
     * @return array
     */
    public function getCountries()
    {
        return $this->getStoreScopeValue(Self::CONFIG_COUNTRY,true);
    }
    /**
     * @return bool
     */
    public function isStateEnable()
    {
        return (bool)$this->getStoreScopeValue(Self::CONFIG_STATE_ENABLE);
    }
    /**
     * @return array
     */
    public function getStates()
    {
        return $this->getStoreScopeValue(Self::CONFIG_STATE,true);
    }
    /**
     * @return bool
     */
    public function isCityEnable()
    {
        return (bool)$this->getStoreScopeValue(Self::CONFIG_CITY_ENABLE);
    }
    /**
     * @return array
     */
    public function getCities()
    {
        return $this->getStoreScopeValue(Self::CONFIG_CITY,true);
    }
    /**
     * @return bool
     */
    public function isZipcodeEnable()
    {
        return (bool)$this->getStoreScopeValue(Self::CONFIG_ZIPCODE_ENABLE);
    }
    /**
     * @return array
     */
    public function getZipcodes()
    {
        return $this->getStoreScopeValue(Self::CONFIG_ZIPCODE,true);
    }
    /**
     * @return bool
     */
    public function isAdvanceValidationEnable()
    {
        return (bool)$this->getStoreScopeValue(Self::CONFIG_ADV_VALIDATION_ENABLE);
    }
    /**
     * @return array
     */
    public function getAdvanceValidations()
    {
        return $this->getStoreScopeValue(Self::CONFIG_ADV_VALIDATION,true);
    }
    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->getStoreScopeValue(Self::CONFIG_ERROR_MESSAGE);
    }
    /**
     * @return array
     */
    public function getConvertedAddress($address=[])
    {
        $address_data = [];
        if(isset($address["countryId"]))
        {
            $address_data["country"] = $address["countryId"];  
        }
        else
        {
            $address_data["country"] =$address["country_id"];
        }
        
        if(isset($address["region_id"]))
        {
            $region_id = $address['region_id'] ;
        }
        else
        {
            $region_id = isset($address['regionId']) ? $address['regionId'] : '';
        }    

        if($region_id!="" && $address['region'] == "")
        {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $region = $objectManager->create('Magento\Directory\Model\Region')
                        ->load($region_id);

            $address_data["state"] = $region->getName();
        }
        else
        {
            $address_data["state"] = isset($address["region"]) ? $address['region'] : "";  
        }
        
        $address_data["city"] = isset($address["city"]) ? $address['city'] : "";
        $address_data["zip"] = isset($address["postcode"]) ? $address['postcode'] : "";

        return $address_data;
    }
        
    /**
     * @return bool
     */
    public function validateAddress($address=[])
    {
        $flag = true;

        if($this->isModuleEnable())
        {
            $flag = false;

            $address_data = $this->getConvertedAddress($address);
            $country = $address_data["country"];
            $state = $address_data["state"];
            $city = $address_data["city"];
            $zip = $address_data["zip"];
            
            $countries = $this->getCountries();
            $states = $this->getStates();
            $cities = $this->getCities();
            $zipcodes = $this->getZipcodes();

            if(($this->isCountryEnable() && count($countries) && in_array($country, $countries)) ||
                ($this->isStateEnable() && count($states) && in_array($state, $states)) ||
                ($this->isCityEnable() && count($cities) && in_array($city, $cities)) ||
                ($this->isZipcodeEnable() && count($zipcodes) && in_array($zip, $zipcodes))
            )
            {
                $flag = true;
            }

            if($this->isAdvanceValidationEnable() && $flag)
            {
                $curr_address_queue = "{$country},{$state},{$city}";

                $adv_validations = explode("\n",$this->getAdvanceValidations());

                foreach ($adv_validations as $address_queue) 
                {
                    if($curr_address_queue == $address_queue)
                    {
                        $flag = false;
                        break;
                    }
                }
            }
        }

        return $flag;
    }

}
