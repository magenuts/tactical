<?php
namespace Mobicommerce\Mobiservices3\Controller\Cart;

class Index extends \Mobicommerce\Mobiservices3\Controller\Action\Action {

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request
    ) {
        parent::__construct($context, $request);
    }

    public function execute()
    {
        $this->_eventManager->dispatch(
            'mobicommerce_service_controller_checkout_cart_before',
            ['controller' => $this, 'request' => $this->request]
        );

        $data = $this->getData();
	    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connector = $objectManager->create('\Mobicommerce\Mobiservices3\Block\Connector');
        $model = $objectManager->create($connector->_getConnectorModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart'));
        $info = $model->getCartDetails($data);
        
        $this->_eventManager->dispatch(
            'mobicommerce_webservice_controller_checkout_cart_after',
            ['controller' => $this, 'response_data' => &$info]
        );

        $this->printResult($info);
    }
}