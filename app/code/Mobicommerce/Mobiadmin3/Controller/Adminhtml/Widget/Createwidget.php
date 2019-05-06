<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Createwidget extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\WidgetFactory
     */
    protected $mobiadmin3WidgetFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\CategorywidgetFactory
     */
    protected $mobiadmin3CategorywidgetFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Mobicommerce\Mobiadmin3\Model\WidgetFactory $mobiadmin3WidgetFactory,
        \Mobicommerce\Mobiadmin3\Model\CategorywidgetFactory $mobiadmin3CategorywidgetFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->request = $request;
        $this->mobiadmin3WidgetFactory = $mobiadmin3WidgetFactory;
        $this->mobiadmin3CategorywidgetFactory = $mobiadmin3CategorywidgetFactory;
        $this->registry = $registry;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
		$isAjax = $this->request->getParam('isAjax');
		if($isAjax){
			$layout = $this->_view->loadLayout();
            $widget_new_block = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Comman')->setTemplate('Mobicommerce_Mobiadmin3::mobiadmin3/application/edit/tab/widget/createnewwidget.phtml')->toHtml();
            
			$response['widget_new_block'] = $widget_new_block;
			$response['status'] = 'success';
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
			return $this->getResponse()->setBody($resultJson);
		}else{
			$response['error'] = 'Some thing Error';
            $resultJson = $this->resultJsonFactory->create();
            return  $resultJson->setData($response);	
			return $this->getResponse()->setBody($resultJson);
		}
	}
}
