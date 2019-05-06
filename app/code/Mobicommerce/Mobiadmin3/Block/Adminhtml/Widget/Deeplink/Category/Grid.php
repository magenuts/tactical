<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Deeplink\Category;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended 
{

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
		$this->setDefaultDir('ASC');
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

	protected function _prepareColumns()
    {
		$this->addColumn('in_category_widget', [
			'name'             => "widget[widget_data][categories][id][]",
			'index'            => 'entity_id',
			'renderer'         => 'Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Deeplink\Category\Grid\Renderer\Radio',
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

        return parent::_prepareColumns();
    }

	public function getGridUrl()
	{
		return $this->getUrl('*/*/categorydeepajaxgrid', [
			'_current'=>true,
			'widget_id' => $this->request->getParam('widget_id'),
			'link_type_value' => $this->request->getParam('link_type_value')
			]);
	}
	
    public function getMultipleRows($item)
    {
        return false;
    }
}