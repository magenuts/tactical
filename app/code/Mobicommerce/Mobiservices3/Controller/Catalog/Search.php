<?php
namespace Mobicommerce\Mobiservices3\Controller\Catalog;

class Search extends \Mobicommerce\Mobiservices3\Controller\Action\Action {

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request
    ) {
        parent::__construct($context, $request);
    }   

    public function execute()
    {
        $this->_eventManager->dispatch(
            'mobicommerce_webservice_controller_catalog_search_listing_before',
            ['controller' => $this, 'request' => $this->request]
        );     
        
        $data = $this->getData();
       
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connector = $objectManager->create('\Mobicommerce\Mobiservices3\Block\Connector');
        $model = $objectManager->create($connector->_getConnectorModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog'));
        
        $info = $model->productList($data);

        $this->_eventManager->dispatch(
            'mobicommerce_webservice_controller_catalog_search_listing_after',
            ['controller' => $this, 'response_data' => &$info]
        );
        
        $this->printResult($info);
    }
}