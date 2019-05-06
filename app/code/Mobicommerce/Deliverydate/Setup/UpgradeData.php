<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\Deliverydate\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Upgrade Data script
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if ($context->getVersion() && version_compare($context->getVersion(), '1.3.0', '<')) {
            $oldQuota = $this->scopeConfig->getValue('mobideliverydate/general/shipping_quota');
            if ($oldQuota) {
                $this->configWriter->delete('mobideliverydate/general/shipping_quota');
                $this->configWriter->save('mobideliverydate/quota/per_day', $oldQuota);
            }
            $oldQuota = $this->scopeConfig->getValue('mobideliverydate/general/tinterval_quota');
            if ($oldQuota) {
                $this->configWriter->delete('mobideliverydate/general/tinterval_quota');
                $this->configWriter->save('mobideliverydate/quota/tinterval_quota', $oldQuota);
            }
        }

        $setup->endSetup();
    }
}
