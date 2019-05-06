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
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Osc\Model\Geoip;

/**
 * Interface ProviderInterface
 * @package Mageplaza\Osc\Model\Geoip
 */
interface ProviderInterface
{
    /**
     * @param ipAddress
     *            IPv4 or IPv6 address to lookup.
     * @return Country model for the requested IP address.
     */
    public function country($ipAddress);

    /**
     * @param ipAddress
     *            IPv4 or IPv6 address to lookup.
     * @return City model for the requested IP address.
     */
    public function city($ipAddress);
}
