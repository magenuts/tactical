<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Deeplink\Cms;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended 
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
    	\Magento\Backend\Block\Template\Context $context,
    	\Magento\Backend\Helper\Data $data,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request
    ) 
	{
        $this->registry = $registry;
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
        $collection=$_objectManager->create('Magento\Cms\Model\Page')->getCollection()
			->addFieldToFilter('is_active',1);
		
	    $this->setCollection($collection);
	    return parent::_prepareCollection();
	} 
	
	protected function _prepareColumns()
    {
		$this->addColumn('in_cms_widget', [
			'index'     => 'page_id',
			'renderer' => 'Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Deeplink\Cms\Grid\Renderer\Radio',
			'filter' => false,
			'width'     => '30',
			'header_css_class' => 'a-center',
			'align'     => 'center',
		]);

		$this->addColumn('title', [
            'header'    => __('Title'),
            'align'     => 'left',
            'index'     => 'title',
        ]);

        $this->addColumn('identifier', [
            'header'    => __('URL Key'),
            'align'     => 'left',
            'index'     => 'identifier'
        ]);
        return parent::_prepareColumns();
    }

	public function getGridUrl()
	{
		return $this->getUrl('*/*/cmsdeepajaxgrid', [
			'_current'=>true,
			'widget_id' => $this->request->getParam('widget_id'),
			'link_type_value' => $this->request->getParam('link_type_value')
			]);
	}
}