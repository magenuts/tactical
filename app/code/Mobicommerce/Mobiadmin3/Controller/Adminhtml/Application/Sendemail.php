<?php

namespace Mobicommerce\Mobiadmin3\Controller\Adminhtml\Application;

class Sendemail extends \Magento\Backend\App\Action {
	/**
	* @var \Magento\Framework\Mail\Template\TransportBuilder
	*/
	protected $_transportBuilder;

	/**
	* @var \Magento\Framework\Translate\Inline\StateInterface
	*/
	protected $inlineTranslation;
	protected $_authSession;
	/**
	* @param \Magento\Framework\App\Action\Context $context
	* @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
	* @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
	*/
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		\Magento\Framework\Escaper $escaper,
		\Magento\Backend\Model\Auth\Session $authSession
		) {
		parent::__construct($context);
		$this->_transportBuilder = $transportBuilder;
		$this->inlineTranslation = $inlineTranslation;
		$this->_authSession = $authSession;
	}

	/**
	* Post user question
	*
	* @return void
	* @throws \Exception
	*/
	public function execute()
	{
		$post = $this->getRequest()->getPostValue();
		if (!$post) {
			$this->_redirect('*/*/');
			return;
		}

		$this->inlineTranslation->suspend();
		try {
			$postObject = new \Magento\Framework\DataObject();
			$postObject->setData($post);

			$error = false;

			$admin_user = $this->_authSession->getUser();
			$sender = [
			'name' => $admin_user->getFirstname(),
			'email' => $admin_user->getEmail(),
			];

			$transport = $this->_transportBuilder
			->setTemplateIdentifier('email_template_mobicommerce_sendqrcode') // this code we have mentioned in the email_templates.xml
			->setTemplateOptions([
				'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
				'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
			])
			->setTemplateVars(['data' => $postObject])
			->setFrom($sender)
			->addTo($post['emailid'])
			->getTransport();
			$transport->sendMessage(); ;
			$this->inlineTranslation->resume();
			/*
			$this->messageManager->addSuccess(
			__('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
			);
			*/
			$response = ['status' => 'success', 'success' => __('You will receive an email shortly. Thanks')];
		} catch (\Exception $e) {
			$this->inlineTranslation->resume();
			/*
			$this->messageManager->addError(
			__('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
			);
			*/
			$response = ['status' => 'fail', 'error' => __('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())];
		}

		echo json_encode($response);exit;
	}
}