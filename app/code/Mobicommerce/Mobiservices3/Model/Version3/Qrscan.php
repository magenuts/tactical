<?php
//namespace \Mobicommerce\Mobiservices3\Model;
namespace Mobicommerce\Mobiservices3\Model\Version3;

class Qrscan extends \Mobicommerce\Mobiservices3\Model\AbstractModel {

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,      
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager
    )
    {
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        
        parent::__construct($context, $registry, $storeManager, $eventManager);
        $this->getModel('Mobicommerce\Mobiservices3\Model\User')->autoLoginMobileUser();
    }

    public function getScanInfo($data)
    {
        $scan_value = trim($data['scan_value']);
        if(empty($scan_value)){
            return $this->errorStatus("invalid_qrcode");
        }
        
        $storeId = $this->storeManager->getStore()->getId();
        $product = $this->getCoreModel('Magento\Catalog\Model\ResourceModel\Product\Collection')->addAttributeToSelect('*')
            ->addAttributeToFilter([
                ['attribute'=>'sku', 'eq' => $scan_value],
                ['attribute'=>'url_key', 'eq' => $scan_value],
                ])
            ->addAttributeToFilter('status', '1')
            ->addAttributeToFilter('visibility', '4')
            ->setStoreId($storeId)
            ->getFirstItem();
        //echo $product->getId();exit;
        if ($product->getId()){
            $productData['id'] = $product->getId();
            $info = $this->successStatus();
            
            $product_data = $this->getModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog')->productInfo(['product_id' => $product->getId()]);

            $info['data']['type'] = 'product';
            $info['data']['id'] = $product->getId();
            $info['data']['product_details'] = $product_data['data']['product_details'];
        } else {
            $category = $this->getCoreModel('\Magento\Catalog\Model\ResourceModel\Category\Collection')->addAttributeToSelect('*')
                ->addAttributeToFilter('url_key', $scan_value)
                ->setStoreId($storeId)
                ->getFirstItem();
            if ($category->getId()) {
                $productData['id'] = $category->getId();
                $info = $this->successStatus();
                $info['data']['category_id'] = $category->getId();

                $categories = $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('category', $data['appcode']);
                $categories = $this->getModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog')->getNlevelCateories($categories, $category->getId());

                $info['data']['type'] = 'category';
                $info['data']['id'] = $category->getId();
                $info['data']['categories'] = $categories;
            } else {
                $info = $this->errorStatus('invalid_qrcode');
            }
        }
        return $info;
    }
}