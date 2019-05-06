<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\Deliverydate\Block\Sales\Order\Info;

class Deliverydate extends \Mobicommerce\Deliverydate\Block\Sales\Order\Email\Deliverydate
{
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('Mobicommerce_Deliverydate::info.phtml');
    }

    public function getFields()
    {
        return $this->deliveryHelper->whatShow('order_info');
    }

    /**
     * @return string
     */
    public function getEditUrl()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->getUrl('mobicommerce_deliverydate/deliverydate/edit', ['order_id' => $this->getOrderId()]);
        }
        return $this->getUrl('mobicommerce_deliverydate/guest/edit', ['order_id' => $this->getOrderId()]);
    }

    /**
     * @param string $field code
     *
     * @return bool
     */
    public function isFieldEditable($field)
    {
        if ($field == 'date') {
            return $this->getDeliveryDate()->isCanEditOnFront();
        }

        return false;
    }
}
