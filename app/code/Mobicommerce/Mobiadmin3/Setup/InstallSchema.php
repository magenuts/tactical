<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mobicommerce\Mobiadmin3\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'mobicommerce_applications3'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('mobicommerce_applications3'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )            
            ->addColumn(
                'app_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => 'simple'],
                'App Name'
            )
            ->addColumn(
                'app_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => 'simple'],
                'App Code'
            )
            ->addColumn(
                'app_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => 'simple'],
                'App Key'
            )
            ->addColumn(
                'app_license_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => 'simple'],
                'License Key'
            )
            ->addColumn(
                'app_storegroupid',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => '0'],
                'Store Group ID'
            )
            ->addColumn(
                'created_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Created Time'
            )
            ->addColumn(
                'update_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Updated Time'
            )
            ->addColumn(
                'app_mode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => 'demo'],
                'License Version'
            )
            ->addColumn(
                'android_url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Android URL'
            )
            ->addColumn(
                'android_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => true, 'default' => ''],
                'Android Status'
            )
            ->addColumn(
                'ios_url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'iOS URL'
            )
            ->addColumn(
                'ios_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => true, 'default' => ''],
                'iOS Status'
            )
            ->addColumn(
                'udid',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '1M',
                ['nullable' => false, 'default' => ''],
                'UDID'
            )
            ->addColumn(
                'delivery_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => true, 'default' => ''],
                'Deleivery Status'
            )
            ->addColumn(
                'addon_parameters',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '1M',
                ['nullable' => false, 'default' => ''],
                'AddOn Parameters'
            )
            ->addColumn(
                'version_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                45,
                ['nullable' => true, 'default' => ''],
                'PRO OR LITE'
            )
            ->setComment('Mobicommerce Applications Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'mobicommerce_applications_settings3'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('mobicommerce_applications_settings3'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'app_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'App Code'
            )
            ->addColumn(
                'storeid',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'setting_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => false, 'default' => ''],
                'Setting Code'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Value'
            )
            ->setComment('Application Settings');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'mobicommerce_category_icon3'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('mobicommerce_category_icon3'))
            ->addColumn(
                'mci_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'mci_category_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['nullable' => true],
                'Mobi Icone Category ID'
            )
            ->addColumn(
                'mci_thumbnail',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Thumbnail icone'
            )
            ->addColumn(
                'mci_banner',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Entity ID'
            )           
            ->setComment('Mobi Category Custom Icone');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'mobicommerce_category_widget3'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('mobicommerce_category_widget3'))
            ->addColumn(
                'widget_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Widget ID'
            )
            ->addColumn(
                'widget_category_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['nullable' => true, 'default' => null],
                'Category ID'
            )
            ->addColumn(
                'widget_label',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Lable'
            )
            ->addColumn(
                'widget_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Widget Code'
            )
            ->addColumn(
                'widget_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Widget Status'
            )
            ->addColumn(
                'widget_position',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Widget Position'
            )
            ->addColumn(
                'widget_data',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Widget Data'
            )           
            ->setComment('Mobi Category Widget');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'mobicommerce_devicetokens'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('mobicommerce_devicetokens'))
            ->addColumn(
                'md_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'md_appcode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                45,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'App Code'
            )
            ->addColumn(
                'md_userid',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['nullable' => true],
                'User ID'
            )
            ->addColumn(
                'md_store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Store ID'
            )
            ->addColumn(
                'md_enable_push',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Enable Push'
            )
            ->addColumn(
                'md_devicetype',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                ['nullable' => false, 'default' => ''],
                'Device Type'
            )
            ->addColumn(
                'md_devicetoken',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Device Token'
            )
            ->addColumn(
                'md_created_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Created Date'
            )            
            ->setComment('MobiCommerce Devicetokens');
        $installer->getConnection()->createTable($table);
        
        /**
         * Create table 'catalog_product_entity_text'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('mobicommerce_licence'))
            ->addColumn(
                'ml_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'ml_licence_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Licence Key'
            )
            ->addColumn(
                'ml_debugger_mode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                ['nullable' => false, 'default' => 'yes'],
                'Mode'
            )
            ->addColumn(
                'ml_installation_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Installation Date'
            )            
            ->setComment('Mobi Licence');
        $installer->getConnection()->createTable($table);
       
        
        /**
         * Create table 'mobicommerce_notification'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('mobicommerce_notification'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                20,
                ['nullable' => false],
                'Type'
            )
            ->addColumn(
                'date_added',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Date Added'
            )
            ->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '1M',
                ['nullable' => false],
                'Message'
            )  
            ->addColumn(
                'read_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false,'default'=>'0'],
                'Read Status'
            )            
            ->setComment('Mobi Notification');
        $installer->getConnection()->createTable($table);
        
        /**
         * Create table 'mobicommerce_pushhistory'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('mobicommerce_pushhistory'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'appcode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                25,
                ['nullable' => true],
                'App Code'
            )
            ->addColumn(
                'date_submitted',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Date Submitted'
            )
            ->addColumn(
                'date_to_send',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Date to Send'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Store Id'
            )  
            ->addColumn(
                'device_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                ['nullable' => true],
                'App Code'
            )
            ->addColumn(
                'heading',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => true],
                'Heading'
            )
            ->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Message'
            )
            ->addColumn(
                'deeplink',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Deeplink'
            )
            ->addColumn(
                'image',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Image'
            )
            ->addColumn(
                'send_to_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,'default'=>"all"],
                'Send To Type'
            )
            ->addColumn(
                'send_to',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Read Status'
            )            
            ->setComment('Mobi Pushhistory');
        $installer->getConnection()->createTable($table);
        
        /**
         * Create table 'mobicommerce_widget3'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('mobicommerce_widget3'))
            ->addColumn(
                'widget_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Widget ID'
            )
            ->addColumn(
                'widget_app_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Widget App Code'
            )
            ->addColumn(
                'widget_store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Widget Store Id'
            )
            ->addColumn(
                'widget_label',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Thumbnail icone'
            )
            ->addColumn(
                'widget_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Thumbnail icone'
            )
            ->addColumn(
                'widget_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Widget Status'
            )
            ->addColumn(
                'widget_position',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Widget Position'
            )
            ->addColumn(
                'widget_data',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                ['nullable' => false],
                'Widget Data'
            )           
            ->setComment('Mobi Widget');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
