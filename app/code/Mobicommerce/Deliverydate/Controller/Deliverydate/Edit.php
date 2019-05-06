<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Controller\Deliverydate;

use Mobicommerce\Deliverydate\Model\DeliverydateRepository;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\AbstractController\OrderViewAuthorization;
use Magento\Sales\Model\OrderRepository;

class Edit extends \Magento\Framework\App\Action\Action
{
    /**
     * @var DeliverydateRepository
     */
    private $deliverydateRepository;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var \Mobicommerce\Deliverydate\Helper\Data
     */
    private $deliveryHelper;

    /**
     * @var OrderViewAuthorization
     */
    private $orderAuthorization;

    /**
     * Edit constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param DeliverydateRepository              $deliverydateRepository
     * @param Registry                            $coreRegistry
     * @param PageFactory                         $resultPageFactory
     * @param OrderViewAuthorization              $orderAuthorization
     * @param OrderRepository                     $orderRepository
     * @param \Mobicommerce\Deliverydate\Helper\Data    $deliveryHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        DeliverydateRepository $deliverydateRepository,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        OrderViewAuthorization $orderAuthorization,
        OrderRepository $orderRepository,
        \Mobicommerce\Deliverydate\Helper\Data $deliveryHelper
    ) {
        parent::__construct($context);
        $this->deliverydateRepository = $deliverydateRepository;
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;
        $this->deliveryHelper = $deliveryHelper;
        $this->orderAuthorization = $orderAuthorization;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if (!$orderId) {
            return $this->_forward('noroute');
        }
        try {
            $deliverydate = $this->deliverydateRepository->getByOrder($orderId);
            $order        = $this->orderRepository->get($orderId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $this->_redirect('sales/order/view', ['order_id' => $orderId]);
        }

        if (!$this->orderAuthorization->canView($order) || !$deliverydate->isCanEditOnFront()) {
            return $this->_redirect('sales/order/history');
        }

        $this->coreRegistry->register('current_mobicommerce_deliverydate', $deliverydate);
        $this->coreRegistry->register('current_order', $order);

        $resultPage = $this->resultPageFactory->create();
        /** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/order/history');
        }

        $title = __('Edit Delivery Date For The Order #%1', $order->getIncrementId());
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
