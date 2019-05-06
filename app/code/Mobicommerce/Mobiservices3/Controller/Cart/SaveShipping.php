<?php
namespace Mobicommerce\Mobiservices3\Controller\Cart;

/**
* added by yash
* coz in mobile app checkoutflow is modified.
* added on 2018-01-09
*/
class SaveShipping extends \Mobicommerce\Mobiservices3\Controller\Action\Action {

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request
    ) {
        parent::__construct($context, $request);
    }

    public function execute()
    {
        $this->_eventManager->dispatch(
            'mobicommerce_service_controller_cart_saveshipping_before',
            ['controller' => $this, 'request' => $this->request]
        );

        $data = $this->getData();
	    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connector = $objectManager->create('\Mobicommerce\Mobiservices3\Block\Connector');
        $model = $objectManager->create($connector->_getConnectorModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart'));
        $info = $model->saveShipping($data);
        
        $this->_eventManager->dispatch(
            'mobicommerce_webservice_controller_cart_saveshipping_after',
            ['controller' => $this, 'response_data' => &$info]
        );
        
        $this->printResult($info);
    }
}