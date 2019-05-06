<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\Deliverydate\Controller\Adminhtml\Dinterval;

use Mobicommerce\Deliverydate\Controller\Adminhtml\Dinterval\Index;
use Magento\Framework\Controller\ResultFactory;

class MassDelete extends Index
{
    /**
     * @return mixed
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collection);
        $collectionSize = $collection->getSize();

        foreach ($collection as $model) {
            $model->delete();
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}