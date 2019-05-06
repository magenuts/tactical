<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) 2017-2018 Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Block\Email;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Pricing\Helper\Data;
use Mageplaza\AbandonedCart\Helper\Data as ModuleHelper;

/**
 * Class Template
 * @package Mageplaza\AbandonedCart\Block\Email
 */
class Template extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Mageplaza\AbandonedCart\Helper\Data
     */
    protected $helperData;

    /**
     * Template constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Mageplaza\AbandonedCart\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        Data $pricingHelper,
        ModuleHelper $helperData,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_productRepository = $productRepository;
        $this->imageHelper        = $context->getImageHelper();
        $this->pricingHelper      = $pricingHelper;
        $this->helperData         = $helperData;
    }

    /**
     * Get items in quote
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductCollection()
    {
        $items = [];
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->getQuote();
        if ($quote) {
            foreach ($quote->getAllVisibleItems() as $item) {
                $items[] = $this->_productRepository->getById($item->getProductId())
                    ->setQtyOrder($item->getQty());
            }
        }

        return $items;
    }

    /**
     * Get subtotal in quote
     *
     * @return string
     */
    public function getSubtotal()
    {
        $subtotal = $this->getQuote() ? $this->getQuote()->getSubtotal() : 0;

        return $this->pricingHelper->currency($subtotal, true, true);
    }

    /**
     * Get image url in quote
     *
     * @param $_item
     * @return string
     */
    public function getProductImage($_item)
    {
        return $this->imageHelper->init($_item, 'category_page_grid', ['height' => 100, 'width' => 100])->getUrl();
    }

    /**
     * Get item price in quote
     *
     * @param $_item
     * @return float|string
     */
    public function getProductPrice($_item)
    {
        $productPrice = $_item->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

        return $this->pricingHelper->currency($productPrice, true, false);
    }

    /**
     * @return string
     */
    public function getPlaceholderImage()
    {
        return $this->imageHelper->getDefaultPlaceholderUrl('image');
    }
}
