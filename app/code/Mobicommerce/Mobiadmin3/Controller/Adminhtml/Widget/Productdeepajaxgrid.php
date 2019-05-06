<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Productdeepajaxgrid extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
        parent::__construct($context);
    }

	public function execute()
    {
		$layout = $this->_view->loadLayout();
		$widget_product_grid = $layout->getLayout()->createBlock('\Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Grid\Widget\Deeplink\Product')->toHtml();
		
       	return $this->getResponse()->setBody($widget_product_grid);
	}
}