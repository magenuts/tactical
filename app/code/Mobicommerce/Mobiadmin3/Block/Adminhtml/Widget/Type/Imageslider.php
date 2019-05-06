<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Widget\Type;

class Imageslider extends \Magento\Backend\Block\Widget\Form\Generic {

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
    protected $_storeManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $data = [];
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->request = $request;
        parent::__construct($context, $registry, $formFactory, $data);

        $this->_storeManager = $storeManager;
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
        if($widgetdata) {
            $data = @unserialize($widgetdata['widget_data']);
            $banners = isset($data['banners']) ? $data['banners'] : [];
        }

	   	$fieldset = $form->addFieldset('widget_imageslider', ['legend'=> __('Image Slider Widget')]);

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

        $field = $fieldset->addField(
            'widget[widget_data][slider_type]',
            'select',
            [
                'name' => 'widget[widget_data][slider_type]',
                'label' => __('Slider Type'),
                'values' => [
                    ['label' => 'Side View', 'value' => 'sideview'],
                    ['label' => 'Dotted View', 'value' => 'dottedview'],
                    ['label' => 'Swiper View', 'value' => 'swiperview'],
                    ['label' => 'Autoplay', 'value' => 'autoplay']
                ],
                'value' => isset($data['slider_type']) ? $data['slider_type'] : ''
            ]
        );

        $media_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $existing_banners = [];
        $existing_banners_index = 1;
        if(isset($data['banners']) && !empty($data['banners'])) {
            foreach($data['banners'] as $_banner) {
                $existing_banners[] = '<tr>
                    <td>
                        <img src="'.$media_url.'/'.$_banner['banner_url'].'" alt="Image" height="32" width="32" />
                        <input type="file" name="banners['.$existing_banners_index.']" class="mobi_fileupload_without_helptext" accept="image/*" />
                        <input type="hidden" name="widget[widget_data][banners]['.$existing_banners_index.'][banner_url]" value="'.$_banner['banner_url'].'" />
                    </td>
                    <td><input type="text" class="input-text mobi_position_textbox" value="'.$_banner['banner_position'].'" name="widget[widget_data][banners]['.$existing_banners_index.'][banner_position]" /></td>
                    <td>
                        <input type="text" class="input-text mobi_position_textbox hidden_banner_link" readonly="readonly" name="widget[widget_data][banners]['.$existing_banners_index.'][banner_link]" id="widget_data_banners_'.$existing_banners_index.'_banner_link" value="'.$_banner['banner_link'].'" />
                        <button type="button" class="action-additional" onclick="imageSliderDeeplink(this);">
                            <span">Link</span>
                        </button>
                    </td>
                    <td><input type="checkbox" '.((isset($_banner['banner_status']) && $_banner['banner_status']) ? 'checked="checked" ': '').' name="widget[widget_data][banners]['.$existing_banners_index.'][banner_status]" value="1" /></td>
                    <td>
                        <button type="button" class="scalable save" onclick="deleteImageSliderOptionRow(this);">
                            <span>Delete</span>
                        </button>
                    </td>
                </tr>';
                $existing_banners_index++;
            }
        }
        $existing_banners = implode('', $existing_banners);

        $field->setAfterElementHtml('
            <br /><br />
            <div style="width: 700px">
                <section class="admin__page-section">
                    <div class="admin__page-section-content">
                        <div class="admin__page-section-item">
                            <div class="admin__page-section-item-content">
                                <table class="admin__table-secondary widget_image_slider_table">
                                    <thead>
                                        <tr>
                                            <th>'.__('Image').'</th>
                                            <th>'.__('Position').'</th>
                                            <th>'.__('Link To').'</th>
                                            <th>'.__('Status').'</th>
                                            <th>'.__('Action').'</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        '.$existing_banners.'
                                        <tr class="mobi_image_slider_hidden_row" style="display: none">
                                            <td>
                                                <input type="file" name="banners[{index}]" class="mobi_fileupload_without_helptext" accept="image/*" />
                                                <input type="hidden" name="widget[widget_data][banners][{index}][banner_url]" />
                                            </td>
                                            <td><input type="text" class="input-text mobi_position_textbox" value="1" name="widget[widget_data][banners][{index}][banner_position]" /></td>
                                            <td>
                                                <input type="text" class="input-text mobi_position_textbox hidden_banner_link" readonly="readonly" name="widget[widget_data][banners][{index}][banner_link]" id="widget_data_banners_{index}_banner_link" />
                                                <button type="button" class="action-additional" onclick="imageSliderDeeplink(this);">
                                                    <span">Link</span>
                                                </button>
                                            </td>
                                            <td><input type="checkbox" checked="checked" name="widget[widget_data][banners][{index}][banner_status]" value="1" /></td>
                                            <td>
                                                <button type="button" class="scalable save" onclick="deleteImageSliderOptionRow(this);">
                                                    <span>Delete</span>
                                                </button>
                                            </td>
                                        </tr>

                                        <tr class="mobi_image_slider_add_option_row">
                                            <td colspan="5">
                                                Recommended image width: 1080px
                                                <button type="button" class="scalable save" onclick="addImageSliderOptionRow();">
                                                    <span>Add Image</span>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <script>
            function deleteImageSliderOptionRow(e)
            {
                jQuery(e).parents("tr").remove();
            }

            function addImageSliderOptionRow()
            {
                var _html = "<tr>";
                var _length = jQuery(".widget_image_slider_table tr").length;
                _html += jQuery(".mobi_image_slider_hidden_row").html();
                _html += "</tr>";

                _html = _html.replace(/{index}/g, _length + 1);
                jQuery(".mobi_image_slider_add_option_row").before(_html);
            }

            function imageSliderDeeplink(e)
            {
                var _bannerid = jQuery(e).parents("td").find(".hidden_banner_link").attr("id");
                var _link = jQuery(e).parents("td").find(".hidden_banner_link").val();
                require([
                        "jquery",
                        "Magento_Ui/js/modal/modal"
                    ], function(jQuery, modal){
                        new Ajax.Request("'.$this->getUrl('mobicommerce/widget/deeplink').'bannerid/"+_bannerid+"/link/"+_link, {
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

                            var popupdata = jQuery("<div id=\'modal_image_slider_deeplink\' />").append(response.responseText);
                            modal(options, popupdata);
                            popupdata.modal("openModal").on("modalclosed", function() { 
                                jQuery("#modal_image_slider_deeplink").remove();
                            });
                        }
                    });
                });
            }
            </script>
            ');

        $fieldset->addField(
            'widget[widget_data][slide_auto_play_interval]',
            'text',
            [
                'name' => 'widget[widget_data][slide_auto_play_interval]',
                'label' => __('Autoplay Interval(ms)'),
                'value' => isset($data['slide_auto_play_interval']) ? $data['slide_auto_play_interval'] : '',
                'class' => 'validate-number'
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
        
       	return parent::_prepareForm();
   	}
}