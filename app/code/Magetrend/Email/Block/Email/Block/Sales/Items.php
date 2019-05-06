<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales;

class Items extends \Magento\Sales\Block\Order\Email\Items
{
    /**
     * @var \Magetrend\Email\Model\Varmanager|null
     */
    private $varManager = null;

    /**
     * @var \Magetrend\Email\Model\Varmanager
     */
    public $varManagerModel;

    /**
     * Items constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magetrend\Email\Model\Varmanager $varmanager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetrend\Email\Model\Varmanager $varmanager,
        array $data = []
    ) {
        $this->varManagerModel = $varmanager;
        parent::__construct($context, $data);
    }

    protected function _beforeToHtml()
    {
        //replace theme
        $template = $this->getTemplate();
        $theme = $this->getParentBlock()->getTheme();
        if ($theme != 'default') {
            $template = str_replace('/default/', '/'.$theme.'/', $template);
            $this->setTemplate($template);
        }

        return parent::_beforeToHtml();
    }

    public function getVarModel()
    {
        if ($this->varManager == null) {
            $this->varManager = $this->varManagerModel;
            $this->varManager->setTemplateId($this->getTemplateId());
            $this->varManager->setBlockId($this->getBlockId());
            $this->varManager->setBlockName($this->getBlockName());
        }
        return $this->varManager;
    }

    public function isRTL()
    {
        return $this->getDirection() == 'rtl';
    }

    public function getDirection()
    {
        return 'ltr';
    }
}
