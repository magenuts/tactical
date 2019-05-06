<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Categorygrid extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\WidgetFactory
     */
    protected $mobiadmin3WidgetFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Mobicommerce\Mobiadmin3\Model\WidgetFactory $mobiadmin3WidgetFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->request = $request;
        $this->mobiadmin3WidgetFactory = $mobiadmin3WidgetFactory;
        $this->registry = $registry;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }
  
	public function execute()
    {
		$this->registerData('categorydata');
        //$layout = $this->_view->loadLayout();
		$widget_category_grid = $this->_view->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Grid\Widget\Category')->toHtml();
		$response['widget_category_grid'] = $widget_category_grid;
		$response['status'] = 'success';
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
	}
    
    public function registerData($variable)
	{
		$widget_id = $this->request->getParam('widget_id');
        if($widget_id){
			$widgetdata = $this->mobiadmin3WidgetFactory->create()->load((int) $widget_id);
			if ($widgetdata->getWidgetId()) {
				$this->registry->register($variable, $widgetdata->getData());
			}
		}
        else {
            $this->registry->register($variable, []);
        }
	}
}
