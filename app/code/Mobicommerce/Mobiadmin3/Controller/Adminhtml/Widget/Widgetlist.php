<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Widgetlist extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

	public function execute()
    {
		$isAjax = $this->request->getParam('isAjax');
		if($isAjax)
        {
			$layout = $this->_view->loadLayout();
			$widget_list_block = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Comman')->setTemplate('Mobicommerce_Mobiadmin3::mobiadmin3/application/edit/tab/widget/widgetlist.phtml')->toHtml();
			$response['widget_list_block'] = $widget_list_block;
			$response['status'] = 'success';
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
		}
        else
        {
			$response['error'] = 'Some thing Error';	
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
		}
	}
}
