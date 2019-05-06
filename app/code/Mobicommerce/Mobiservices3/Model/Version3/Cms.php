<?php
namespace Mobicommerce\Mobiservices3\Model\Version3;

class Cms extends \Mobicommerce\Mobiservices3\Model\AbstractModel {

	public function _getCmsdata($data, $store_id = false)
	{
		return $this->getMobiHelper('\Mobicommerce\Mobiservices3\Helper\Cache')->getCacheData('cms', $data['appcode']);
	}

	public function getCmsdata($data, $store_id = false)
	{
		$info = $this->successStatus();
		$info['data']['CMS'] = $this->_getCmsdata($data);
		return $info;
	}

	public function getCmsDetail($data)
	{
		$page_id = isset($data['page_id']) ? $data['page_id'] : null;
		$collection = $this->getCoreModel('\Magento\Cms\Model\Page')->load($page_id);
		$detail = $collection->getData();
		if(!empty($detail)){
			$info = $this->successStatus();
			$info['data']['detail'] = [
				'page_id' => $detail['page_id'],
                'title'   => $detail['title'],
                'content' => $this->getCoreHelper('Magento\Cms\Model\Template\FilterProvider')->getBlockFilter()
        			->setStoreId($this->storeManager->getStore()->getId())->filter($detail['content'])
				];
			return $info;
		}
		else
			return $this->errorStatus('oops');
	}
}