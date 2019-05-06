<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Callwidget extends \Magento\Backend\App\Action {

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
		$widget_category_id = $this->request->getParam('cat', false);
		$widget_code = $this->request->getParam('widget_code');
		$widget_id = $this->request->getParam('widget_id');
        if($widget_id){
        	if(empty($widget_category_id))
				$widgetdata = $this->mobiadmin3WidgetFactory->create()->load((int) $widget_id);
			else
				$widgetdata = $this->mobiadmin3CategorywidgetFactory->create()->load((int) $widget_id);

			if ($widgetdata->getWidgetId()){
				$this->registry->register('widgetdata', $widgetdata->getData());
			}
		}
        else {
            $this->registry->register('widgetdata', []);
        }

		if($isAjax){
			$layout = $this->_view->loadLayout();
			switch ($widget_code) {
				case 'widget_image_slider':
                    $widget_block_content = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Type\Imageslider')->toHtml();
					break;
				case 'widget_category':
                    $widget_block_content = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Type\Category')->toHtml();
					break;
				case 'widget_product_slider':
					$widget_block_content = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Type\Product')->toHtml();
					break;
				case 'widget_image':
					$widget_block_content = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Type\Imagewidget')->toHtml();
					break;
			}
			$response['widget_block_content'] = $widget_block_content;
			$response['status'] = 'success';
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
		}else{
			$response['error'] = 'Some thing Error';	
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
		}
	}

    public function getWidgetData()
    {
        return $widgetdata = $this->registry->registry('widgetdata');
    }
}
