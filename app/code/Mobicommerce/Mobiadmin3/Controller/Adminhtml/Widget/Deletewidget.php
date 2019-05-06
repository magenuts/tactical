<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;
use Magento\Framework\Controller\ResultFactory;

class Deletewidget extends \Magento\Backend\App\Action {

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\WidgetFactory
     */
    protected $mobiadmin3WidgetFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\CategorywidgetFactory
     */
    protected $mobiadmin3CategorywidgetFactory;

    protected $messageManager;
    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Mobicommerce\Mobiadmin3\Model\WidgetFactory $mobiadmin3WidgetFactory,
        \Mobicommerce\Mobiadmin3\Model\CategorywidgetFactory $mobiadmin3CategorywidgetFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ResultFactory $resultPageFactory
    ) {
        $this->mobiadmin3WidgetFactory = $mobiadmin3WidgetFactory;
        $this->mobiadmin3CategorywidgetFactory = $mobiadmin3CategorywidgetFactory;
        $this->messageManager = $messageManager;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

	public function execute()
    {
		if($this->getRequest()->getParam('widget_id') > 0) {
            try{
                $cat = $this->getRequest()->getParam('cat');
                if(empty($cat))
                    $model = $this->mobiadmin3WidgetFactory->create();
                else
                    $model = $this->mobiadmin3CategorywidgetFactory->create();

                $model->setId($this->getRequest()->getParam('widget_id'))
                    ->delete();
                $this->messageManager->addSuccess(__('Widget is deleted successfully'));
                $resultRedirect = $this->resultPageFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }catch(Exception $e){
                $resultRedirect = $this->resultPageFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }
        }
		$resultRedirect = $this->resultPageFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;        
	}
}
