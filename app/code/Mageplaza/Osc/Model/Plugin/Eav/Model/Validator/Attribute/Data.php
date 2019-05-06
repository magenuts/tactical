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

namespace Mageplaza\Osc\Model\Plugin\Eav\Model\Validator\Attribute;

class Data extends \Magento\Eav\Model\Validator\Attribute\Data
{
    /**
     * @var \Mageplaza\Osc\Helper\Data
     */
    protected $_oscHelperData;

    /**
     * Data constructor.
     * @param \Magento\Eav\Model\AttributeDataFactory $attrDataFactory
     * @param \Mageplaza\Osc\Helper\Data $oscHelperData
     */
    public function __construct(
        \Magento\Eav\Model\AttributeDataFactory $attrDataFactory,
        \Mageplaza\Osc\Helper\Data $oscHelperData
    )
    {
        parent::__construct($attrDataFactory);
        $this->_oscHelperData = $oscHelperData;
    }

    /**
     * @param \Magento\Eav\Model\Validator\Attribute\Data $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsValid(\Magento\Eav\Model\Validator\Attribute\Data $subject, $result)
    {
        if ($this->_oscHelperData->isFlagOscMethodRegister()) {
            $subject->_messages = [];

            return count($subject->_messages) == 0;
        }

        return $result;
    }
}