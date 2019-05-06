<?php

namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Customformfield\Edit\Renderer;

/**
* CustomFormField Customformfield field renderer
*/
class Checkboxwithtextbox extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
    * Get the after element html.
    *
    * @return mixed
    */
    public function getBeforeElementHtml()
    {
        $customDiv = '<div style="display: flex; justify-content: center; align-items: center;"><input type="checkbox" name="'.$this->getCheckboxName().'" '.$this->isCheckboxChecked().' value="'.$this->getValue().'" /><input style="margin-left: 15px;" id="'.$this->getTextboxName().'" name="'.$this->getTextboxName().'" value="'.$this->getTextboxValue().'" type="text" class="input-text admin__control-text '.$this->getClassNames().'" '.$this->isTextboxReadonly().'></div>';
        return $customDiv;
    }

    public function getLabelHtml($idSuffix = '', $scopeLabel = '')
    {
        $scopeLabel = $scopeLabel ? ' data-config-scope="' . $scopeLabel . '"' : '';

        if ($this->getImageLabel() !== null) {
            $html = '<label class="label admin__field-label" for="' .
                $this->getHtmlId() . $idSuffix . '"' . $this->_getUiId(
                    'label'
                ) . '><span' . $scopeLabel . '>' . 
                    '<img src="'. $this->getImageLabel() . '" alt="'.$this->getTitle().'"'
                 . '</span></label>' . "\n";
        } else if($this->getLabel() !== null) {
            $html = '<label class="label admin__field-label" for="' .
                $this->getHtmlId() . $idSuffix . '"' . $this->_getUiId(
                    'label'
                ) . '><span' . $scopeLabel . '>' . 
                    $this->getLabel()
                 . '</span></label>' . "\n";
        }
        else {
            $html = '';
        }
        return $html;
    }

    /**
     * Retrieve Element HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getBeforeElementHtml();
        $html .= $this->getAfterElementHtml();
        return $html;
    }

    protected function isCheckboxChecked()
    {
        if($this->getCheckboxValue()) {
            return 'checked="checked"';
        }

        return null;
    }

    protected function isTextboxReadonly()
    {
        if(!$this->getCheckboxValue()) {
            //return 'readonly="readonly"';
        }

        return null;
    }

    protected function getClassNames()
    {
        return $this->getClass();
    }
}