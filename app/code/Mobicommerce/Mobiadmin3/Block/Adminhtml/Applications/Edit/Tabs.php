<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs  {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
    	\Magento\Backend\Block\Template\Context $context,
    	\Magento\Framework\Json\EncoderInterface $json,
    	\Magento\Backend\Model\Auth\Session $auth,
        \Magento\Framework\Registry $registry
    )
	{
        $this->registry = $registry;
		parent::__construct($context,$json,$auth);
		$this->setId('application_data');
		$this->setDestElementId('edit_form');
		$this->setTitle(__('App Settings'));
	}

	protected function _beforeToHtml()
	{
		$appdata = $this->registry->registry('application_data');
		$versionType = $appdata->getVersionType();
		if(!in_array($versionType, ['001', '002'])){
			$versionType = '001';
		}

		$this->addTab('overview',[ 
			'label'   => __('Overview'),
			'title'   => __('Overview'),
			'content' => $this->getLayout()->createBlock('\Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit\Tab\Overview')->toHtml(),
            'active' => true
			]);

		$this->addTab('general_settings', [
			'label'   => __('General Settings'),
			'title'   => __('General Settings'),
			'content' => $this->getLayout()->createBlock('\Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit\Tab\Form')->toHtml()
			]);

    	$this->addTab('theme_personalization', [
			'label'   => __('Color Scheme'),
			'title'   => __('Color Scheme'),
			'content' => $this->getLayout()->createBlock('\Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit\Tab\Personalization')->toHtml()
			]);

		$this->addTab('advance_settings', [
			'label'   => __('Advance Settings'),
			'title'   => __('Advance Settings'),
			'content' => $this->getLayout()->createBlock('\Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit\Tab\Advancesettings')->toHtml()
			]);

		$this->addTab('widgets', [
			'label'   => __('Home Page Widgets'),
			'title'   => __('Home Page Widgets'),
			'content' => $this->getLayout()->createBlock('\Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit\Tab\Widget')->toHtml()
			]);

		$this->addTab('cms_contents', [
			'label'   => __('CMS Contents'),
			'title'   => __('CMS Contents'),
			'content' => $this->getLayout()->createBlock('\Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit\Tab\Cms')->toHtml()
			]);

		$this->addTab('google_analytics', [
			'label'   => __('Google Analytics'),
			'title'   => __('Google Analytics'),
			'content' => $this->getLayout()->createBlock('\Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit\Tab\Googleanalytics')->toHtml()
			]);

    	$this->addTab('push_notification', [
			'label'   => __('Push Notifications'),
			'title'   => __('Push Notifications'),
			'content' => $this->getLayout()->createBlock('\Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit\Tab\Pushnotifications')->toHtml()
			]);
        
		return parent::_beforeToHtml();
	}
}