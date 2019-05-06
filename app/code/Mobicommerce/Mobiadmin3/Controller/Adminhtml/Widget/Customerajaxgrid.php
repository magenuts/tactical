<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Customerajaxgrid extends \Magento\Backend\App\Action {

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

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Mobicommerce\Mobiadmin3\Model\WidgetFactory $mobiadmin3WidgetFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->request = $request;
        $this->mobiadmin3WidgetFactory = $mobiadmin3WidgetFactory;
        $this->registry = $registry;
        parent::__construct($context);
    }

	public function execute()
    {
		$layout = $this->_view->loadLayout();
		$widget_customer_grid = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Grid\Widget\Customer')->toHtml();
		return $this->getResponse()->setBody($widget_customer_grid);
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
	}
}
