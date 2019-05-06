<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Imagemap extends \Magento\Backend\App\Action {

    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

	public function execute()
    {
	    $layout = $this->_view->loadLayout();
        echo $blockInstance = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Comman')->setTemplate('Mobicommerce_Mobiadmin3::mobiadmin3/application/edit/tab/widget/type/image/imagemap.phtml')->toHtml();
	}
}
