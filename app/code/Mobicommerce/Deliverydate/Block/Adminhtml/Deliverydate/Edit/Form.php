<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Block\Adminhtml\Deliverydate\Edit;

use Magento\Backend\Block\Widget\Form as WidgetForm;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Mobicommerce\Deliverydate\Helper\Data
     */
    protected $deliveryHelper;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Mobicommerce\Deliverydate\Helper\Data $deliveryHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->deliveryHelper = $deliveryHelper;
        $this->coreRegistry = $coreRegistry;
        $this->date = $date;
        parent::__construct($context, $registry, $formFactory, $data);
    }


    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('edit_form');
        $this->setTitle(__('Edit'));
    }

    /**
     * @return WidgetForm
     */
    protected function _prepareForm()
    {
        $orderId = $this->coreRegistry->registry('current_order')->getId();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('mobicommerce_deliverydate/*/save', ['order_id' => $orderId]),
                    'method' => 'post',
                ],
            ]
        );

        $storeId = $this->_storeManager->getStore(true)->getStoreId();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Delivery Date')]);

        $fieldset->addField('date', 'Mobicommerce\Deliverydate\Block\Adminhtml\Sales\Order\Renderer\Date', array(
            'label'    => __('Delivery Date'),
            'title'    => __('Delivery Date'),
            'name'     => 'date',
            'format'       => $this->scopeConfig->getValue('mobideliverydate/date_field/format'),
            'required'     => $this->scopeConfig->getValue('mobideliverydate/date_field/required'),
            'date_format'  => $this->scopeConfig->getValue('mobideliverydate/date_field/format'),
            'min_date'     => $this->date->date($this->deliveryHelper->getPhpFormat())
        ));

        $fieldset->addField('clear', 'checkbox', array(
            'label'    => __('Reset Delivery Date Value'),
            'title'    => __('Reset Delivery Date Value'),
            'name'     => 'clear'
        ));

        if ($this->scopeConfig->getValue('mobideliverydate/time_field/enabled_time')) {
            $options = $this->deliveryHelper->getTIntervals($storeId);
            if (!empty($options)) {
                $fieldset->addField('tinterval_id', 'select', array(
                    'label'    => __('Delivery Time Interval'),
                    'title'    => __('Delivery Time Interval'),
                    'name'     => 'tinterval_id',
                    'required' => $this->scopeConfig->getValue('mobideliverydate/time_field/required'),
                    'values'   => $options
                ));
            }
        }

        if ($this->scopeConfig->getValue('mobideliverydate/comment_field/enabled_comment')) {
            $fieldset->addField('comment', 'textarea', array(
                'label'    => __('Delivery Comments'),
                'title'    => __('Delivery Comments'),
                'name'     => 'comment',
                'required' => $this->scopeConfig->getValue('mobideliverydate/comment_field/required'),
            ));
        }

        $fieldset->addField('notify', 'checkbox', array(
            'label'    => __('Notify Customer by Email'),
            'title'    => __('Notify Customer by Email'),
            'name'     => 'notify'
        ));

        $data = $this->coreRegistry->registry('current_mobicommerce_deliverydate')->getData();

        if (array_key_exists('date', $data)
            && ('0000-00-00' == $data['date']
                || '1970-01-01' == $data['date'])) {
            unset($data['date']);
        }

        $form->setValues($data);


        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}