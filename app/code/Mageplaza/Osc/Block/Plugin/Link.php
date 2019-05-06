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

namespace Mageplaza\Osc\Block\Plugin;

/**
 * Class Link
 * @package Mageplaza\Osc\Block\Plugin
 */
class Link
{
    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Mageplaza\Osc\Helper\Config
     */
    protected $configHelper;

    /**
     * Link constructor.
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param \Mageplaza\Osc\Helper\Config $configHelper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $httpRequest,
        \Mageplaza\Osc\Helper\Config $configHelper
    )
    {
        $this->_request     = $httpRequest;
        $this->configHelper = $configHelper;
    }

    /**
     * @param \Magento\Framework\Url $subject
     * @param $routePath
     * @param $routeParams
     * @return array|null
     */
    public function beforeGetUrl(\Magento\Framework\Url $subject, $routePath = null, $routeParams = null)
    {
        if ($this->configHelper->isEnabled() && $routePath == 'checkout' && $this->_request->getFullActionName() != 'checkout_index_index') {
            return ['onestepcheckout', $routeParams];
        }

        return null;
    }
}
