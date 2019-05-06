<?php
namespace Mobicommerce\Mobiservices3\Helper;

class Mobicommerce extends \Magento\Framework\App\Helper\AbstractHelper {

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    protected $_dir;

    /* function to remove entire directory with all files in it */
    //protected $mobiConnector;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        $this->storeManager = $storeManager;
        $this->_dir = $dir;
        parent::__construct($context);
    }

    public function getCoreHelper($helperPath)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $helper = $objectManager->create($helperPath);
        /** Apply filters here */
        return $helper;
    }

    public function rrmdir($dir, $include_basedir = true)
    {
        if(is_dir($dir)){
            $objects = scandir($dir);
            foreach($objects as $object){
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
                } 
            }
            reset($objects); 
            if($include_basedir)
                rmdir($dir); 
        } 
    }

    public function getProductPriceByCurrency($price=null)
    {
        return $this->getCoreHelper('Magento\Framework\Pricing\Helper\Data')->currency($price, false, false);
    }

    /**
     * Check to see if mobile version is supported or not
     */
    public function isMobileVersionSupported()
    {
        $supportedVersions = ["3.0.0"];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connector = $objectManager->create('\Mobicommerce\Mobiservices3\Block\Connector');
        $version = $connector->_getConnectorVersion();
        if(in_array($version, $supportedVersions))
            return true;
        else
            return false;
    }

    public function addImageRatio(&$product)
    {
        $urls = [
            'widget_image',
            'banner_url',
            'full_image_url',
            'image_url',
            'thumbnail_url',
            'product_image',
            'product_small_image_url',
            'product_image_url',
            'product_thumbnail_url'
            ];

        foreach($urls as $_url) {
            if(isset($product[$_url])) {
                $product['ratio_'.$_url] = $this->getImageRatio($product[$_url]);
            }
        }

        return $product;
    }

    public function getImageRatio($url)
    {
        $width = 1;
        $height = 1;
        if(!empty($url)) {
            $path = str_replace($this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA), $this->_dir->getPath("media").'/', $url);
        
            if(file_exists($path) && !is_dir($path)) {
                list($width, $height, $type, $attr) = @getimagesize($path);
            }
        }

        $ratio = round($height / $width, 2);
        return "1:$ratio";
    }
}