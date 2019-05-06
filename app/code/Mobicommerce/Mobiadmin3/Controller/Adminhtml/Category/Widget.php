<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Category;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class Widget extends \Magento\Backend\App\Action {

    /**
     * @var \Mobicommerce\Mobiadmin3\Helper\Data
     */
    protected $mobiadmin3Helper;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Categoryicon\CollectionFactory
     */
    protected $mobiadmin3ResourceCategoryiconCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\CategoryiconFactory
     */
    protected $mobiadmin3CategoryiconFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\CategorywidgetFactory
     */
    protected $mobiadmin3CategorywidgetFactory;

    /**
     * @var \Magento\Framework\File\UploaderFactory
     */
    protected $uploaderFactory;
    
    protected $_resultPageFactory;
    protected $category;
    protected $_dir;
    protected $messageManager;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Mobicommerce\Mobiadmin3\Helper\Data $mobiadmin3Helper,
        \Mobicommerce\Mobiadmin3\Model\Resource\Categoryicon\CollectionFactory $mobiadmin3ResourceCategoryiconCollectionFactory,
        \Mobicommerce\Mobiadmin3\Model\CategoryiconFactory $mobiadmin3CategoryiconFactory,
        \Mobicommerce\Mobiadmin3\Model\CategorywidgetFactory $mobiadmin3CategorywidgetFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->mobiadmin3Helper = $mobiadmin3Helper;
        $this->mobiadmin3ResourceCategoryiconCollectionFactory = $mobiadmin3ResourceCategoryiconCollectionFactory;
        $this->mobiadmin3CategoryiconFactory = $mobiadmin3CategoryiconFactory;
        $this->mobiadmin3CategorywidgetFactory = $mobiadmin3CategorywidgetFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->categoryRepository = $categoryRepository;
        $this->_dir = $dir; 
        $this->messageManager = $messageManager;
        
        parent::__construct($context);
    }

    public function execute()
	{   
        $cat = $this->getRequest()->getParam('cat', null);
        $this->category = $this->getRequest()->getParam('cat', false);
        
        if($this->category){
           $this->category = $this->categoryRepository->get($this->category);
        }
        
        //Code edited by PArvez to "cat" will replace wit post parameter
        if($this->getRequest()->getPost("form_key"))
        {
            $this->saveWidget();
        }
        else
        {
            $resultPage = $this->_resultPageFactory->create();
            if($this->category)
            {
                $resultPage->getConfig()->getTitle()->prepend( $this->category->getName()." ".__('Category Widget'));
            }
            else
            {
               $resultPage->getConfig()->getTitle()->prepend(__('Category Widget')); 
            }
            
            return $resultPage;
        }
	}

    public function saveWidget()
    {
        $cat = $this->getRequest()->getParam('cat', null);
        $post = $this->getRequest()->getPost();        
        
        $this->saveWidgetPosition($post);
        $widget_data = $post['widget'];
        $media_url =  $this->_dir->getUrlPath("media");
        $media_dir = $this->_dir->getPath("media");
        $appgalleryimageurl = 'mobi_commerce/category/';
        $media_path =  $media_dir.'/mobi_commerce/category/';

        if(!file_exists($media_path)) {
            mkdir($media_path, 0777);
        }
        
        if(isset($_FILES['category_thumbnail']['name']) && !empty($_FILES['category_thumbnail']['name'])){
            $path =$this->_dir->getPath("media").'/mobi_commerce/category/';
            $fname = uniqid().'.'.PATHINFO($_FILES['category_thumbnail']['name'], PATHINFO_EXTENSION);
            try{
                $uploader = $this->uploaderFactory->create(['fileId' => 'category_thumbnail']);
                $uploader->setAllowedExtensions(['jpg','gif','png','jpeg']);
                $uploader->setAllowCreateFolders(true);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $result = $uploader->save($path, $fname);
                $iconCollection = $this->mobiadmin3ResourceCategoryiconCollectionFactory->create();
                $iconCollection->addFieldToFilter('mci_category_id', $cat);
                if($iconCollection->getSize() > 0){
                    foreach($iconCollection as $_collection){
                        $_collection->setMciThumbnail($result['file'])->save();
                    }
                }
                else{
                    $this->mobiadmin3CategoryiconFactory->create()->setData([
                        'mci_category_id' => $cat,
                        'mci_thumbnail'   => $result['file']
                        ])->save();
                }
            }
            catch(Exception $e){
                $this->messageManager->addError($e->getMessage());
            }
        }

        if(isset($_FILES['category_banner']['name']) && !empty($_FILES['category_banner']['name'])){
            $path = $this->_dir->getPath("media").'/mobi_commerce/category/';
            $fname = uniqid().'.'.PATHINFO($_FILES['category_banner']['name'], PATHINFO_EXTENSION);
            try{
                $uploader = $this->uploaderFactory->create(['fileId' => 'category_banner']);
                $uploader->setAllowedExtensions(['jpg','gif','png','jpeg']);
                $uploader->setAllowCreateFolders(true);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $result = $uploader->save($path, $fname);
                $iconCollection = $this->mobiadmin3ResourceCategoryiconCollectionFactory->create();
                $iconCollection->addFieldToFilter('mci_category_id', $cat);
                if($iconCollection->getSize() > 0){
                    foreach($iconCollection as $_collection){
                        $_collection->setMciBanner($result['file'])->save();
                    }
                }
                else{
                    $this->mobiadmin3CategoryiconFactory->create()->setData([
                        'mci_category_id' => $cat,
                        'mci_banner'      => $result['file']
                        ])->save();
                }
            }
            catch(Exception $e){
                $this->messageManager->addError($e->getMessage());
            }
        }

        if(isset($post['delete_image_thumbnail'])){
            $iconCollection = $this->mobiadmin3ResourceCategoryiconCollectionFactory->create();
            $iconCollection->addFieldToFilter('mci_category_id', $cat);
            if($iconCollection->getSize() > 0){
                foreach($iconCollection as $_collection){
                    $_collection->setMciThumbnail(NULL)->save();
                }
            }
        }
        if(isset($post['delete_image_banner'])){
            $iconCollection = $this->mobiadmin3ResourceCategoryiconCollectionFactory->create();
            $iconCollection->addFieldToFilter('mci_category_id', $cat);
            if($iconCollection->getSize() > 0){
                foreach($iconCollection as $_collection){
                    $_collection->setMciBanner(NULL)->save();
                }
            }
        }
        
        $this->mobiadmin3Helper->saveWidget($post);

        $message = __('Data saved successfully.');
        $this->messageManager->addSuccess($message);

        $this->_redirect('*/*/widget', [
            'cat'      => $cat,
            '_current' => true
        ]);
    }

    public function saveWidgetPosition($post)
    {
        if(count($post['widget_position'])) {
            foreach($post['widget_position'] as $widget_id => $position) {              
                $widgetModel = $this->mobiadmin3CategorywidgetFactory->create()->load($widget_id);
                $widgetModel->setWidgetPosition($position)->save();
            }
        }    
    }
}