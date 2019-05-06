<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Product;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended 
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    protected $_storeManager;

    public function __construct(
    	\Magento\Backend\Block\Template\Context $context,
    	\Magento\Backend\Helper\Data $data,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) 
	{
        $this->request = $request;
		parent::__construct($context, $data);
		$this->setId('id');
		$this->setDefaultDir('ASC');
		$this->setUseAjax(true);
        $this->_storeManager = $storeManager;
	}

	protected function _prepareCollection() 
	{
	    $storeid = $this->_storeManager->getStore()->getId();
		$cat = $this->getRequest()->getParam('cat', false);
		if(!empty($cat)){
            $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $category = $_objectManager->create('Magento\Catalog\Model\Category')->load($cat);
			$collection = $category->getProductCollection()
				->addFieldToFilter('visibility', ['neq' => '1'])
				->addAttributeToSelect('*');
		}
		else{
            $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $collection = $_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory')->create()->setStoreId($storeid)->addFieldToFilter('visibility', ['neq' => '1'])->addAttributeToSelect('*');
		}

	    $this->setCollection($collection);
	    return parent::_prepareCollection();
	} 

	protected function _prepareColumns()
    {
		$this->addColumn('in_product_widget', [
			'header_css_class' => 'a-center',
			'align'     => 'center',
			'index'     => 'entity_id',
		    'renderer' => 'Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Product\Grid\Renderer\Checkbox',
           
			'filter' => false,
			'width'     => '30',
		]);

		$this->addColumn('entity_id', [
            'header' => __('Id'),
            'width'  => '50px',
            'index'  => 'entity_id',
            ]);
		$this->addColumn('name', [
            'header' => __('Name'),
            'width'  => '200px',
            'index'  => 'name',
            ]);
		$this->addColumn('position', [
            'header' => __('Position'),
            'width'  => '50px',
            'renderer' => 'Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Product\Grid\Renderer\Position',
			'type'   => 'text',
			'filter' => false,
            ]);

        return parent::_prepareColumns();
    }

	public function getGridUrl()
	{
		return $this->getUrl('*/*/productgridajax', [
			'_current'=>true,
			'widget_id' => $this->request->getParam('widget_id'),
		]);
	}
	
}