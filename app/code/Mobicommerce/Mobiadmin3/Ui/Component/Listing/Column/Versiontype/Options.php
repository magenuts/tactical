<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mobicommerce\Mobiadmin3\Ui\Component\Listing\Column\Versiontype;

/**
 * Store Options for Cms Pages and Blocks
 */
class Options implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->options = $objectManager->create('Mobicommerce\Mobiadmin3\Model\Config\Source\MobiversionType')->toOptionArray();
        
        return $this->options;
    }
}
