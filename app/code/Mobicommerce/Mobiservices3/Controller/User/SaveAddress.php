<?php
namespace Mobicommerce\Mobiservices3\Controller\User;

class SaveAddress extends \Mobicommerce\Mobiservices3\Controller\Action\Action {

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request
    ) {
        parent::__construct($context,$request);
    }

    public function execute()
    {
        $data = $this->getData();
        
        $addressArray = [];
        $addressArray['id']="";
        $addressArray['firstname']="irshad";
        $addressArray['lastname']="ansari";
        $addressArray['street']="new add";
        $addressArray['city']="abad";
        $addressArray['postcode']="123456";
        $addressArray['country_id']="IN";
        $addressArray['telephone']="1234455678";
        
        $data = array_merge($data, $addressArray);
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connector = $objectManager->create('\Mobicommerce\Mobiservices3\Block\Connector');
        $model = $objectManager->create($connector->_getConnectorModel('Mobicommerce\Mobiservices3\Model\User'));
        $info = $model->saveCustomerAddress($data);
        $this->printResult($info);
    }
}