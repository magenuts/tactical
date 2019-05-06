<?php
namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Application;
use Magento\Store\Model\Group;

class Labelsmessages extends \Magento\Backend\App\Action
{
	protected $_storeManager;
	protected $_resultPageFactory;
	protected $mobiadmin3Helper;
	protected $request;
	protected $_dir;

	/**
	* @var \Magento\Framework\App\Config\ScopeConfigInterface
	*/
	protected $scopeConfig;        

	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Mobicommerce\Mobiadmin3\Helper\Data $mobiadmin3Helper,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\Filesystem\DirectoryList $dir,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	) {
		parent::__construct($context);
		$this->_storeManager = $storeManager;
		$this->_resultPageFactory = $resultPageFactory;
		$this->mobiadmin3Helper = $mobiadmin3Helper;
		$this->request = $request;
		$this->_dir = $dir;  
		$this->scopeConfig = $scopeConfig;    
	}

	public function execute()
	{
		$this->mobiadmin3Helper->getMobicommercePrerequisites();
		$locale = $this->request->getParam('lang_code');
		$post = $this->request->getPost();
		if($post['form_key']){
			$languageData = $post['language_data'];

			$xml = $this->_dir->getUrlPath("media").'/mobi_assets/multilanguage/v3/'.$locale.'.xml';
			$xmldata = simplexml_load_file($xml);

			$childxml = $this->_dir->getUrlPath("media").'/mobi_commerce/multilanguage/v3/'.$locale.'.xml';
			$doc = new \DOMDocument('1.0');
			$doc->formatOutput = true;
			$root = $doc->createElement('mobicommerce_multilanguage');
			$root = $doc->appendChild($root);

			foreach($xmldata as $_key => $_data){
				$optioncodenode = $doc->createElement($_key);
				$newdoc = $root->appendChild($optioncodenode);

				$em = $doc->createElement('mm_text');
				$text = $doc->createTextNode(isset($languageData[$_key]) ? $languageData[$_key] : $_data->mm_text);
				$em->appendChild($text);
				$newdoc->appendChild($em);
			}
			$doc->save($childxml);

			$this->mobiadmin3Helper->setLanguageCodeData($locale);
			$resultPage = $this->_resultPageFactory->create();
			$resultPage->setActiveMenu('Mobicommerce_Mobiadmin3::applicationLabelsmessages');
			
			return $resultPage;
		} else {
			if($locale){
				$this->mobiadmin3Helper->setLanguageCodeData($locale);
				$resultPage = $this->_resultPageFactory->create();
				$resultPage->setActiveMenu('Mobicommerce_Mobiadmin3::applicationLabelsmessages');
				return $resultPage;
			}
			else{
				$storearray = [];
				$_websites = $this->_storeManager->getWebsites();
				foreach ($_websites as $website){
					foreach ($website->getGroups() as $group){
						$stores = $group->getStores();
						foreach ($stores as $store){
							$storearray[] = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());
						}
					}
				}

				$url = $this->getUrl('mobicommerce/application/labelsmessages', ['lang_code' => $storearray[0]]);

				$resultRedirect = $this->resultRedirectFactory->create();
				$resultRedirect->setPath($url);
				return $resultRedirect;
			}
		}
	}
}