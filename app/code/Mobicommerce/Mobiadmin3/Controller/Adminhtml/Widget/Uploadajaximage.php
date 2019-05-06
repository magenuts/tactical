<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Widget;

class Uploadajaximage extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\File\UploaderFactory
     */
    protected $uploaderFactory;
    protected $resultJsonFactory;
    protected $_dir;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        
        $this->_dir = $dir;
        parent::__construct($context);
    }

	public function execute()
    {
        $post = $this->getRequest()->getPostValue();
		if ( 0 < $_FILES['file']['error'] ) {
			echo 'Error: ' . $_FILES['file']['error'] . '<br>';
		}
		else {
			$path = $this->_dir->getPath("media").'/mobi_commerce/widget_image/';
			$fname = uniqid().'.'.PATHINFO($_FILES['file']['name'], PATHINFO_EXTENSION);
			$uploader = $this->uploaderFactory->create(['fileId' => 'file']);
            $uploader->setAllowedExtensions(['jpg','gif','png','jpeg']);
            $uploader->setAllowCreateFolders(true);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $result = $uploader->save($path, $fname);
			$response['image_url'] = 'mobi_commerce/widget_image/'.$result['file'];
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
		}
	}
}
