<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Specificcustomerajaxgrid extends \Magento\Backend\App\Action {

    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

	public function execute()
    {
        $layout = $this->_view->loadLayout();
		$widget_customer_grid = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Grid\Widget\Specificcustomer')->toHtml();
       	return $this->getResponse()->setBody($widget_customer_grid);
	}
}