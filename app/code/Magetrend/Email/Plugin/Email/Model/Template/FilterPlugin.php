<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

// @codingStandardsIgnoreFile

namespace Magetrend\Email\Plugin\Email\Model\Template;

use Magento\Email\Model\Template\Filter;

class FilterPlugin
{
    /**
     * @var \Magetrend\Email\Helper\Data
     */
    protected $_mtHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $_redirectFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * PreviewPlugin constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magetrend\Email\Helper\Data $helper
     * @param \Magento\Framework\ObjectManagerInterface $objectManagerInterface
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magetrend\Email\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\App\State $state
    ) {
        $this->_mtHelper = $helper;
        $this->_registry = $registry;
        $this->_objectManager = $objectManagerInterface;
        $this->_redirectFactory = $redirectFactory;
        $this->_logger = $loggerInterface;
        $this->_blockFactory = $blockFactory;
        $this->_appState = $state;
    }

    /**
     * @param Filter $filter
     * @param $value
     */
    public function beforeFilter(Filter $filter, $value)
    {
        if ($this->_mtHelper->isActive() || !$this->isAllowToProcess()) {
            $this->_registry->unregister('mtemail_template_filter_value');
            $this->_registry->register('mtemail_template_filter_value', $value);
        }
    }

    /**
     * @param Filter $filter
     * @param $value
     * @return \Magento\Framework\Phrase|mixed|string
     */
    public function afterFilter(Filter $filter, $value)
    {
        if (!$this->_mtHelper->isActive() || !$this->isAllowToProcess() || $this->isEditMode()) {
            return $value;
        }

        try {
            $origValue = $this->_registry->registry('mtemail_template_filter_value');
            $value = $this->prepareTemplateBefore($value, $origValue);
            $value = $this->applyCustomFilter($value);
            $this->_registry->register('mtemail_template_filter_disable', 1);
            $value = $filter->filter($value);
            $this->_registry->unregister('mtemail_template_filter_disable');
        } catch (\Exception $e) {
            if ($this->_appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
                $value = sprintf(__('Error filtering template: %s'), $e->getMessage());
            } else {
                $value = __("We're sorry, an error has occurred while generating this email.");
            }
            $this->_logger->critical($e);
        }
        return $value;
    }

    /**
     * Edit mode means variable will be not processed
     * @return bool
     */
    protected function isEditMode()
    {
        if ($this->_registry->registry('mt_editor_edit_mode') == 1) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function isAllowToProcess()
    {
        if ($this->_registry->registry('mtemail_template_filter_disable') == 1) {
            return false;
        }
        return true;
    }

    /**
     * These filters will be applied after template rendering.
     *
     * @param $value
     * @return mixed
     */
    protected function applyCustomFilter($value)
    {
        $value = $this->applyCustomBlockFilter($value);
        return $value;
    }

    /**
     * Custom changes in template before filter.
     *
     * @param $value
     * @param $origValue
     * @return mixed
     */
    protected function prepareTemplateBefore($value, $origValue)
    {
        if (!$this->isSubjectFilter($origValue)) {
            $value = $this->addBody($value);
        }
        return $value;
    }

    /**
     * This method will add <body> tag to the email content
     *
     * @param $value
     * @return mixed
     */
    protected function addBody($value)
    {
        if (substr_count($value, '<body>') == 0) {
            $value = $this->_blockFactory->createBlock('Magento\Framework\View\Element\Template')
                ->setTemplate('Magetrend_Email::email/body.phtml')
                ->setContent($value)
                ->setArea('frontend')
                ->toHtml();
        }

        return $value;
    }

    /**
     * It will replace {{block }} variable to block html output
     * Variable format: {{block type='cms/block' block_id=1}}
     *
     * @param $value
     * @return mixed
     */
    protected function applyCustomBlockFilter($value)
    {
        if (substr_count($value, '{{block ') > 0) {
            $tmpString = explode('{{block ', $value);
            $i = 0;
            foreach ($tmpString as $tmpValue) {
                //skip the first
                if ($i == 0) {
                    $i = 1;
                    continue;
                }
                if (substr_count($tmpValue, '}}') > 0) {
                    $tmpString2 = explode('}}', $tmpValue);
                    if (substr_count($tmpString2[0], 'type=') == 1 &&substr_count($tmpString2[0], 'block_id=') == 1) {
                        $find = '{{block '.$tmpString2[0].'}}';

                        $tmpString3 = explode(' ', $tmpString2[0]);
                        foreach ($tmpString3 as $params) {
                            if (substr_count($params, 'type=') == 1) {
                                $blockType = str_replace(['type=', '"', "'"], '', $params);
                            } elseif (substr_count($params, 'block_id=') == 1) {
                                $blockId = str_replace(['block_id=', '"', "'"], '', $params);
                            }
                        }

                        if (!empty($blockType)) {
                            $replaceBlock = $this->_blockFactory->createBlock($blockType);
                            if (!empty($blockId)) {
                                $replaceBlock->setBlockId($blockId);
                            }
                            $replace = $replaceBlock->toHtml();
                            $value = str_replace($find, $replace, $value);
                        };
                    }
                }
            }
        }
        return $value;
    }

    /**
     * Returns: Is content a subject?
     * @param string $origValue
     * @return bool
     */
    protected function isSubjectFilter($origValue)
    {
        if (substr_count($origValue, '{{layout') > 0) {
            return false;
        }
        return true;
    }
}
