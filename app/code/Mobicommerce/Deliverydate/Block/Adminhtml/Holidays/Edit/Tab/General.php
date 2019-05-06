<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\Deliverydate\Block\Adminhtml\Holidays\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class General extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Mobicommerce\Deliverydate\Helper\Data
     */
    protected $mobiHelper;

    /**
     * General constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param \Magento\Store\Model\System\Store       $systemStore
     * @param \Mobicommerce\Deliverydate\Helper\Data        $mobiHelper
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Mobicommerce\Deliverydate\Helper\Data $mobiHelper,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->mobiHelper = $mobiHelper;
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('General');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_mobicommerce_deliverydate_holidays');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('mobicommerce_deliverydate_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Properties')]);

        if ($model->getId()) {
            $fieldset->addField('holiday_id', 'hidden', ['name' => 'holiday_id']);
        }

        if ($this->_storeManager->isSingleStoreMode()) {
            $storeId = $this->_storeManager->getStore(true)->getStoreId();
            $fieldset->addField('store_ids', 'hidden', ['name' => 'store_ids[]', 'value' => $storeId]);
            $model->setStoreIds($storeId);
        } else {
            $field = $fieldset->addField(
                'store_ids',
                'multiselect',
                [
                    'name'     => 'store_ids[]',
                    'label'    => __('Stores'),
                    'title'    => __('Stores'),
                    'values'   => $this->_systemStore->getStoreValuesForForm(false, true),
                    'required' => true,
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        }

        $fieldset->addField(
            'type_day',
            'select',
            [
                'label' => __('Day type'),
                'title' => __('Day type'),
                'name'  => 'type_day',
                'values' => $this->mobiHelper->getTypeDay()
            ]
        );

        $fieldset->addField(
            'day',
            'select',
            [
                'label' => __('Day'),
                'title' => __('Day'),
                'name' => 'day',
                'values' => $this->mobiHelper->getDays()
            ]
        );

        $fieldset->addField(
            'month',
            'select',
            [
                'label' => __('Month'),
                'title' => __('Month'),
                'name' => 'month',
                'values' => $this->mobiHelper->getMonths(true)
            ]
        );

        $fieldset->addField(
            'year',
            'select',
            [
                'label' => __('Year'),
                'title' => __('Year'),
                'name' => 'year',
                'values' => $this->mobiHelper->getYears(true)
            ]
        );

        $fieldset->addField(
            'description',
            'text',
            [
                'label' => __('Description'),
                'title' => __('Description'),
                'name' => 'description'
            ]
        );

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
