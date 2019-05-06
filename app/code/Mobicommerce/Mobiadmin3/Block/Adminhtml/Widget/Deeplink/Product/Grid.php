<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Deeplink\Product;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
    	\Magento\Backend\Block\Template\Context $context,
    	\Magento\Backend\Helper\Data $data,
        \Magento\Framework\App\Request\Http $request
    ) 
	{
        $this->request = $request;
		parent::__construct($context, $data);
		$this->setId('id');
		$this->setDefaultDir('ASC');
		$this->setUseAjax(true);
	}

	protected function _prepareCollection() 
	{
		$storeid = $this->request->getParam('store_id');		
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
			'align'            => 'center',
			'index'            => 'entity_id',
			'renderer'         => 'Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Deeplink\Product\Grid\Renderer\Radio',
			'filter'           => false,
			'width'            => '30',
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
		
        return parent::_prepareColumns();
    }

	public function _getSelectedCategories()
	{
		$categoryData = $this->getCategoryWidget();;
		$category_widget_data = unserialize($categoryData['widget_data']);
        $cat_ids = $category_widget_data['categories']; 
		return $cat_ids;
	}

	public function getGridUrl()
	{
		return $this->getUrl('*/*/productdeepajaxgrid', [
			'_current'        => true,
			'widget_id'       => $this->request->getParam('widget_id'),
			'link_type_value' => $this->request->getParam('link_type_value')
		]);
	}
}