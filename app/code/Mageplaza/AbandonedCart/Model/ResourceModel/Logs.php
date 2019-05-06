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

namespace Mageplaza\AbandonedCart\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone;

/**
 * Class Logs
 * @package Mageplaza\AbandonedCart\Model\ResourceModel
 */
class Logs extends AbstractDb
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $timeZone;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timeZone
     */
    public function __construct(
        Context $context,
        DateTime $date,
        Timezone $timeZone
    )
    {
        $this->date               = $date;
        $this->timeZone           = $timeZone;
        $this->resourceConnection = $context->getResources();

        parent::__construct($context);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('mageplaza_abandonedcart_logs', 'id');
    }

    /**
     * @inheritdoc
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }
        $object->setUpdatedAt($this->date->date());

        return parent::_beforeSave($object);
    }

    /**
     * @param $quoteId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateRecovery($quoteId)
    {
        $bind  = ['recovery' => true];
        $where = ['quote_id = ?' => $quoteId];
        $this->getConnection()->update($this->getMainTable(), $bind, $where);
    }

    /**
     *
     * @param string $date
     * @return string
     */
    private function convertDate($date)
    {
        return $this->date->date('Y-m-d H:i:s', strtotime($date));
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @param null $dimension
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadReportData($fromDate, $toDate, $dimension = null)
    {
        $result       = [];
        $timeZoneFrom = $this->timeZone->date($fromDate);
        $timeZoneTo   = $this->timeZone->date($toDate);
        $timeDiff     = $timeZoneFrom->diff($timeZoneTo);
        if ($dimension == 'month') {
            $numbers = $timeDiff->m;
            $level   = ' month';
            if ($numbers == 0 && $this->date->date('m', $fromDate) != $this->date->date('m', $toDate)) {
                $numbers = 1;
            }
        } else {
            $level   = ' days';
            $numbers = $timeDiff->days;
        }
        for ($number = 0; $number <= $numbers; $number++) {
            if ($dimension == 'month') {
                $fromDate = $this->date->date('m/01/Y', $fromDate);
            }
            $date       = $this->date->date('m/d/Y', $fromDate . '+' . $number . $level);
            $nextDate   = $this->date->date('m/d/Y', $date . '+1' . $level);
            $dateFormat = $date;
            if ($dimension == 'month') {
                $date       = $this->date->date('m/01/Y', $date);
                $dateFormat = $this->date->date('m/Y', $date);
            }
            $result[] = [
                $dateFormat,
                $this->getAbandonedCart($date, $nextDate),
                $this->getLogData($date, $nextDate),
                $this->getLogData($date, $nextDate, 'recovery'),
                $this->getLogData($date, $nextDate, 'error')
            ];
        }

        return $result;
    }

    /**
     * @param string $date
     * @param string $nextDate
     * @return int
     */
    private function getAbandonedCart($date, $nextDate)
    {
        $adapter  = $this->resourceConnection->getConnection();
        $select   = $adapter->select()
            ->from($this->resourceConnection->getTableName('quote'))
            ->where('(created_at >= ? AND updated_at = "0000-00-00 00:00:00") OR updated_at >= ?', $this->convertDate($date))
            ->where('(created_at < ? AND updated_at = "0000-00-00 00:00:00") OR updated_at < ?', $this->convertDate($nextDate))
            ->where('is_active = ?', true)
            ->where('items_count > ?', 0)
            ->where('customer_email != ?', null);
        $quoteIds = $adapter->fetchAll($select);

        return count($quoteIds);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function clear()
    {
        $bind = ['display' => false];
        $this->getConnection()->update($this->getMainTable(), $bind);
    }

    /**
     * @param $date
     * @param $nextDate
     * @param null $column
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getLogData($date, $nextDate, $column = null)
    {
        $adapter = $this->resourceConnection->getConnection();
        $select  = $adapter->select()
            ->from($this->getMainTable())
            ->where('updated_at >= ?', $this->convertDate($date))
            ->where('updated_at < ?', $this->convertDate($nextDate));
        if ($column == 'recovery') {
            $select->group('quote_id')->where('recovery = ?', true);
        }
        if ($column == 'error') {
            $select->where('status = ?', false);
        }
        $collection = $adapter->fetchCol($select);

        return count($collection);
    }
}
