<?php
namespace Mobicommerce\Mobiservices3\Model;

class AbstractModel extends \Magento\Framework\Model\AbstractModel {

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
		\Magento\Framework\Event\ManagerInterface $eventManager,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
		\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		array $data = []
	)
	{
		$this->storeManager = $storeManager;
		$this->eventManager = $eventManager;

		parent::__construct($context, $registry, $resource, $resourceCollection, $data);
	}

	public function successStatus($success = ['SUCCESS'])
	{
		return [
		'status'  => 'SUCCESS',
		'message' => $success,
		];
	}

	public function errorStatus($error = ['0','opps! unknown Error '])
	{
		return [
		'status' => 'FAIL',
		'message' => is_array($error)?$error[0]:$error,
		];
	}

	public function checkUserLoginSession(){
		return $this->customerSession->isLoggedIn();
	}

	public function _getStoreId(){
		return $this->storeManager->getStore()->getStoreId();
	}

	public function _getStoreName(){
		return $this->storeManager->getStore()->getName();
	}

	public function _getWebsiteId(){
		return $this->storeManager->getWebsite()->getWebsiteId();
	}

	public function eventChangeData($name_event, $value){
		$this->eventManager->dispatch($name_event, $value);
	}

	public function getModel($modelPath)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$connector = $objectManager->create('\Mobicommerce\Mobiservices3\Block\Connector');
		$model = $objectManager->create($connector->_getConnectorModel($modelPath));
		return $model;
	}

	public function getCoreModel($modelPath)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		/** @var \Magento\Catalog\Model\ResourceModel\Product\Collection
		$productCollection */
		$model = $objectManager->create($modelPath);
		/** Apply filters here */
		return $model;
	}

	public function getCoreHelper($helperPath)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		/** @var \Magento\Catalog\Model\ResourceModel\Product\Collection
		$productCollection */
		$helper = $objectManager->create($helperPath);
		/** Apply filters here */
		return $helper;
	}

	public function getMobiHelper($helperPath)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		/** @var \Magento\Catalog\Model\ResourceModel\Product\Collection
		$productCollection */
		$helper = $objectManager->create($helperPath);
		/** Apply filters here */
		return $helper;
	}

	public function _getUrl($url, $params = [])
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		/** @var \Magento\Catalog\Model\ResourceModel\Product\Collection
		$productCollection */
		$urlInterface = $objectManager->create('\Magento\Framework\UrlInterface');
		/** Apply filters here */
		return $urlInterface->getUrl($url, $params);
	}
}