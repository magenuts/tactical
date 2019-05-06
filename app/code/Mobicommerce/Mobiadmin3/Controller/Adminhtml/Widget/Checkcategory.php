<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Checkcategory extends \Magento\Backend\App\Action {

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
		$checkedcategory = [];
		$checked = $this->request->getParam('checked');
		$checkedcategoryid = $this->request->getParam('categoryid');
		$checkedcategorypos = $this->request->getParam('categorypos');

		if($checked == 1) {			
			$checkedcategory[$checkedcategoryid] = $checkedcategorypos;
			$categories = $this->backendSession->getData('checked_categories');	
			if(!empty($categories)){
				$categories[$checkedcategoryid] = $checkedcategorypos;
				$this->backendSession->setData('checked_categories',$categories);
			}else {
				$this->backendSession->setData('checked_categories',$checkedcategory);
			}
		} else {
			$categories = $this->backendSession->getData('checked_categories');
			if(!empty($categories)){
				unset($categories[$checkedcategoryid]);
				$this->backendSession->setData('checked_categories',$categories);
			}
		}
        
		$response = $this->backendSession->getData('checked_categories');
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
	}
}
