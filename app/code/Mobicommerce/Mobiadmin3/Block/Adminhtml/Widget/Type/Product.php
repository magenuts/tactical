<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Type;

class Product extends \Magento\Backend\Block\Widget\Form\Generic {

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
        $ids = '';

        $product_ids = [];
        if($widgetdata) {
            $data = @unserialize($widgetdata['widget_data']);
            $widget_status = $widgetdata['widget_status'];
            $widget_label = $widgetdata['widget_label'];
            $ids = $data['products'];
            $product_ids = json_decode($ids, true);
        }
        
        $this->_backendSession->setData('checked_products', $product_ids);

	   	$fieldset = $form->addFieldset('widget_product', ['legend'=> __('Product Widget')]);

        if($widgetdata) {
            $fieldset->addField(
                'widget_id',
                'hidden',
                [
                    'name'  => 'widget_id',
                    'label' => __('ID'),
                    'value' => $widgetdata['widget_id']
                ]
            );
        }
        
	   	$fieldset->addField(
            'widget[name]',
            'text',
            [
                'name'     => 'widget[name]',
                'label'    => __('Name'),
                'value'    => isset($widgetdata['widget_label']) ? $widgetdata['widget_label'] : '',
                'required' => true
            ]
        );

        $fieldset->addField(
            'widget[widget_data][title]',
            'text',
            [
                'name'  => 'widget[widget_data][title]',
                'label' => __('Title'),
                'value' => isset($data['title']) ? $data['title'] : '',
            ]
        );

        $fieldset->addField(
            'widget[widget_data][title_align]',
            'select',
            [
                'name'   => 'widget[widget_data][title_align]',
                'label'  => __('Align Title'),
                'values' => [
                    ['label' => 'Center', 'value' => 'center'],
                    ['label' => 'Left', 'value' => 'left'],
                    ['label' => 'Right', 'value' => 'right']
                ],
                'value' => isset($data['title_align']) ? $data['title_align'] : ''
            ]
        );

        $fieldset->addField(
            'widget[widget_data][type]',
            'select',
            [
                'name'   => 'widget[widget_data][type]',
                'label'  => __('Type'),
                'values' => [
                    ['value' => 'grid', 'label' => 'Grid'],
                    ['value' => 'list', 'label' => 'List'],
                    ['value' => 'slider', 'label' => 'Slider'],
                    ['value' => 'full', 'label' => 'Full']
                ],
                'value' => isset($data['type']) ? $data['type'] : ''
            ]
        );

        $fieldset->addField(
            'widget[widget_data][maxItems]',
            'text',
            [
                'name'  => 'widget[widget_data][maxItems]',
                'label' => __('Products to show on Widget Page'),
                'value' => isset($data['maxItems']) ? $data['maxItems'] : 4,
            ]
        );

        $fieldset->addField(
            'widget[widget_data][limit]',
            'text',
            [
                'name'  => 'widget[widget_data][limit]',
                'label' => __('Maximum Number of products'),
                'value' => isset($data['limit']) ? $data['limit'] : 10,
            ]
        );

        $fieldset->addField(
            'widget[widget_data][show_name]',
            'radios',
            [
                'name'   => 'widget[widget_data][show_name]',
                'label'  => __('Show Name'),
                'values' => $this->getYesNoOptions(),
                'value'  => isset($data['show_name']) ? $data['show_name'] : '1',
                'note' => __('Not applicable to List view')
            ]
        );
        /*
        $fieldset->addField(
            'widget_widget_data_show_name_note',
            'note',
            [
                'name' => 'widget_widget_data_show_name_note',
                'label' => '',
                'text' => __('Not applicable to List view'),
                'bold' => true
            ]
        );
        */

        $fieldset->addField(
            'widget[widget_data][show_price]',
            'radios',
            [
                'name'   => 'widget[widget_data][show_price]',
                'label'  => __('Show Price'),
                'values' => $this->getYesNoOptions(),
                'value'  => isset($data['show_price']) ? $data['show_price'] : '1'
            ]
        );

