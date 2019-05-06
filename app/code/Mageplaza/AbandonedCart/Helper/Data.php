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

namespace Mageplaza\AbandonedCart\Helper;

use Mageplaza\Core\Helper\AbstractData;

/**
 * Class Data
 * @package Mageplaza\AbandonedCart\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'abandonedcart';

    /**
     * @var string Url Suffig analytics
     */
    protected $urlSuffix = [];

    /**
     * @param null $storeId
     * @return array|mixed
     * @throws \Zend_Serializer_Exception
     */
    public function getEmailConfig($storeId = null)
    {
        $emailConfig = $this->getConfigGeneral('email', $storeId);
        if ($emailConfig) {
            $configs = $this->unserialize($emailConfig);
            foreach ($configs as $configId => $config) {
                if (isset($config['send']) && $config['send']) {
                    $configSeconds = 0;
                    $configTimes   = explode(' ', $config['send']);
                    foreach ($configTimes as $configTime) {
                        if (strpos($configTime, 'd') !== false) {
                            $configSeconds += (int)str_replace('d', '', $configTime) * 24 * 60 * 60;
                        } else if (strpos($configTime, 'h') !== false) {
                            $configSeconds += (int)str_replace('h', '', $configTime) * 60 * 60;
                        } else if (strpos($configTime, 'm') !== false) {
                            $configSeconds += (int)str_replace('m', '', $configTime) * 60;
                        }
                    }
                    $configs[$configId]['send'] = $configSeconds;
                    $send[$configId]            = $configSeconds;
                }
            }
            array_multisort($send, SORT_ASC, $configs);

            return $configs;
        }

        return [];
    }

    /**
     * @param string $code
     * @param null $storeId
     * @return mixed
     */
    public function getAnalyticsConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/analytics' . $code, $storeId);
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getUrlSuffix($storeId)
    {
        if (!isset($this->urlSuffix[$storeId])) {
            $suffix = '';
            if ($source = $this->getAnalyticsConfig('source', $storeId)) {
                $suffix .= 'utm_source=' . $source;
                if ($medium = $this->getAnalyticsConfig('medium', $storeId)) {
                    $suffix .= '&utm_medium=' . $medium;
                }
                if ($name = $this->getAnalyticsConfig('name', $storeId)) {
                    $suffix .= '&utm_campaign=' . $name;
                }
                if ($term = $this->getAnalyticsConfig('term', $storeId)) {
                    $suffix .= '&utm_term=' . $term;
                }
                if ($content = $this->getAnalyticsConfig('content', $storeId)) {
                    $suffix .= '&utm_content=' . $content;
                }
            }

            $this->urlSuffix[$storeId] = $suffix;
        }

        return $this->urlSuffix[$storeId];
    }

    /**
     * Get Coupon Config
     *
     * @param string $code
     * @param null $storeId
     * @return mixed
     */
    public function getCouponConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/coupon' . $code, $storeId);
    }
}
