<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Linktype extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    protected $_resultPageFactory;
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }
    
	public function execute()
	{
		$isAjax = $this->request->getParam('isAjax');
		$link_type = $this->request->getParam('link_type');

        if($link_type) {
           $layout = $this->_view->loadLayout();
			switch ($link_type) {
				case 'product':
                    $deeplink_block_content = $layout->getLayout()->createBlock('\Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Grid\Widget\Deeplink\Product')->toHtml();
					break;
				case 'category':
					$deeplink_block_content = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Comman')->setTemplate('Mobicommerce_Mobiadmin3::mobiadmin3/application/edit/tab/widget/type/deeplink/category.phtml')->toHtml();
					break;
				case 'cms':
					$deeplink_block_content = $layout->getLayout()->createBlock('\Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Grid\Widget\Deeplink\Cms')->toHtml();
                    break;
				case 'phone':
					$deeplink_block_content = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Comman')->setTemplate('Mobicommerce_Mobiadmin3::mobiadmin3/application/edit/tab/widget/type/deeplink/phone.phtml')->toHtml();
					break;
				case 'email':
					$deeplink_block_content = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Comman')->setTemplate('Mobicommerce_Mobiadmin3::mobiadmin3/application/edit/tab/widget/type/deeplink/email.phtml')->toHtml();
					break;
				case 'external':
					$deeplink_block_content = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Comman')->setTemplate('Mobicommerce_Mobiadmin3::mobiadmin3/application/edit/tab/widget/type/deeplink/external.phtml')->toHtml();
					break;
				case 'qrscan':
					$deeplink_block_content = $layout->getLayout()->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Comman')->setTemplate('Mobicommerce_Mobiadmin3::mobiadmin3/application/edit/tab/widget/type/deeplink/qrscan.phtml')->toHtml();
					break;
			}

            $resultPage = $this->_resultPageFactory->create();
            $this->getResponse()->setBody($deeplink_block_content);
            return;
            /*
			$response['deeplink_block_content'] = $deeplink_block_content;
			$response['status'] = 'success';
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
            */
		}
	}
}