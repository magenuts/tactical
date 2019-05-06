<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Type;

class Category extends \Magento\Backend\Block\Widget\Form\Generic {

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
        $catidsinput = "";
        $ids = '';
        $cat_ids = [];
        if($widgetdata) {
            $data = @unserialize($widgetdata['widget_data']);
            $ids = $data['categories'];
            if(!is_array($ids))
                $cat_ids = @json_decode($ids, true);
            else
                $cat_ids = $ids;

            if(is_array($cat_ids))
                $catidsinput = implode(",",array_keys(@$cat_ids));
        }

        $this->_backendSession->setData('checked_categories', $cat_ids);

	   	$fieldset = $form->addFieldset('widget_category', ['legend'=> __('Category Widget')]);

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
            'widget[widget_data][cat_layout]',
            'select',
            [
                'name' => 'widget[widget_data][cat_layout]',
                'label' => __('Layout'),
                'values' => [
                    ['value' => 'grid', 'label' => 'Grid'],
                    ['value' => 'list', 'label' => 'List'],
                    ['value' => 'slider', 'label' => 'Slider'],
                    ['value' => 'banner', 'label' => 'Banner']
                ],
                'value' => isset($data['cat_layout']) ? $data['cat_layout'] : ''
            ]
        );

        $fieldset->addField(
            'widget[widget_data][category_force_product_nav]',
            'radios',
            [
                'name' => 'widget[widget_data][category_force_product_nav]',
                'label' => __('Force Navigate to Product List'),
                'values' => $this->getYesNoOptions(),
                'value' => isset($data['category_force_product_nav']) ? $data['category_force_product_nav'] : '1'
            ]
        );

        $fieldset->addField(
            'widget[widget_data][show_thumbnail]',
            'radios',
            [
                'name' => 'widget[widget_data][show_thumbnail]',
                'label' => __('Show Thumbnail'),
                'values' => $this->getYesNoOptions(),
                'value' => isset($data['show_thumbnail']) ? $data['show_thumbnail'] : '1'
            ]
        );

        $fieldset->addField(
            'widget[widget_data][show_name]',
            'radios',
            [
                'name' => 'widget[widget_data][show_name]',
                'label' => __('Show Name'),
                'values' => $this->getYesNoOptions(),
                'value' => isset($data['show_name']) ? $data['show_name'] : '1'
            ]
        );

        $field = $fieldset->addField(
            'hidden_display_categories',
            'text',
            [
                'name' => 'category',
                'label' => __('Categories'),
                'readonly' => true,
                'value' => $catidsinput
            ]
        );

        $field = $fieldset->addField(
            'widget[widget_data][categories]',
            'hidden',
            [
                'name' => 'widget[widget_data][categories]',
                'label' => '',
                'class' => 'selectedcategories',
                'value' => (is_array($ids) && count($ids)) ? json_encode($ids) : $ids
            ]
        );

        $field->setAfterElementHtml(
            '<br /><div class="category-grid" style="width: 800px;"></div>
            <script>
            function saveCategory(e) {
                if(jQuery(e).is(":checked")) {
                    var categoryid = jQuery(e).val();
                    var categorypos = jQuery(e).parent("td").parent("tr").find(".category-pos").val();
                    var categorynav = jQuery(e).parent("td").parent("tr").find(".category-navigate").val();
                    var checked = "1";
                } else { 
                    var categoryid = jQuery(e).val();
                    var checked = "0";
                }
                
                new Ajax.Request("'.$this->getUrl('mobicommerce/widget/checkcategory').'", {
                    method: "Post",
                    parameters: {categoryid : categoryid, checked : checked, categorypos:categorypos ,categorynav:categorynav},
                    onSuccess: function(response) {
                        jQuery(".selectedcategories").val(response.responseText);
                    },
                    onFailure: function(response){
                        alert(json.error);
                    }
                });
            }

            function savePosition(e){
                var categoryid = jQuery(e).attr("data-categoryid");
                var categorypos = jQuery(e).val();
                if(categorypos){
                    new Ajax.Request("'.$this->getUrl('mobicommerce/widget/savecatposition').'", {
                        method: "Post",
                        parameters: {categoryid : categoryid, categorypos:categorypos},
                        onSuccess: function(response){
                            jQuery(".selectedcategories").val(response.responseText);
                        },
                        onFailure: function(response){
                            alert(json.error);
                        }
                    });
                }
            }

            function showCategoryGridArea(){
                var cat = "'.$this->getRequest()->getParam('cat', false).'";
                new Ajax.Request("'.$this->getUrl('mobicommerce/widget/categorygrid').'cat/"+cat, {
                    method: "Post",
                    parameters: {isAjax : 1, widget_id : "'.(isset($widgetdata['widget_id'])?$widgetdata['widget_id']:'').'"},
                    onComplete: function(data) {                    
                    },
                    onSuccess: function(response){
                        var json = response.responseText.evalJSON(true);
                        if(json.status == "success"){
                            jQuery(".category-grid").html(json.widget_category_grid);
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
            }
            </script>
            '
        );

        $assignCategoryButton = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'label' => __('Choose Categories'),
                'onclick' => '
                    require([
                        "jquery"
                    ], function(jQuery){
                        showCategoryGridArea();
                    });
                    ',
                'class' => 'save',
            ]
        );

        $fieldset->addField('assign_category_button', 'note', [
            'text' => $assignCategoryButton->toHtml()
        ]);

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