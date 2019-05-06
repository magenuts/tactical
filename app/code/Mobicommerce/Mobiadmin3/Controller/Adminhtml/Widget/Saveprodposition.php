<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Saveprodposition extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    protected $resultJsonFactory;
    protected $backendSession;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->backendSession = $backendSession;
        parent::__construct($context);
    }

	public function execute()
    {
		$products = $this->backendSession->getData('checked_products');
		$productpos = $this->request->getParam('prod_position');
		$productid = $this->request->getParam('productid');
		if(array_key_exists($productid,$products)){
			unset($products[$productid]);
			$products[$productid] = $productpos;			
			$this->backendSession->setData('checked_products',$products);
			$response = $this->backendSession->getData('checked_products');
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
		}
	}
}
