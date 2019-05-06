<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\Deliverydate\Block\Adminhtml\Sales\Order\Create;

use Mobicommerce\Deliverydate\Model\DeliverydateFactory;
use Magento\Framework\View\Element\Template\Context;

class Deliverydate extends \Magento\Framework\View\Element\Template
{

    /**
     * @var DeliverydateFactory
     */
    protected $deliveryDateFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    /**
     * @var \Mobicommerce\Deliverydate\Helper\Data
     */
    protected $deliveryHelper;
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Mobicommerce\Deliverydate\Model\ResourceModel\Deliverydate
     */
    protected $deliverydateResourceModel;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $sessionQuote;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    public function __construct(
        Context $context,
        DeliverydateFactory $deliveryDateFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Mobicommerce\Deliverydate\Helper\Data $deliveryHelper,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Mobicommerce\Deliverydate\Model\ResourceModel\Deliverydate $deliverydateResourceModel,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        array $data = []
    ) {
        $this->deliveryDateFactory = $deliveryDateFactory;
        $this->coreRegistry = $coreRegistry;
        $this->deliveryHelper = $deliveryHelper;
        $this->sessionQuote = $sessionQuote;
        $this->deliverydateResourceModel = $deliverydateResourceModel;

        parent::__construct($context, $data);
        $this->formFactory = $formFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->date = $date;
    }

    protected function _construct()
    {
        parent::_construct();

        $deliveryDate = $this->deliveryDateFactory->create();
        $orderId = 0;
        if ($this->sessionQuote->getOrderId()) { // edit order
            $orderId = $this->sessionQuote->getOrderId();
        } elseif ($this->sessionQuote->getReordered()) { // reorder
            $orderId = $this->sessionQuote->getReordered();
        }

        if($orderId) {
            $this->deliverydateResourceModel->load($deliveryDate, $orderId, 'order_id');
            $this->coreRegistry->register('current_deliverydate', $deliveryDate);
        }

        $this->setTemplate('Mobicommerce_Deliverydate::delivery_create.phtml');
    }

    public function getFormElements()
    {
        $form = $this->formFactory->create();
        $form->setHtmlIdPrefix('mobicommerce_deliverydate_');
        $availableFields = $this->deliveryHelper->whatShow('order_create');
        $storeId = $this->_storeManager->getStore(true)->getStoreId();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Delivery Date')]);

        if (in_array('date', $availableFields)) {
            $date = $fieldset->addField('date', 'Mobicommerce\Deliverydate\Block\Adminhtml\Sales\Order\Renderer\Date', [
                    'label'        => __('Delivery Date'),
                    'name'         => 'mobideliverydate[date]',
                    'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                    'style'        => 'width: 40%',
                    'format'       => $this->deliveryHelper->getDefaultScopeValue('date_field/format'),
                    'required'     => false,
                    'date_format'  => $this->deliveryHelper->getDefaultScopeValue('date_field/format'),
                    'min_date'     => $this->date->date($this->deliveryHelper->getPhpFormat())
                ]
            );
        }

        if ($this->scopeConfig->getValue('mobideliverydate/time_field/enabled_time')
            && in_array('time', $availableFields)) {
            $options = $this->deliveryHelper->getTIntervals($storeId);
            $fieldset->addField('tinterval_id', 'select', [
                    'label'    => __('Delivery Time Interval'),
                    'name'     => 'mobideliverydate[tinterval_id]',
                    'style'    => 'width: 40%',
                    'required' => false,
                    'options'  => $options
                ]
            );
        }

        if ($this->scopeConfig->getValue('mobideliverydate/comment_field/enabled_comment')
            && in_array('comment', $availableFields)) {
            $fieldset->addField('comment', 'textarea', [
                    'label'    => __('Delivery Comments'),
                    'title'    => __('Delivery Comments'),
                    'name'     => 'mobideliverydate[comment]',
                    'required' => false,
                    'style'    => 'width: 40%',
                ]
            );
        }

        if ($deliveryDate = $this->coreRegistry->registry('current_deliverydate')) {
            $data = $deliveryDate->getData();
            if (isset($data['date']) && '0000-00-00' == $data['date']) {
                $data['date'] = '';
            }
            $form->setValues($data);
        }

        return $form->getElements();
    }
}