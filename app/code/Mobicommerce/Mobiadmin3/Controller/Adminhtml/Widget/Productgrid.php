<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Productgrid extends \Magento\Backend\App\Action {

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
		//$layout = $this->_view->loadLayout();
		$widget_product_grid = $this->_view->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Grid\Widget\Product')->toHtml();
		$response['widget_product_grid'] = $widget_product_grid;
		$response['status'] = 'success';
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
	}
}