<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Checkproduct extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    protected $resultJsonFactory;
    protected $backendSession;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->request = $request;
        $this->registry = $registry;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->backendSession = $backendSession;
        parent::__construct($context);
    }

	public function execute()
    {
		$checkedproduct = [];
		$checked = $this->request->getParam('checked');
		$checkedproductid = $this->request->getParam('productid');
		$checkedproductpos = $this->request->getParam('prod_position');
		if($checked == 1) {			
			$checkedproduct[$checkedproductid] = $checkedproductpos;
			$products = $this->backendSession->getData('checked_products');	
			if(!empty($products)){
				$products[$checkedproductid] = $checkedproductpos;
				$this->backendSession->setData('checked_products',$products);
			}else {
				$this->backendSession->setData('checked_products',$checkedproduct);
			}
		} else {
			$products = $this->backendSession->getData('checked_products');
			if(!empty($products)){
				unset($products[$checkedproductid]);
				$this->backendSession->setData('checked_products',$products);
			}
		}
		$response = $this->backendSession->getData('checked_products');		
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
	}
}
