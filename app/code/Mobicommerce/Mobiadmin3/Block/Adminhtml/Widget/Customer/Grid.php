<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Customer;
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

    public function __construct(\Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $data,
        \Magento\Framework\Registry $registry,
        \Mobicommerce\Mobiadmin3\Helper\Data $mobiadmin3Helper,
        \Magento\Framework\App\Request\Http $request
    ) 
	{
        $this->registry = $registry;
        $this->mobiadmin3Helper = $mobiadmin3Helper;
        $this->request = $request;
		parent::__construct($context,$data);
		$this->setId('id');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	protected function _prepareCollection() 
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        $collection = $objectManager->create('Magento\Customer\Model\Group')->getCollection();
	    $this->setCollection($collection);
	    return parent::_prepareCollection();
	} 

	protected function _prepareColumns()
    {
        $this->addColumn('in_customer_widget', [
			'header_css_class' => 'a-center',
			'align'            => 'center',
			'index'            => 'customer_group_id',
			'renderer'         => 'Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Customer\Grid\Renderer\Checkbox',
			'filter'           => false,
			'width'            => '30',
		]);

		$this->addColumn('customergroupgrid_customer_group_id', [
            'header' => __('Customer Group Id'),
            'index'  => 'customer_group_id',
        ]);

		$this->addColumn('customergroupgrid_customer_group_code', [
            'header' => __('Customer Group Name'),
            'index'  => 'customer_group_code',
        ]);
		
        return parent::_prepareColumns();
    }

    public function getGridUrl()
	{
		return $this->getUrl('*/*/customerajaxgrid', [
			'_current'=>true,
		]);
	}
    
    public function getMultipleRows($item)
    {
        return false;
    }
}