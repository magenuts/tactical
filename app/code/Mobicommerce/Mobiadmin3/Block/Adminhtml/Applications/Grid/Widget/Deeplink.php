<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Grid\Widget;

class Deeplink extends \Magento\Backend\Block\Template {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Backend\Block\Template\Context $context
    )
    {
        $this->request = $request;
        parent::__construct($context);
		$this->setTemplate('mobiadmin3/application/edit/tab/widget/type/deeplink.phtml');
    }
}