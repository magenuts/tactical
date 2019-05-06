<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.Mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\DeliverydateCore\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Mobicommerce\Deliverydate\Model\DeliverydateConfigProvider;
use \Mobicommerce\Deliverydate\Model\ResourceModel\Tinterval\Collection as TintervalCollection;
use \Mobicommerce\DeliverydateCore\Model\DeliveryDate\Validator;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Store\Model\StoreManagerInterface;
use \Mobicommerce\Deliverydate\Helper\Data as DeliveryData;

class ModifySaveShippingResponse implements ObserverInterface
{
    /**
     * @var \Mobicommerce\Deliverydate\Helper\Data
     */
    protected $deliveryHelper;    
    /**
     * @var \Mobicommerce\Deliverydate\Model\ResourceModel\Tinterval\Collection
     */
    protected $tintervalCollection;
    /**
     * @var Mobicommerce\Deliverydate\Model\DeliverydateConfigProvider
     */
    protected $dateConfigProvider;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        DeliveryData $deliveryHelper,
        \Magento\Framework\Registry $coreRegistry,
        DeliverydateConfigProvider $dateConfigProvider,
        TintervalCollection $tintervalCollection,
        Validator $validator,
        DateTime $date,
        StoreManagerInterface $storeManager
    ) {
        $this->deliveryHelper = $deliveryHelper;
        $this->coreRegistry = $coreRegistry;
        $this->dateConfigProvider = $dateConfigProvider;
        $this->tintervalCollection = $tintervalCollection;
        $this->validator = $validator;
        $this->date = $date;
        $this->storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->deliveryHelper->moduleEnabled()) 
        {   
            $responseData = $observer->getResponseData();

            $default_date = $this->dateConfigProvider->getDefaultDeliveryDate();
            $default_time = $this->dateConfigProvider->getDefaultDeliveryTime();
            
            $data =[
                "min_days"=> (int)$this->deliveryHelper->getMinDays(),
                "max_days"=> (int)$this->deliveryHelper->getMinDays(),
                "comment"=> $this->deliveryHelper->getStoreScopeValue('general/comment'),
                "date_required"=> (int)$this->deliveryHelper->getStoreScopeValue('date_field/required'),
                "date_default"=> $default_date ? $default_date : "0" ,
                "date_note"=> $this->deliveryHelper->getStoreScopeValue('date_field/note'),
                "time_enabled"=> (int)$this->deliveryHelper->getStoreScopeValue('time_field/enabled_time'),
                "time_required"=> (int)$this->deliveryHelper->getStoreScopeValue('time_field/required'),
                "time_default" => $default_time ? $default_time :"0",
                "time_note"=> $this->deliveryHelper->getStoreScopeValue('time_field/note'),                
                "time_offset"=> (int)$this->deliveryHelper->getStoreScopeValue('time_field/offset_disabled'),
                "comment_enabled"=> (int)$this->deliveryHelper->getStoreScopeValue('comment_field/enabled_comment'),
                "comment_required"=> (int)$this->deliveryHelper->getStoreScopeValue('comment_field/required'),
                "comment_note"=>(int)$this->deliveryHelper->getStoreScopeValue('comment_field/note'),
                "enabled"=> (int)$this->deliveryHelper->getStoreScopeValue('general/enabled')
            ];

            $start = $month = strtotime($this->date->gmtDate("Y-m-d"));
            $end = strtotime("+1 month", $start);

            $delivery_dates = [];
            $tmpI = 0;

            $max_days = $data['max_days'] ? $data['max_days'] : 15;
            
            $disabl_days = $this->dateConfigProvider->getDisabledDays();

            while($month < $end && $tmpI < $max_days )
            {
                $curr_date = date('Y-m-d', $month);
                 
                if($this->validator->validate($curr_date,$disabl_days))
                {
                    $delivery_dates[] = $curr_date;
                    $tmpI++;
                }  

                $month = strtotime("+1 day", $month);                
            }

            $data["dates"] = $delivery_dates;

            if ($this->deliveryHelper->getStoreScopeValue('time_field/enabled_time')) 
            {
                
                $currentStoreId = $this->storeManager->getStore()->getId();
                $this->tintervalCollection
                    ->addFieldToFilter("store_ids",[
                            array('finset'=> array("0")),
                            array('finset'=> array($currentStoreId))
                        ])
                    ->getSelect()
                    ->order('sorting_order');
                

                $data["times"] = [];

                foreach ($this->tintervalCollection as $time) 
                {        
                    $time_data = $time->getData();  
                    
                    $time_data["time_from_int"] = $this->_convertTimeToSec($time_data['time_from']);
                    $time_data["time_to_int"] = $this->_convertTimeToSec($time_data['time_to']);

                    $data["times"][] =$time_data;
                }

            }
            $responseData['data']["delivery_timeslots"] = $data;
            
            $observer->setResponseData($responseData);
        }
    }

    protected function _convertTimeToSec($time)
    {
        $time = explode(' ', $time);
        $AM_PM = isset($time[1]) ? $time[1] : "";
        
        $str_time = $time[0];

        sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
                
        if(strtolower($AM_PM) == 'pm' && $hours != 12){
            $str_time += 12;
        }

        $time_seconds = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;

        return $time_seconds;
    }
}
