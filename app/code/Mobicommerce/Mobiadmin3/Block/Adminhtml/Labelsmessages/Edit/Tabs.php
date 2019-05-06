<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Labelsmessages\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs 
{
	protected $registry;
    
    public function __construct(
    	\Magento\Backend\Block\Template\Context $context,
    	\Magento\Framework\Json\EncoderInterface $json,
    	\Magento\Backend\Model\Auth\Session $auth
    )
	{
		parent::__construct($context, $json, $auth);
		$this->setId('labelsmessages_data_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(__('Labels and Messages'));
	}
}