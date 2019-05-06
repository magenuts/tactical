<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Plugin\Order;

use Mobicommerce\Deliverydate\Model\Config\Source\IncludeInto;

/**
 * Insert Delivery Date information Block to Invoice Admin PDF
 */
class InvoicePdf extends \Mobicommerce\Deliverydate\Plugin\AbstractPdf
{
    /**
     * @param \Magento\Sales\Model\Order\Pdf\Invoice  $subject
     * @param \Magento\Sales\Model\Order\Invoice[] $shipments
     *
     * @return array
     */
    public function beforeGetPdf($subject, $shipments = [])
    {
        // just for note. All code content in abstract.
        return parent::beforeGetPdf($subject, $shipments);
    }

    /**
     * @param \Magento\Sales\Model\Order\Pdf\Invoice $subject
     * @param \Zend_Pdf_Page                          $page
     * @param string                                  $text
     *
     * @return array
     */
    public function beforeInsertDocumentNumber($subject, $page, $text)
    {
        // just for note. All code content in abstract
        return parent::beforeInsertDocumentNumber($subject, $page, $text);
    }

    protected function getWhatShow()
    {
        return $this->deliveryHelper->whatShow(IncludeInto::INVOICE_PDF, 'include');
    }

    /**
     * {@inheritdoc}
     */
    protected function getPhrasePrefix()
    {
        return __('Invoice # ');
    }
}
