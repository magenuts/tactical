<?php
namespace Mobicommerce\Mobiservices3\Controller\Action;

abstract class Action extends \Magento\Framework\App\Action\Action {
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    protected $mobicommerceHelper;
    protected $jsonHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request
    ) {
        error_reporting(E_ERROR);
        parent::__construct($context);
        $this->request = $request;
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('\Mobicommerce\Mobiservices3\Helper\Mobicommerce');
        $this->mobicommerceHelper = $helper;
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $jsonHelper = $objectManager->create('\Magento\Framework\Json\Helper\Data');        
        $this->jsonHelper = $jsonHelper;
        $this->setRequestData();        
    }

    public function dataToJson($data)
    {
        $this->setData($data);
        $this->dispatchEventChangeData($this->getActionName('_return'), $data);
        $this->_data = $this->getData();
        $json = $this->jsonHelper->jsonEncode($this->_data);
        //http://stackoverflow.com/questions/10199017/how-to-solve-json-error-utf8-error-in-php-json-decode
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $json;
            case JSON_ERROR_UTF8:
                $data = $this->utf8ize($this->_data);
                return $this->dataToJson($data);
            default:
                return $json;
        }
    }

    public function utf8ize($mixed) {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->utf8ize($value);
            }
        } else if (is_string ($mixed)) {
            return utf8_encode($mixed);
        }
        return $mixed;
    }

    public function dispatchEventChangeData($event_name, $data)
    {
        
    }

    public function getActionName($last = '')
    {
        return $this->request->getFullActionName() . $last;
    }    

    public function printResult($data)
    {
        //ob_start('ob_gzhandler');
        $data['data']['version_support'] = $this->mobicommerceHelper->isMobileVersionSupported();
        if(!$data['data']['version_support']){
            $data['status']  = "FAIL";
            $data['messagecode'] = "N101";
            $data['message'] = "This is outdated version. Please upgrade app.";
        }

        $json_data = $this->dataToJson($data);
        if(isset($_GET['callback']) && $_GET['callback']!=''){
           print $_GET['callback'] ."(".$json_data.")";
        }else{
            header('content-type:application/json');
            echo $json_data;
        }
        exit;
    }

    public function setRequestData()
    {
        $this->setData($this->getRequest()->getParams());
        $this->dispatchEventChangeData($this->getActionName(), $this->_data);
        $this->_data = $this->getData();
    }

    public function getData()
    {
        return $this->_data;
    }

    public function setData($data)
    {
        $this->_data = $data;
    }

    public function execute()
    {
        
    }
}