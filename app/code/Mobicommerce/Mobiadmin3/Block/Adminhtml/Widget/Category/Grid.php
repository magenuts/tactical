<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Category;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Mobicommerce\Mobiadmin3\Helper\Data
     */
    protected $mobiadmin3Helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $data,
        \Magento\Framework\Registry $registry,
        \Mobicommerce\Mobiadmin3\Helper\Data $mobiadmin3Helper,
        \Magento\Framework\App\Request\Http $request
    ) 
	{
        $this->registry = $registry;
        $this->mobiadmin3Helper = $mobiadmin3Helper;
        $this->request = $request;
		parent::__construct($context, $data);
		$this->setId('id');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	public function getCategoryWidget()
    {
        return $this->registry->registry('categorydata');
    }

	protected function _prepareCollection() 
	{
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $_objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory')->create()->addAttributeToSelect('*');
		
		$cat = $this->getRequest()->getParam('cat', false);
		if(!empty($cat)){
			$subcategory = $this->mobiadmin3Helper->getAllChildCategories($cat);
			$collection->addIdFilter($subcategory);
		}        

	    $this->setCollection($collection);
	    return parent::_prepareCollection();
	} 

	protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_category_widget') {
            $catIds = $this->_getSelectedCategories();
            if (empty($catIds)) {
                $catIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $catIds]);
            }
            elseif(!empty($catIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $catIds]);
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

	protected function _prepareColumns()
    {
		$this->addColumn('in_category_widget', [
			'name'             => "widget[widget_data][categories][id][]",
			'index'            => 'entity_id',
			'renderer'         => 'Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Category\Grid\Renderer\Checkbox',
			'filter'           => false,
			'width'            => '30',
			'header_css_class' => 'a-center',
			'align'            => 'center',
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
			'header'   => __('Position'),
			'width'    => '50px',
			'renderer' => 'Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Category\Grid\Renderer\Position',
			'type'     => 'text',
			'filter'   => false,
            ]);

        return parent::_prepareColumns();
    }

	public function _getSelectedCategories()
	{
        $cat_ids = [];
		$categoryData = $this->getCategoryWidget();
        if($categoryData) {
            $category_widget_data = unserialize($categoryData['widget_data']);
            $cat_ids = $category_widget_data['categories'];
        }
		return $cat_ids;
	}

	public function getGridUrl()
	{
		return $this->getUrl('*/*/categoryajaxgrid', [
			'_current' => true,
			'widget_id' => $this->request->getParam('widget_id'),
			]);
	}
    
    public function getMultipleRows($item)
    {
        return false;
    }
}