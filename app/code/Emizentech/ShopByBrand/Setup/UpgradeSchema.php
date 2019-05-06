<?php

namespace Emizentech\ShopByBrand\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();
   
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
          $installer->getConnection()->addColumn(
                $installer->getTable('emizentech_shopbybrand_items'),
                'description',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '2M',
                    'nullable' => true,
                    'comment' => 'Description'
                ]
            );
        }
        $installer->endSetup();
    }
}

?>