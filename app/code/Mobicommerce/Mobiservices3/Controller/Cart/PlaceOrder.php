<?php
namespace Mobicommerce\Mobiservices3\Controller\Cart;

class PlaceOrder extends \Mobicommerce\Mobiservices3\Controller\Action\Action {

    protected $checkoutSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context, $request);
        $this->checkoutSession = $checkoutSession;
    }

    public function execute()
    {
        $this->_eventManager->dispatch(
            'mobicommerce_service_controller_cart_placeorder_before',
            ['controller' => $this, 'request' => $this->request]
        );

        $delivery_data = $this->checkoutSession->getMobideliveryDateData();

        $data = $this->getData();
	    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connector = $objectManager->create('\Mobicommerce\Mobiservices3\Block\Connector');
        $model = $objectManager->create($connector->_getConnectorModel('Mobicommerce\Mobiservices3\Model\Shoppingcart\Cart'));
       
        $data = $this->getData();
        $saveOrder = $model->validateOrder($data);
        if ($saveOrder) {
            $this->printResult($saveOrder);
            exit;
        }
             
        $info = $model->saveOrder($data);
        
        $this->_eventManager->dispatch(
            'mobicommerce_service_controller_cart_placeorder_after',
            ['controller' => $this, 'response_data' => $info, 'deliverydata' => $delivery_data]
        );
        
        $this->printResult($info);
    }
}