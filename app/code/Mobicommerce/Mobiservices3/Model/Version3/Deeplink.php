<?php
//namespace \Mobicommerce\Mobiservices3\Model;
namespace Mobicommerce\Mobiservices3\Model\Version3;


class Deeplink extends \Mobicommerce\Mobiservices3\Model\AbstractModel {

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    protected $_urlRewriteFactory;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,      
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\UrlRewrite\Model\UrlRewrite $urlRewriteFactory
    )
    {
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->_urlRewriteFactory = $urlRewriteFactory;
        parent::__construct($context, $registry, $storeManager, $eventManager);
    }

	public function getInfo($data)
    {
        $url = $data['url'];        
        $base_url = $this->storeManager->getStore()->getBaseUrl();
        $path = str_replace($base_url, '', $url);
        
        $rewrite = $this->_urlRewriteFactory->setStoreId($this->storeManager->getStore()->getId())->setRequestPath($path);

        $result = [
            'type' => false,
            'id' => null
            ];
        $record_found = false;
        if($rewrite->getProductId()) {
            $record_found = true;
            $result['type'] = 'product';
            $result['id'] = $rewrite->getProductId();
        }
        else if($rewrite->getCategoryId()) {
            $record_found = true;
            $result['type'] = 'category';
            $result['id'] = $rewrite->getCategoryId();
        }
        else {
            $cms = $this->getCoreModel('\Magento\Cms\Model\Page')->load($path, 'identifier');
            if($cms) {
                $record_found = true;
                $result['type'] = 'cms';
                $result['id'] = $cms->getIdentifier();
            }
        }

        $info = $this->successStatus();
        $info['data']['response'] = $result;
        return $info;
    }
}