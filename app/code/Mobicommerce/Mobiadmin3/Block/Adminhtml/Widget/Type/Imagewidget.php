<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Type;

class Imagewidget extends \Magento\Backend\Block\Widget\Form\Generic {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;
    protected $_backendSession;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $data = [];
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->request = $request;
        parent::__construct($context, $registry, $formFactory, $data);

        $this->_backendSession = $backendSession;
    }
    
    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }
    
    protected function _prepareForm()
   	{
       	$form = $this->formFactory->create();
       	$this->setForm($form);
        
        $widgetdata = $this->registry->registry('widgetdata');
        $widget_image = '';
        $mapcode = '';
        $map_coords = [];
        $map_href = [];

        if($widgetdata) {
            $data = @unserialize($widgetdata['widget_data']);
            $widget_image = $data['widget_image'];
            $mapcode = $data['mapcode'];
            
            $regex = "/<area(.*)>/";
            $matches = explode('<area', htmlspecialchars_decode($mapcode));
            if(!empty($matches)){
                for($i=0; $i<(count($matches) - 1); $i++){
                    $_match = $matches[$i];
                    if(!empty($_match)){
                        $start = strpos($_match, 'coords="');
                        $end = strpos($_match, '" title=');
                        if($start !== FALSE && $end !== FALSE){
                            $href_start = strpos($_match, 'href="');
                            $href_end = strpos($_match, '" target=');

                            $map_coords[] = substr($_match, $start + 8, $end - 8 - $start);
                            $map_href[] = substr($_match, $href_start + 6, $href_end - 6 - $href_start);
                        }
                    }
                }
            }
            $map_coords = array_unique($map_coords);
            $map_href = array_unique($map_href);
        }
        $map_coords = implode('__SEPRATER__', $map_coords);
        $map_href = implode('__SEPRATER__', $map_href);

	   	$fieldset = $form->addFieldset('widget_image_fieldset', ['legend'=> __('Image Widget')]);

        if($widgetdata) {
            $fieldset->addField(
                'widget_id',
                'hidden',
                [
                    'name' => 'widget_id',
                    'label' => __('ID'),
                    'value' => $widgetdata['widget_id']
                ]
            );
        }
        
	   	$fieldset->addField(
            'widget[name]',
            'text',
            [
                'name' => 'widget[name]',
                'label' => __('Name'),
                'value' => isset($widgetdata['widget_label']) ? $widgetdata['widget_label'] : '',
                'required' => true
            ]
        );

        $fieldset->addField(
            'widget[widget_data][title]',
            'text',
            [
                'name' => 'widget[widget_data][title]',
                'label' => __('Title'),
                'value' => isset($data['title']) ? $data['title'] : '',
            ]
        );

        $fieldset->addField(
            'widget[widget_data][title_align]',
            'select',
            [
                'name' => 'widget[widget_data][title_align]',
                'label' => __('Align Title'),
                'values' => [
                    ['label' => 'Center', 'value' => 'center'],
                    ['label' => 'Left', 'value' => 'left'],
                    ['label' => 'Right', 'value' => 'right']
                ],
                'value' => isset($data['title_align']) ? $data['title_align'] : ''
            ]
        );

        $fieldset->addField(
            'widget[enable]',
            'select',
            [
                'name' => 'widget[enable]',
                'label' => __('Status'),
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                    ],
                'value' => isset($data['widget_status']) ? $data['widget_status'] : '1'
            ]
        );

        $fieldset->addField(
            'widget_image_hidden',
            'hidden',
            [
                'name' => 'widget[widget_data][widget_image]',
                'label' => '',
                'value' => $widget_image
            ]
        );

        $fieldset->addField(
            'mapcode',
            'hidden',
            [
                'name' => 'widget[widget_data][mapcode]',
                'label' => '',
                'value' => $mapcode
            ]
        );

        $field = $fieldset->addField(
            'widget_image',
            'file',
            [
                'name' => 'widget_image',
                'label' => __('Image')
            ]
        );

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $formKeyObject = $objectManager->get('Magento\Framework\Data\Form\FormKey');

        $field->setAfterElementHtml('
            <br /><br /><br />
            <iframe class="image-map-iframe" height="100%" scrolling="yes" src="" style="width:700px; height:100%; background:white;-webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box; border:0; min-height: 500px;">
            </iframe>
            <script>
                require([
                    "jquery"
                ], function(jQuery){
                    var widget_image = "'.$widget_image.'";
                    if(widget_image != "") {
                        var src ="'.$this->getUrl('mobicommerce/widget/imagemap').'?image_url="+widget_image+"&map_coords='.$map_coords.'&map_href='.$map_href.'";
                        var iframe = jQuery(".image-map-iframe");
                        iframe.attr("src",src);
                    }

                    jQuery("input[name=widget_image]").on("change", function(){
                        bindWidgetImageClick();
                    });
                });

                function bindWidgetImageClick()
                {
                    var iframe = jQuery(".image-map-iframe");
                    var file_data = jQuery("input[name=widget_image]").prop("files")[0];
                    if(file_data["name"] != ""){
                        var form_data = new FormData();
                        form_data.append("file", file_data);
                        form_data.append("isAjax", "1");
                        form_data.append("form_key", "'.$formKeyObject->getFormKey().'");
                        
                        jQuery.ajax({
                            url: "'.$this->getUrl('mobicommerce/widget/uploadajaximage').'",
                            showLoader: true,
                            type: "post",  
                            contentType: false,
                            processData: false,
                            data: form_data,
                            dataType: "json",
                            success: function(response) {
                                jQuery("#widget_image_hidden").val(response.image_url);
                                var src ="'.$this->getUrl('mobicommerce/widget/imagemap').'?image_url="+response.image_url;
                                iframe.attr("src",src);
                            },
                            error: function() {
                                alert("fail");
                            }
                        });
                    }
                }

                function deeplinkImageWidget()
                {
                    require([
                        "jquery",
                        "Magento_Ui/js/modal/modal"
                    ], function(jQuery, modal){
                        var bannerid = "linkURL";
                        var linkval = jQuery(".image-map-iframe").contents().find("#linkURL").val();
                        
                        new Ajax.Request("'.$this->getUrl('mobicommerce/widget/deeplink').'bannerid/"+bannerid+"/link/"+linkval, {
                            method: "Post",
                            parameters: {isAjax : 1},
                            onSuccess: function(response){
                                var options = {
                                    type: "slide",
                                    responsive: true,
                                    innerScroll: true,
                                    title: jQuery.mage.__("Deeplink"),
                                    buttons: [{
                                        text: jQuery.mage.__("Insert Link"),
                                        class: "button primary",
                                        click: function () {
                                            if(savedeeplink()) {
                                                this.closeModal();
                                            }
                                        }
                                    }]
                                };

                                var popupdata = jQuery("<div id=\'modal_image_widget_deeplink\' />").append(response.responseText);
                                modal(options, popupdata);
                                popupdata.modal("openModal").on("modalclosed", function() { 
                                    jQuery("#modal_image_widget_deeplink").remove();
                                });
                            }
                        });
                    });
                }
            </script>
            ');
        
       	return parent::_prepareForm();
   	}
}