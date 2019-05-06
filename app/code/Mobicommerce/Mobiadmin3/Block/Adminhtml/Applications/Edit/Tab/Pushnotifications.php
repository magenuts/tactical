<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit\Tab;

class Pushnotifications extends \Magento\Backend\Block\Widget\Form\Generic {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory
     */
    protected $mobiadmin3ResourceAppsettingCollectionFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;
    protected $_systemStore;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory $mobiadmin3ResourceAppsettingCollectionFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore
    ) {
        $data = [];
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->mobiadmin3ResourceAppsettingCollectionFactory = $mobiadmin3ResourceAppsettingCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_systemStore = $systemStore;
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

	   	$fieldset = $form->addFieldset('pushnotifications', ['legend'=> __('Push Notifications [WEBSITE]')]);

        $fieldset->addField(
            'pushnotifications_helptext',
            'note',
            [
                'name' => 'pushnotifications_helptext',
                'label' => '',
                'text' => 'Send push notifications to all app users. Enter the message and send to all customers using your app. Configure test message before sending to all customers on test devices and ensure that it is working fine and delivering correctly.',
                'bold' => true
            ]
        );
        
	   	$fieldset->addField(
            'pushnotifications_devicetype',
            'select',
            [
                'name' => 'push_device_type',
                'label' => __('Select Device'),
                'options' => [
                    'both' => __('Both'),
                    'android' => __('Android'),
                    'ios' => __('iOS'),
                ]
            ]
        );

        $fieldset->addField('push_store',
            'select',
            [
                'name' => 'push_store',
                'label' => __('Store View'),
                'title' => __('Store View'),
                'values' => $this->_systemStore->getStoreValuesForForm(false, true)
            ]
        );

        $fieldset->addField(
            'pushheading',
            'text',
            [
                'name' => 'pushheading',
                'label' => __('Heading'),
                'title' => __('Heading')
            ]
        );

        $fieldset->addField(
            'pushnotifications_message',
            'textarea',
            [
                'name' => 'pushnotifications',
                'label' => __('Message'),
                'title' => __('Message')
            ]
        );

        $fieldset->addField(
            'pushdeeplink',
            'text',
            [
                'name' => 'pushdeeplink',
                'label' => __('Deeplink'),
                'title' => __('Deeplink'),
                'readonly' => false
            ]
        );

        $deeplinkButton = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'label' => __('Assign Deeplink'),
                'onclick' => '
                    require([
                        "jquery",
                        "Magento_Ui/js/modal/modal"
                    ], function(jQuery, modal){
                        var _pushdeeplink_value = jQuery("input[name=pushdeeplink]").val();
                        new Ajax.Request("'.$this->getUrl('mobicommerce/widget/deeplink').'bannerid/pushdeeplink/link/"+_pushdeeplink_value, {
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
                                var popupdata = jQuery("<div id=\'modal_push_deeplink\' />").append(response.responseText);
                                modal(options, popupdata);
                                popupdata.modal("openModal").on("modalclosed", function() { 
                                    jQuery("#modal_push_deeplink").remove();
                                });
                            },
                        });
                    });
                    ',
                'class' => 'save',
            ]
        );

        $fieldset->addField('push_deeplink_button', 'note', [
            'text' => $deeplinkButton->toHtml()
        ]);

        $fieldset->addField(
            'pushfile',
            'file',
            [
                'name' => 'pushfile',
                'label' => __('Image'),
                'title' => __('Image'),
                'note' => __('Recommended size: 512px(w) x 256px(h) <br />Image support for Android only<br />Supported Filetypes: png, jpg, jpeg')
            ]
        );

        $field = $fieldset->addField(
            'push_device_type',
            'select',
            [
                'name' => 'whom',
                'label' => __('Send To'),
                'options' => [
                    'all' => __('All'),
                    'customer_group' => __('Customer Group'),
                    'specific_customer' => __('Specific Customer'),
                ],
                'onchange'  => "changeWhom(this.value);"
            ]
        );

        $field->setAfterElementHtml('
            <br />
            <div id="parent_customer_group"><div class="child_customer-grid" style="width: 800px"></div></div>
            <div id="parent_specific_customer"><div class="child_specificcustomer-grid" style="width: 800px"></div></div>
            <div id="parent_send_to_customer"><div class="child_send_to_customer-grid" style="width: 800px"></div></div>
            <script>
                function changeWhom(type) {
                    if(type == "customer_group"){
                        new Ajax.Request("'.$this->getUrl('mobicommerce/widget/customergrid').'", {
                            method: "Post",
                            parameters: {isAjax : 1},
                            onSuccess: function(response){
                                var json = response.responseText.evalJSON(true);
                                if(json.status == "success"){
                                    jQuery("#parent_specific_customer").hide();
                                    jQuery("#parent_customer_group").show();
                                    jQuery(".child_customer-grid").html(json.widget_customer_grid);
                                }
                                else{
                                    alert(json.error);
                                }
                            },
                            onFailure: function(response){
                                var json = response.responseText.evalJSON(true);
                                alert(json.error);
                            }
                        });
                    }else if(type == "specific_customer"){
                        new Ajax.Request("'.$this->getUrl('mobicommerce/widget/specificcustomergrid').'", {
                            method: "Post",
                            parameters: {isAjax : 1},
                            onSuccess: function(response){
                                var json = response.responseText.evalJSON(true);
                                if(json.status == "success"){
                                    jQuery("#parent_customer_group").hide();
                                    jQuery("#parent_specific_customer").show();
                                    jQuery(".child_specificcustomer-grid").html(json.widget_spacificcustomer_grid);
                                }
                                else{
                                    alert(json.error);
                                }
                            },
                            onFailure: function(response){
                                var json = response.responseText.evalJSON(true);
                                alert(json.error);
                            }
                        });
                    }else{
                        jQuery("#parent_specific_customer").hide();
                        jQuery("#parent_customer_group").hide();
                        jQuery(".child_customer-grid").html("");
                    }
                    jQuery(".child_send_to_customer-grid").empty();
                }

                function saveSpecificcustomer(e){
                    if(jQuery(e).is(":checked")) {
                        var specifcustomerid = jQuery(e).val();
                        var custpos = jQuery(e).parent("td").parent("tr").find(".cust_position").val();
                        var checked = "1";
                    } else { 
                        var specifcustomerid = jQuery(e).val();
                        var custpos = jQuery(e).parent("td").parent("tr").find(".cust_position").val();
                        var checked = "0";
                    }
                    
                    new Ajax.Request("'.$this->getUrl('mobicommerce/widget/checkspecificcustomer').'", {
                        method: "Post",
                        parameters: {customerid:specifcustomerid, custpos:custpos, checked : checked},
                        onSuccess: function(response){
                            customer = response.responseText;
                            var a = jQuery.parseJSON(customer);
                            a = a["user-info"];
                            var customers = "";
                            jQuery(a).each(function( index, value ) {
                                customers += \'<span class="popOver">\'+ value.name +\'<b onclick="removeUser(\'+value.id+\')">X</b></span>\';
                            });
                            jQuery(".send_to_customer-grid").html(customers); 
                            jQuery(".selectedcustomer").val(response.responseText);
                        },
                        onFailure: function(response){
                            alert(json.error);
                        }
                    });
                }

                function saveCustomer(e){
                    if(jQuery(e).is(":checked")) {
                        var customerid = jQuery(e).val();
                        var customerpos = jQuery(e).parent("td").parent("tr").find(".cust_position").val();
                        var checked = "1";
                    } else { 
                        var customerid = jQuery(e).val();
                        var customerpos = jQuery(e).parent("td").parent("tr").find(".cust_position").val();
                        var checked = "0";
                    }
                    
                    new Ajax.Request("'.$this->getUrl('mobicommerce/widget/checkcustomer').'", {
                        method: "Post",
                        parameters: {custId:customerid, customerpos:customerpos, checked : checked},
                        onSuccess: function(response){
                            customer = response.responseText;
                            var group = jQuery.parseJSON(customer);
                            group = group["user-info"];
                            var grcustomers = "";
                            jQuery(group).each(function( index, value ) {
                                grcustomers += \'<span class="popOver">\'+ value.name +\'<b onclick="removeGrUser(\'+value.id+\')">X</b></span>\';
                            });
                            jQuery(".send_to_customer-grid").html(grcustomers);           
                            jQuery(".selectedcustomersId").val(response.responseText);
                        },
                        onFailure: function(response){              
                            alert(json.error);
                        }
                    });
                }

                function removeUser(id){
                    var checked = "0";
                    var custpos = null;
                    
                    new Ajax.Request("'.$this->getUrl('mobicommerce/widget/checkspecificcustomer').'", {
                        method: "Post",
                        parameters: {customerid:id, custpos:custpos, checked : checked},
                        onSuccess: function(response){
                            customer = response.responseText;
                            var a = jQuery.parseJSON(customer);
                            a = a["user-info"];
                            var customers = "";
                            jQuery(a).each(function( index, value ) {
                                customers += \'<span class="popOver">\'+ value.name +\'<b onclick="removeUser(\'+value.id+\')">X</b></span>\';
                            });
                            jQuery("input:checkbox[value=\'\" + id + \"\']").attr("checked", false);
                            jQuery(".send_to_customer-grid").html(customers); 
                            jQuery(".selectedcustomer").val(response.responseText);
                        },
                        onFailure: function(response){
                            alert(json.error);
                        }
                    });
                }

                function removeGrUser(id){
                    var customerid = id;
                    var customerpos = null;
                    var checked = "0";
                    
                    new Ajax.Request("'.$this->getUrl('mobicommerce/widget/checkcustomer').'", {
                        method: "Post",
                        
                        parameters: {custId:customerid, customerpos:customerpos, checked : checked},
                        onSuccess: function(response){
                            customer = response.responseText;
                            var group = jQuery.parseJSON(customer);
                            group = group["user-info"];
                            var grcustomers = "";
                            jQuery(group).each(function( index, value ) {
                                grcustomers += \'<span class="popOver">\'+ value.name +\'<b onclick="removeGrUser(\"\'+value.id+\'\")">X</b></span>\';
                            });
                            jQuery(".send_to_customer-grid").html(grcustomers);
                            jQuery("input:checkbox[value=\'\" + id + \"\']").attr("checked", false);
                            jQuery(".selectedcustomersId").val(response.responseText);
                        },
                        onFailure: function(response){
                            alert(json.error);
                        }
                    });
                }
            </script>
        ');
        
       	return parent::_prepareForm();
   	}
}