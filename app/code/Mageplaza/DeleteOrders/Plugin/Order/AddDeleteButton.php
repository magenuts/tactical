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
 * @package     Mageplaza_DeleteOrder
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DeleteOrders\Plugin\Order;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View;
use Mageplaza\DeleteOrders\Helper\Data;

/**
 * Class AddDeleteButton
 * @package Mageplaza\DeleteOrders\Plugin\Order
 */
class AddDeleteButton
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var AuthorizationInterface
     */
    protected $_authorization;

    /**
     * AddDeleteButton constructor.
     * @param Data $helper
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        Data $helper,
        AuthorizationInterface $authorization
    )
    {
        $this->helper         = $helper;
        $this->_authorization = $authorization;
    }

    /**
     * @param View $object
     * @param LayoutInterface $layout
     * @return array
     */
    public function beforeSetLayout(View $object, LayoutInterface $layout)
    {
        if ($this->helper->isEnabled() && $this->_authorization->isAllowed('Magento_Sales::delete')) {
            $object->addButton(
                'order_delete',
                [
                    'label'          => __('Delete'),
                    'class'          => 'delete',
                    'id'             => 'order-view-delete-button',
                    'data_attribute' => [
                        'url'     => $object->getDeleteUrl()
                    ]
                ]);
        }

        return [$layout];
    }

    /**
     * @param View $object
     * @param $result
     * @return string
     */
    public function afterGetFormScripts(View $object, $result)
    {
        if ($this->helper->isEnabled() && $this->_authorization->isAllowed('Magento_Sales::delete')) {
            $message = __('Are you sure you want to delete this order?');
            $result .= <<<SCRIPT
<script type="text/javascript">
    require(['jquery', 'Magento_Ui/js/modal/confirm'], function ($, confirm) {
        'use strict';
    
        $('#order-view-delete-button').click(function () {
            var url = $(this).data('url');
    
            confirm({
                'content': '$message',
                'actions': {
                    confirm: function () {
                        $('<form>', {'action': url, 'method': 'POST'})
                        .append($('<input>', {'name': 'form_key', 'value': window.FORM_KEY, 'type': 'hidden'}))
                        .appendTo('body')
                        .submit();
                    }
                }
            });
    
            return false;
        });
    });
</script>
SCRIPT;
        }

        return $result;
    }
}