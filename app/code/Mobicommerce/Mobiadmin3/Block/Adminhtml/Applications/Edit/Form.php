<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic {

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected function _prepareForm()
	{
     	$form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'enctype'=> 'multipart/form-data',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
            ]
        );
		$form->setUseContainer(true);
		$this->setForm($form);
		return parent::_prepareForm();
	}
}