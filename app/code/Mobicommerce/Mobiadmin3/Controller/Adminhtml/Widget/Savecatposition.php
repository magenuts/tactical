<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Savecatposition extends \Magento\Backend\App\Action {

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
		$categories = $this->backendSession->getData('checked_categories');
		$categorypos = $this->request->getParam('categorypos');
		$categoryid = $this->request->getParam('categoryid');
		if(array_key_exists($categoryid,$categories)){
			unset($categories[$categoryid]);
			$categories[$categoryid] = $categorypos;			
			$this->backendSession->setData('checked_categories',$categories);
			$response = $this->backendSession->getData('checked_categories');
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
		}
	}
}
