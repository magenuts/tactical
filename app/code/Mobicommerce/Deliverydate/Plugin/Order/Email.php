<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Plugin\Order;

class Email
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    /**
     * @var \Mobicommerce\Deliverydate\Helper\Data
     */
    protected $deliveryHelper;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Email constructor.
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Registry         $coreRegistry
     * @param \Mobicommerce\Deliverydate\Helper\Data    $deliveryHelper
     * @param \Psr\Log\LoggerInterface            $logger
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $coreRegistry,
        \Mobicommerce\Deliverydate\Helper\Data $deliveryHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->coreRegistry = $coreRegistry;
        $this->deliveryHelper = $deliveryHelper;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Sales\Block\Items\AbstractItems $subject
     * @param string                                   $result  HTML
     *
     * @return string
     */
    public function afterToHtml(\Magento\Sales\Block\Items\AbstractItems $subject, $result)
    {
        $addToResult = '';

        if ($subject->getOrder() && $subject->getOrder()->getId()) {
            $deliveryDateFields = '';
            if ($subject instanceof \Magento\Sales\Block\Order\Email\Invoice\Items) {
                $deliveryDateFields = $this->deliveryHelper->whatShow('invoice_email', 'include');
            } elseif ($subject instanceof \Magento\Sales\Block\Order\Email\Shipment\Items) {
                $deliveryDateFields = $this->deliveryHelper->whatShow('shipment_email', 'include');
            } elseif ($subject instanceof \Magento\Sales\Block\Order\Email\Items) {
                $deliveryDateFields = $this->deliveryHelper->whatShow('order_email', 'include');
            }

            if ($deliveryDateFields) {
                try {
                    $addToResult = $subject->getLayout()
                        ->createBlock(
                            'Mobicommerce\Deliverydate\Block\Sales\Order\Email\Deliverydate',
                            'deliverydate_info',
                            [
                                'data' => [
                                    'order_id' => $subject->getOrder()->getId(),
                                    'fields'   => $deliveryDateFields
                                ]
                            ]
                        )
                        ->toHtml();

                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->logger->error($e->getLogMessage());
                }
            }
        }

        return $addToResult . $result;
    }
}
