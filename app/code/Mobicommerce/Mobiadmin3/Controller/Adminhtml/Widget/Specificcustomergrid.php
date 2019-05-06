<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Specificcustomergrid extends \Magento\Backend\App\Action {

    protected $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

	public function execute()
    {
		$layout = $this->_view->loadLayout();
		$widget_spacificcustomer_grid = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Grid\Widget\Specificcustomer')->toHtml();
		$response['widget_spacificcustomer_grid'] = $widget_spacificcustomer_grid;
		$response['status'] = 'success';
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
	}
}
