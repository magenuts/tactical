<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Controller\Adminhtml;

use Magento\Backend\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Psr\Log\LoggerInterface;

abstract class Dinterval extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var LoggerInterface
     */
    protected $logInterface;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Mobicommerce\Deliverydate\Model\ResourceModel\Dinterval
     */
    protected $resourceModel;

    /**
     * @var \Mobicommerce\Deliverydate\Model\ResourceModel\Dinterval\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Mobicommerce\Deliverydate\Model\DintervalFactory
     */
    protected $model;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        LoggerInterface $logInterface,
        \Mobicommerce\Deliverydate\Model\DintervalFactory $model,
        \Mobicommerce\Deliverydate\Model\ResourceModel\Dinterval $resourceModel,
        \Mobicommerce\Deliverydate\Model\ResourceModel\Dinterval\Collection $collection,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->logInterface = $logInterface;
        $this->session = $context->getSession();
        $this->resourceModel = $resourceModel;
        $this->collection = $collection;
        $this->filter = $filter;
        $this->model = $model;
        $this->date = $date;
    }

    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mobicommerce_Deliverydate::deliverydate_dinterval');
        $resultPage->addBreadcrumb(__('Exceptions: Date Intervals'), __('Exceptions: Date Intervals'));
        $resultPage->getConfig()->getTitle()->prepend(__('Exceptions: Date Intervals'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mobicommerce_Deliverydate::deliverydate_dinterval');
    }
}
