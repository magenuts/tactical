<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Categoryajaxgrid extends \Magento\Backend\App\Action {

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

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Mobicommerce\Mobiadmin3\Model\WidgetFactory $mobiadmin3WidgetFactory,
        \Mobicommerce\Mobiadmin3\Model\CategorywidgetFactory $mobiadmin3CategorywidgetFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->request = $request;
        $this->mobiadmin3WidgetFactory = $mobiadmin3WidgetFactory;
        $this->mobiadmin3CategorywidgetFactory = $mobiadmin3CategorywidgetFactory;
        $this->registry = $registry;
        parent::__construct($context);
    }
  
	public function execute()
    {
		$this->registerData('categorydata');
        $layout = $this->_view->loadLayout();
		$widget_category_grid = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Grid\Widget\Category')->toHtml();
		return $this->getResponse()->setBody($widget_category_grid);
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
