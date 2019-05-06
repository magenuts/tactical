<?php
namespace Mobicommerce\Mobiservices3\Controller\Pushservice;

class Removeapps extends \Mobicommerce\Mobiservices3\Controller\Action\Action {

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request
    ) {
        parent::__construct($context, $request);
    }

    public function execute()
    {
        $data = $this->getData();
	    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connector = $objectManager->create('\Mobicommerce\Mobiservices3\Block\Connector');
        $model = $objectManager->create('Mobicommerce\Mobiservices3\Model\Pushservice');
        $info = $model->removeapps($data);
        $this->printResult($info);
    }
}