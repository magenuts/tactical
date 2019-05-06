<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales\Items\Order;

class DefaultOrder extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
{
    /**
     * Final parent block
     *
     * @var \Magetrend\Email\Block\Email\Block
     */
    private $mainNode = null;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    public $imageBuilder;

    public $productRepository;

    public $moduleHelper;

    /**
     * DefaultOrder constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magetrend\Email\Helper\Data $moduleHelper,

     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magetrend\Email\Helper\Data $moduleHelper,
        array $data = []
    ) {
        $this->imageBuilder = $imageBuilder;
        $this->productRepository = $productRepository;
        $this->moduleHelper = $moduleHelper;
        parent::__construct($context, $data);
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $template = $this->getTemplate();
        $itemName = $this->getItem()->getName();
        if (strlen($itemName) > 23) {
            $template = str_replace('default.phtml', 'default_long.phtml', $template);
            $this->setTemplate($template);
        }
        return $this;
    }

    /**
     * Returns variable manager model
     *
     * @return \ Magetrend\Email\Model\Varmanager|null
     */
    public function getVarModel()
    {
        return $this->getMainNode()->getVarModel();
    }

    /**
     * Returns final parent block
     *
     * @return \Magetrend\Email\Block\Email\Block
     */
    public function getMainNode()
    {
        if ($this->mainNode == null) {
            $this->mainNode = $this->getParentBlock()->getParentBlock()->getParentBlock();
        }
        return $this->mainNode;
    }

    /**
     * Is text direction trl
     * @return bool
     */
    public function isRTL()
    {
        return $this->getMainNode()->isRTL();
    }

    /**
     * Show item image or not
     *
     * @return bool
     */
    public function showImage()
    {
        return true;
    }

    /**
     * Returns item image html
     *
     * @param $item
     * @return string
     */
    public function getItemImage($item)
    {
        $product = $item->getProduct();
        if (!$product) {
            return $this->getProductImagePlaceholder();
        }
        $imageUrl = $this->imageBuilder->setProduct($product)
            ->setImageId('sendfriend_small_image')
            ->create()->getImageUrl();
        return $imageUrl;
    }

    public function getProductImagePlaceholder()
    {
        return $this->getViewFileUrl('Magento_Catalog::images/product/placeholder/thumbnail.jpg');
    }


    public function getHelper()
    {
        return $this->moduleHelper;
    }
}
