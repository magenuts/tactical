<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\Deliverydate\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface {

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->_addTypeDayColumn($setup);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function _addTypeDayColumn(SchemaSetupInterface $setup) {
        $table = $setup->getTable('mobicommerce_mobideliverydate_holidays');
        $setup->getConnection()
              ->addColumn(
                    $table,
                    'type_day',
                    [
                        'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'nullable'  => false,
                        'default'   => '0',
                        'comment'   => 'Day type'
                    ]);
    }
}