        $fieldset->addField(
            'widget[widget_data][show_review]',
            'radios',
            [
                'name' => 'widget[widget_data][show_review]',
                'label' => __('Show Review'),
                'values' => $this->getYesNoOptions(),
                'value' => isset($data['show_review']) ? $data['show_review'] : '1'
            ]
        );

        $fieldset->addField(
            'productslider_type',
            'select',
            [
                'name' => 'widget[widget_data][productslider_type]',
                'label' => __('Type'),
                'values' => [
                    ['value' => '', 'label' => 'Select'],
                    ['value' => 'selected', 'label' => 'Selected Products'],
                    ['value' => 'newarrivals', 'label' => 'New Arrivals'],
                    ['value' => 'bestseller', 'label' => 'Best Seller'],
                    ['value' => 'productviewed', 'label' => 'Product Viewed']
                ],
                'value' => isset($data['productslider_type']) ? $data['productslider_type'] : '',
                'required' => true,
                'onchange' => 'changeProductSliderType()'
            ]
        );

        $field = $fieldset->addField(
            'widget[widget_data][products]',
            'hidden',
            [
                'name' => 'widget[widget_data][products]',
                'label' => '',
                'class' => 'selectedproducts',
                'value' => $ids
            ]
        );

        $field->setAfterElementHtml(
            '<br /><div class="product-grid" style="width: 800px;"></div>
            <script>
            function saveProduct(e){
                if(jQuery(e).is(":checked")) {
                    var productid = jQuery(e).val();
                    var productpos = jQuery(e).parent("td").parent("tr").find(".prod_position").val();
                    var checked = "1";
                } else { 
                    var productid = jQuery(e).val();
                    var productpos = jQuery(e).parent("td").parent("tr").find(".prod_position").val();
                    var checked = "0";
                }
                
                new Ajax.Request("'.$this->getUrl('mobicommerce/widget/checkproduct').'", {
                    method: "Post",
                    parameters: {productid : productid, prod_position:productpos, checked : checked},
                    onSuccess: function(response){
                        jQuery(".selectedproducts").val(response.responseText);             
                    },
                    onFailure: function(response){              
                        alert(json.error);
                    }
                });
            }

            function savePosition(e){
                var productid = jQuery(e).attr("data-productid");
                var productpos = jQuery(e).val();
                if(productid){
                    new Ajax.Request("'.$this->getUrl('mobicommerce/widget/saveprodposition').'", {
                        method: "Post",
                        parameters: {productid : productid, prod_position:productpos},
                        onSuccess: function(response){
                            jQuery(".selectedproducts").val(response.responseText);             
                        },
                        onFailure: function(response){              
                            alert(json.error);
                        }
                    });
                }
            }

            function changeProductSliderType(){
                var cat = "'.$this->getRequest()->getParam('cat', false).'";
                var selectedwidget = jQuery("#productslider_type").val();
                if(selectedwidget == "selected"){
                    new Ajax.Request("'.$this->getUrl('mobicommerce/widget/productgrid').'cat/"+cat, {
                        method: "Post",
                        parameters: {isAjax : 1, widget_id : "'.(isset($widgetdata['widget_id']) ? $widgetdata['widget_id'] : '').'"},
                        onComplete: function(data) {
                        },
                        onSuccess: function(response){
                            var json = response.responseText.evalJSON(true);
                            if(json.status == "success"){
                                jQuery(".product-grid").html(json.widget_product_grid);
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
                    jQuery(".product-grid").html("");
                }
            }

            jQuery(function(){
                changeProductSliderType();
            });
            </script>
            '
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
        
       	return parent::_prepareForm();
   	}

    protected function getYesNoOptions()
    {
        return [
            ['value' => '1','label' => 'Yes'],
            ['value' => '0','label' => 'No'],
        ];
    }
}