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
 * @copyright   Copyright (c) 2017 Mageplaza (http://mageplaza.com/)
 * @license     http://mageplaza.com/license-agreement.html
 */

namespace Mageplaza\Osc\Model\System\Config\Source;

/**
 * Class Radio Style
 * @package Mageplaza\Osc\Model\System\Config\Source
 */
class RadioStyle
{
    const STYLE_DEFAULT = 'default';
    const WITH_GAP = 'with_gap';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('Default'),
                'value' => self::STYLE_DEFAULT
            ],
            [
                'label' => __('With Gap'),
                'value' => self::WITH_GAP
            ]
        ];

        return $options;
    }
}
