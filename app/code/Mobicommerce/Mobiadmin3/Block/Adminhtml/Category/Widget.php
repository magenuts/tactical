<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Category;
use Magento\Catalog\Api\CategoryRepositoryInterface;
class Widget extends \Magento\Backend\Block\Widget\Form\Container {

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Categoryicon\CollectionFactory
     */
    protected $mobiadmin3ResourceCategoryiconCollectionFactory;
    
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;
    
    protected $_dir;
    protected $storeManager;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Mobicommerce\Mobiadmin3\Model\Resource\Categoryicon\CollectionFactory $mobiadmin3ResourceCategoryiconCollectionFactory,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        $this->mobiadmin3ResourceCategoryiconCollectionFactory = $mobiadmin3ResourceCategoryiconCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->setUseAjax(true);
        $this->_headerText = __('Manage Mobile Apps');
        $this->_dir = $dir;
        $this->storeManager = $storeManager;
        parent::__construct($context,$data);

        $this->category = $this->getRequest()->getParam('cat', false);
        if($this->category){
            $this->category = $this->categoryRepository->get($this->category);
        }
    }
    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Mobicommerce_Mobiadmin3';
        $this->_controller = 'adminhtml_applications';
        parent::_construct();
    }
    
    public function getBasePathInfo($folder)
    {
        return $this->_dir->getPath($folder);
    }
    
    public function getBaseUrlInfo($folder)
    {
        return $this->_dir->getUrlPath($folder);
    }

    public function getImageUrl($link_url = '')
    {
        if(!empty($link_url))
        {
            $media_url = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            return $media_url.'/'.$link_url;
        }
        else
        {
            return '#';
        }
    }
    protected function _preparelayout()
    {
        if($this->getRequest()->getParam('cat', false))
        {
            $this->buttonList->add(
                'showwidgets',
                [
                    'label' => __('Widgets List'),
                    'class' => 'primary',
                    'onclick'=>"showwidgets()",
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'showwidgets', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                100
            );
            
            $this->buttonList->add(
                'createwidget',
                [
                    'label' => __('Add New Widget'),
                    'class' => 'primary',
                    'onclick'=>"createwidget()",
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'createwidget', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                200
            );
            
            $this->buttonList->add(
                'savewidget',
                [
                    'label' => __('Save'),
                    'class' => 'primary',
                    'onclick'=>"savewidget()",
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'savewidget', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                300
            );
        }      
        $this->removeButton('save');
        return parent::_prepareLayout();
    }

    /**
     * Return save url for edit form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('/*/widget', ['_current' => true, 'back' => null]);
    }
    
    public function getCategoryName()
    {
        if($this->category){
            return $this->category->getName();
        }
    }

    public function getCategory()
    {
        if($this->category){
            $collection = $this->mobiadmin3ResourceCategoryiconCollectionFactory->create();
            $collection->addFieldToFilter('mci_category_id', $this->category->getId());
            if($collection->getSize() > 0)
                return $collection->getFirstItem()->getData();
        }
        return null;
    }
}