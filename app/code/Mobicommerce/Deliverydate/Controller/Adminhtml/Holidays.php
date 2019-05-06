<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */


namespace Mobicommerce\Deliverydate\Controller\Adminhtml;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Psr\Log\LoggerInterface;
use Magento\Backend\Model\Session;

abstract class Holidays extends \Magento\Backend\App\Action
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
     * @var \Mobicommerce\Deliverydate\Model\ResourceModel\Holidays
     */
    protected $resourceModel;
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;
    /**
     * @var \Mobicommerce\Deliverydate\Model\ResourceModel\Holidays\Collection
     */
    protected $collection;
    /**
     * @var \Mobicommerce\Deliverydate\Model\HolidaysFactory
     */
    protected $model;

    /**
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        LoggerInterface $logInterface,
        \Mobicommerce\Deliverydate\Model\HolidaysFactory $model,
        \Mobicommerce\Deliverydate\Model\ResourceModel\Holidays $resourceModel,
        \Mobicommerce\Deliverydate\Model\ResourceModel\Holidays\Collection $collection,
        \Magento\Ui\Component\MassAction\Filter $filter
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->logInterface = $logInterface;
        $this->session = $context->getSession();
        $this->resourceModel = $resourceModel;
        $this->filter = $filter;
        $this->collection = $collection;
        $this->model = $model;
    }

    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mobicommerce_Deliverydate::deliverydate_holidays');
        $resultPage->addBreadcrumb(__('Manage Exceptions: Working Days and Holidays'), __('Manage Exceptions: Working Days and Holidays'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Exceptions: Working Days and Holidays'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mobicommerce_Deliverydate::deliverydate_holidays');
    }
}
