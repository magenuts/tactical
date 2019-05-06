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

namespace Mageplaza\AbandonedCart\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Token
 * @package Mageplaza\AbandonedCart\Model
 */
class Token
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param ResourceConnection $resource
     */
    public function __construct(
        DateTime $date,
        ResourceConnection $resource
    )
    {
        $this->resource = $resource;
        $this->date     = $date;
    }

    /**
     * @param int $quoteId
     * @param string $configId
     * @param string $token
     * @return void
     */
    public function saveToken($quoteId, $configId, $token)
    {
        $bind = [
            'quote_id'                  => $quoteId,
            'config_id'                 => $configId,
            'checkout_token'            => $token,
            'checkout_token_created_at' => $this->date->date()
        ];
        $this->resource->getConnection()->insert($this->resource->getTableName('mageplaza_abandonedcart_logs_token'), $bind);
    }

    /**
     * @param int|null $quoteId
     * @param string|null $token
     * @return bool
     */
    public function validateCartLink($quoteId = null, $token = null)
    {
        if ($quoteId == null || $token == null) {
            return false;
        }
        $connection = $this->resource->getConnection();
        $select     = $connection->select()
            ->from($this->resource->getTableName('mageplaza_abandonedcart_logs_token'))
            ->where('checkout_token = :checkout_token')
            ->where('quote_id = :quote_id');
        $bind       = [
            'checkout_token' => $token,
            'quote_id'       => $quoteId
        ];
        $result     = $connection->fetchOne($select, $bind);
        if (isset($result) && !empty($result)) {
            return true;
        }

        return false;
    }

    /**
     * @param int $quoteId
     * @param string $configId
     * @return bool
     */
    public function validateEmail($quoteId, $configId)
    {
        $connection = $this->resource->getConnection();
        $select     = $connection->select()
            ->from($this->resource->getTableName('mageplaza_abandonedcart_logs_token'))
            ->where('config_id = :config_id')
            ->where('quote_id = :quote_id');

        $result = $connection->fetchOne($select, ['config_id' => $configId, 'quote_id' => $quoteId]);
        if (isset($result) && !empty($result)) {
            return false;
        }

        return true;
    }
}
