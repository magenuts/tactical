<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Specificcustomer;

class Grid extends  \Magento\Backend\Block\Widget\Grid\Extended {

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
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	protected function _prepareCollection() 
	{
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objectManager->create('Magento\Customer\Model\Customer')->getCollection()
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');
	    $this->setCollection($collection);
	    return parent::_prepareCollection();
	} 

	protected function _prepareColumns()
    {
		$this->addColumn('in_specific_widget', [
			'header_css_class' => 'a-center',
			'align'            => 'center',
			'index'            => 'entity_id',
			'renderer'         => 'Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Specificcustomer\Grid\Renderer\Checkbox',
			'filter'           => false,
			'width'            => '30',
		]);

		$this->addColumn('customergrid_customer_id', [
            'header' => __('Id'),
            'index'  => 'entity_id',
        ]);

		$this->addColumn('customergrid_customername', [
            'header' => __('Customer Name'),
            'index'  => 'name',
        ]);

		$this->addColumn('customergrid_email', [
            'header' => __('Email'),
            'index'  => 'email',
        ]);
		
        return parent::_prepareColumns();
    }

    public function getGridUrl()
	{
		return $this->getUrl('*/*/specificcustomerajaxgrid', [
			'_current'=>true,
		]);
	}
}