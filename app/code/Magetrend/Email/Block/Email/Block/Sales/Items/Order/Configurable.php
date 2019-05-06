<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales\Items\Order;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail as ThumbnailSource;

class Configurable extends \Magetrend\Email\Block\Email\Block\Sales\Items\Order\DefaultOrder
{
    private $childProduct = null;

    public function getItemImage($item)
    {
        /**
         * Show parent product thumbnail if it must be always shown according to the related setting in system config
         * or if child thumbnail is not available
         */
        $showParent = $this->_scopeConfig->getValue(
            \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable::CONFIG_THUMBNAIL_SOURCE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($showParent == ThumbnailSource::OPTION_USE_PARENT_IMAGE ||
            !($this->getChildProduct($item)->getThumbnail() && $this->getChildProduct($item)->getThumbnail() != 'no_selection')
        ) {
            return parent::getItemImage($item);
        }

        $product = $this->getChildProduct($item);
        if (!$product) {
            return parent::getItemImage($item);
        }

        $imageUrl = $this->imageBuilder->setProduct($product)
            ->setImageId('sendfriend_small_image')
            ->create()->getImageUrl();
        return $imageUrl;
    }

    public function getChildProduct($item)
    {
        if ($this->childProduct == null) {
            $options = $item->getProductOptions();
            if (isset($options['simple_sku']) && !empty($options['simple_sku'])) {
                $this->childProduct = $this->productRepository->get($options['simple_sku']);
            }
        }

        return $this->childProduct;
    }

}