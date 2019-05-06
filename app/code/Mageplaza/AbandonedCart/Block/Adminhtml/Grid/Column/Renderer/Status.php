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

namespace Mageplaza\AbandonedCart\Block\Adminhtml\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class Status
 * @package Mageplaza\AbandonedCart\Block\Adminhtml\Grid\Column\Renderer
 */
class Status extends AbstractRenderer
{
    /**
     * Render email status
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        if ($this->_getValue($row)) {
            $class = 'grid-severity-notice';
            $text  = __('Sent');
        } else {
            $class = 'grid-severity-major';
            $text  = __('Error');
        }

        return '<span class="' . $class . '"><span>' . $text . '</span></span>';
    }
}
