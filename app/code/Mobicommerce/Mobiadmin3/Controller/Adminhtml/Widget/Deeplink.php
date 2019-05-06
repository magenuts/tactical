<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Deeplink extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->request = $request;
        $this->registry = $registry;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

	public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $block = $resultPage->getLayout()
            ->createBlock('Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Grid\Widget\Deeplink')
            ->toHtml();
        $this->getResponse()->setBody($block);
	}
}
