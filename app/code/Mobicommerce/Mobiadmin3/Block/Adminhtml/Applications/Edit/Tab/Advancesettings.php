<?php
namespace Mobicommerce\Mobiadmin3\Block\Adminhtml\Applications\Edit\Tab;

class Advancesettings extends \Magento\Backend\Block\Widget\Form\Generic {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory
     */
    protected $mobiadmin3ResourceAppsettingCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Helper\Data
     */
    protected $mobiadmin3Helper;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory $mobiadmin3ResourceAppsettingCollectionFactory,
        \Mobicommerce\Mobiadmin3\Helper\Data $mobiadmin3Helper,
        \Magento\Framework\Data\FormFactory $formFactory
    ) {
        $data = [];
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->request = $request;
        $this->mobiadmin3ResourceAppsettingCollectionFactory = $mobiadmin3ResourceAppsettingCollectionFactory;
        $this->mobiadmin3Helper = $mobiadmin3Helper;
        parent::__construct($context, $registry, $formFactory, $data);
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
        $store = $this->getRequest()->getParam('store');
       	$form = $this->formFactory->create();
       	$this->setForm($form);
       	$applicationData = $this->registry->registry('application_data');
	   	$appcode = $applicationData->getAppCode();
        $storeGroupId = $this->_storeManager->getStore()->getGroupId();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeGroupModel = $objectManager->create('Magento\Store\Model\Store');
        $storeGroupModel->setStoreGroupId($storeGroupId);
        $rootCategoryId = $storeGroupModel->getRootCategoryId();

        $advance_settings_data = [];
        $collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
        $collection->addFieldToFilter('app_code', $appcode)->addFieldToFilter('setting_code','advance_settings');
        if($collection->getSize() > 0){
            $advance_settings_data = @unserialize($collection->getFirstItem()->getValue());
        }

	   	$fieldset = $form->addFieldset('advance_settings_category_settings', ['legend'=> __('Category Settings [Website]')]);

        $fieldset->addField(
            'advance_settings_category_settings_helptext',
            'note',
            [
                'name' => 'advance_settings_category_settings_helptext',
                'label' => '',
                'text' => 'Selected categories will be displayed in Category menu of the mobile app.',
                'bold' => true
            ]
        );

        $optionArray = [];
        $optionSelectedArray = [];
        $categoryObject = $objectManager->create('Magento\Catalog\Model\Category')->load($rootCategoryId);
        $subCats = $categoryObject->getChildrenCategories();
        if($subCats)
        {
            foreach ($subCats as $subcat) {
                $optionArray[] = ['label' => $subcat->getName(), 'value' => $subcat->getId()];
            }
        }

        $homepage_categories = $this->_getHomepageCategories($appcode, $store);
        if(empty($homepage_categories)) {
            $homepage_categories = [];
        }

        $fieldset->addField(
            'advance_settings_category_settings_categories',
            'multiselect',
            [
                'name'     => 'homepage_categories[]',
                'label'    => __('Categories To Show'),
                'title'    => __('Categories To Show'),
                'required' => false,
                'values'   => $optionArray,
                'value'    => array_keys($homepage_categories),
                'disabled' => false
            ]
        );

        $fieldset = $form->addFieldset('advance_settings_image_settings', ['legend'=> __('Image Settings [Website]')]);

        $fieldset->addField(
            'advance_settings_image_settings_helptext',
            'note',
            [
                'name'  => 'advance_settings_image_settings_helptext',
                'label' => '',
                'text'  => 'Provide image dimension which you are using for your product & category images. image container in mobile app will be adjust according to defined shapes and make the app best suit according to your product line.',
                'bold'  => true
            ]
        );

        $fieldset->addField(
            'advance_settings_image_settings_category_image_width',
            'text',
            [
                'name'     => 'advancesettings[image][category_ratio_width]',
                'label'    => __('Category Image Width'),
                'title'    => __('Category Image Width'),
                'value'    => $advance_settings_data['image']['category_ratio_width'],
                'required' => true,
                'class'    => 'validate-number'
            ]
        );
        $fieldset->addField(
            'advance_settings_image_settings_category_image_height',
            'text',
            [
                'name'     => 'advancesettings[image][category_ratio_height]',
                'label'    => __('Category Image Height'),
                'title'    => __('Category Image Height'),
                'value'    => $advance_settings_data['image']['category_ratio_height'],
                'required' => true,
                'class'    => 'validate-number'
            ]
        );

        $fieldset->addField(
            'advance_settings_image_settings_product_image_width',
            'text',
            [
                'name'     => 'advancesettings[image][product_ratio_width]',
                'label'    => __('Product Image Width'),
                'title'    => __('Product Image Width'),
                'value'    => $advance_settings_data['image']['product_ratio_width'],
                'required' => true,
                'class'    => 'validate-number'
            ]
        );

        $fieldset->addField(
            'advance_settings_image_settings_product_image_height',
            'text',
            [
                'name'     => 'advancesettings[image][product_ratio_height]',
                'label'    => __('Product Image Height'),
                'title'    => __('Product Image Height'),
                'value'    => $advance_settings_data['image']['product_ratio_height'],
                'required' => true,
                'class'    => 'validate-number'
            ]
        );

        $fieldset = $form->addFieldset('advance_settings_miscellaneous_settings', ['legend'=> __('Miscellaneous Settings [Website]')]);

        $fieldset->addField(
            'advance_settings_miscellaneous_settings_helptext',
            'note',
            [
                'name'  => 'advance_settings_miscellaneous_settings_helptext',
                'label' => '',
                'text'  => 'Enable/disable module or features for the whole mobile app, disabled module will no longer available in the mobile app.',
                'bold'  => true
            ]
        );

        $enable_disable_options = [
            [
                'label' => 'Enable Rating Feature',
                'value' => $advance_settings_data['miscellaneous']['enable_rating'],
                'name' => 'advancesettings[miscellaneous][enable_rating]'
            ],
            [
                'label' => 'Enable Wishlist Feature',
                'value' => $advance_settings_data['miscellaneous']['enable_wishlist'],
                'name' => 'advancesettings[miscellaneous][enable_wishlist]'
            ],
            [
                'label' => 'Enable Social Media Sharing',
                'value' => $advance_settings_data['miscellaneous']['enable_socialsharing'],
                'name' => 'advancesettings[miscellaneous][enable_socialsharing]'
            ],
            [
                'label' => 'Enable Discount Coupon',
                'value' => $advance_settings_data['miscellaneous']['enable_discountcoupon'],
                'name' => 'advancesettings[miscellaneous][enable_discountcoupon]'
            ],
            [
                'label' => 'Enable Product Search Facility',
                'value' => $advance_settings_data['miscellaneous']['enable_productsearch'],
                'name' => 'advancesettings[miscellaneous][enable_productsearch]'
            ],
            [
                'label' => 'Enable Scan QR Code',
                'value' => $advance_settings_data['miscellaneous']['enable_qrcodescan'],
                'name' => 'advancesettings[miscellaneous][enable_qrcodescan]'
            ],
            [
                'label' => 'Enable NFC Scanner',
                'value' => $advance_settings_data['miscellaneous']['enable_nfcscan'],
                'name' => 'advancesettings[miscellaneous][enable_nfcscan]'
            ],
            [
                'label' => 'Enable Guest Checkout',
                'value' => $advance_settings_data['miscellaneous']['enable_guestcheckout'],
                'name' => 'advancesettings[miscellaneous][enable_guestcheckout]'
            ],
            [
                'label' => 'Enable Estimated Shipping Cost',
                'value' => $advance_settings_data['miscellaneous']['enable_estimatedshippingcost'],
                'name' => 'advancesettings[miscellaneous][enable_estimatedshippingcost]'
            ],
            [
                'label' => 'Show Category Icon',
                'value' => $advance_settings_data['miscellaneous']['enable_categoryicon'],
                'name' => 'advancesettings[miscellaneous][enable_categoryicon]'
            ],
            [
                'label' => 'Enable Category Widgets',
                'value' => $advance_settings_data['miscellaneous']['enable_categorywidget'],
                'name' => 'advancesettings[miscellaneous][enable_categorywidget]'
            ],
            [
                'label' => 'Enable Social Login',
                'value' => $advance_settings_data['miscellaneous']['enable_sociallogin'],
                'name' => 'advancesettings[miscellaneous][enable_sociallogin]'
            ],
            [
                'label' => 'Show Push in Preferences',
                'value' => $advance_settings_data['miscellaneous']['show_push_in_preferences'],
                'name' => 'advancesettings[miscellaneous][show_push_in_preferences]'
            ]
        ];

        $this->render_enable_disable_options($enable_disable_options, $fieldset);

        $fieldset->addField(
            'advancesettings[miscellaneous][show_max_subcategory]',
            'text',
            [
                'name' => 'advancesettings[miscellaneous][show_max_subcategory]',
                'label' => __('No of Sub-Category shown on left panel'),
                'title' => __('No of Sub-Category shown on left panel'),
                'value' => $advance_settings_data['miscellaneous']['show_max_subcategory']
            ]
        );

        $fieldset = $form->addFieldset('advance_settings_productlist_settings', ['legend'=> __('Product Listing Settings [Website]')]);

        $enable_disable_options = [
            [
                'label' => 'Show Product Name',
                'value' => $advance_settings_data['productlist']['showname'],
                'name' => 'advancesettings[productlist][showname]'
            ],
            [
                'label' => 'Show Product Price',
                'value' => $advance_settings_data['productlist']['showprice'],
                'name' => 'advancesettings[productlist][showprice]'
            ],
            [
                'label' => 'Show Product Rating',
                'value' => $advance_settings_data['productlist']['showrating'],
                'name' => 'advancesettings[productlist][showrating]'
            ],
            [
                'label' => 'Enable Sort By option',
                'value' => $advance_settings_data['productlist']['enablesort'],
                'name' => 'advancesettings[productlist][enablesort]'
            ]
        ];

        $this->render_enable_disable_options($enable_disable_options, $fieldset);

        $fieldset->addField(
            'advancesettings[productlist][default_sorting]',
            'select',
            [
                'name' => 'advancesettings[productlist][default_sorting]',
                'label' => __('Default Product Sorting'),
                'options' => [
                    'peopularity' => __('Popularity'),
                    'position'    => __('Position'),
                    'price-h-l'   => __('Price High to Low'),
                    'price-l-h'   => __('Price Low to High'),
                    'rating-h-l'  => __('Rating'),
                    'name-a-z'    => __('Name A-Z'),
                    'name-z-a'    => __('Name Z-A'),
                    'newst'       => __('Newest')
                    ],
                'value' => $advance_settings_data['productlist']['default_sorting']
            ]
        );

        $fieldset->addField(
            'advancesettings[productlist][default_view]',
            'select',
            [
                'name' => 'advancesettings[productlist][default_view]',
                'label' => __('Default Product View'),
                'options' => [
                    'list' => __('List'),
                    'grid' => __('Grid'),
                    'full' => __('Image')
                    ],
                'value' => $advance_settings_data['productlist']['default_view']
            ]
        );

        $enable_disable_options = [
            [
                'label' => 'Enable Change Product View',
                'value' => $advance_settings_data['productlist']['enablechangeproductview'],
                'name' => 'advancesettings[productlist][enablechangeproductview]'
            ],
            [
                'label' => 'Persistent View',
                'value' => $advance_settings_data['productlist']['persistent_view'],
                'name' => 'advancesettings[productlist][persistent_view]'
            ],
            [
                'label' => 'Enable Filter Option',
                'value' => $advance_settings_data['productlist']['enablefilter'],
                'name' => 'advancesettings[productlist][enablefilter]'
            ],
            [
                'label' => 'Enable Masonry View',
                'value' => $advance_settings_data['productlist']['enablemasonry'],
                'name' => 'advancesettings[productlist][enablemasonry]'
            ]
        ];

        $this->render_enable_disable_options($enable_disable_options, $fieldset);

        $fieldset = $form->addFieldset('advance_settings_productdetail_settings', ['legend'=> __('Product Detail Settings [Website]')]);

        $enable_disable_options = [
            [
                'label' => 'Enable product Zoom',
                'value' => $advance_settings_data['productdetail']['enable_productzoom'],
                'name' => 'advancesettings[productdetail][enable_productzoom]'
            ],
            [
                'label' => 'Enable Endless Slider',
                'value' => $advance_settings_data['productdetail']['enable_endless_slider'],
                'name' => 'advancesettings[productdetail][enable_endless_slider]'
            ],
            [
                'label' => 'Enable Related Products',
                'value' => $advance_settings_data['productdetail']['enable_youmaylike_slider'],
                'name' => 'advancesettings[productdetail][enable_youmaylike_slider]'
            ]
        ];

        $this->render_enable_disable_options($enable_disable_options, $fieldset);

        $fieldset->addField(
            'advancesettings[productdetail][show_max_related_products]',
            'text',
            [
                'name' => 'advancesettings[productdetail][show_max_related_products]',
                'label' => __('Maximum Number of Related Products on Product Detail Page'),
                'value' => $advance_settings_data['productdetail']['show_max_related_products'],
                'required' => true,
                'class' => 'validate-number'
            ]
        );

        $optionArray = [];
        $productAttributes = $this->mobiadmin3Helper->getProductAttributes();
        $productAttributesMasterValue = [];
        foreach ($productAttributes as $_attribute) {
            $optionArray[] = ['label' => $_attribute['label'], 'value' => $_attribute['code']];
            $productAttributesMasterValue[] = $_attribute['code'];
        }

        $deselected_values = $advance_settings_data['productdetail']['showattribute'];
        if(empty($advance_settings_data['productdetail']['showattribute'])) {
            $deselected_values = [];
        }
        else {
            $deselected_values = array_keys($advance_settings_data['productdetail']['showattribute']);   
        }

        $arrayDiff = array_diff($productAttributesMasterValue, $deselected_values);

        $fieldset->addField(
            'advancesettings[productdetail][showattribute][]',
            'multiselect',
            [
                'name'     => 'advancesettings[productdetail][showattribute][]',
                'label'    => __('Select Attributes to show on Product Detail Page'),
                'required' => false,
                'values'   => $optionArray,
                'value'    => $arrayDiff,
                'disabled' => false
            ]
        );

        if(empty($advance_settings_data['productdetail']['showattribute_popup'])) {
            $advance_settings_data['productdetail']['showattribute_popup'] = [];
        }

        $deselected_values = $advance_settings_data['productdetail']['showattribute_popup'];
        if(empty($advance_settings_data['productdetail']['showattribute_popup'])) {
            $deselected_values = [];
        }
        else {
            $deselected_values = array_keys($advance_settings_data['productdetail']['showattribute_popup']);   
        }

        $arrayDiff = array_diff($productAttributesMasterValue, $deselected_values);

        $fieldset->addField(
            'advancesettings[productdetail][showattribute_popup][]',
            'multiselect',
            [
                'name'     => 'advancesettings[productdetail][showattribute_popup][]',
                'label'    => __('Select Attributes to show on Know More Screen (popup screen)'),
                'required' => false,
                'values'   => $optionArray,
                'value'    => $arrayDiff,
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'advancesettings[productdetail][show_max_attributes]',
            'text',
            [
                'name'     => 'advancesettings[productdetail][show_max_attributes]',
                'label'    => __('Maximum Number of Attributes on Product Detail Page'),
                'value'    => $advance_settings_data['productdetail']['show_max_attributes'],
                'required' => true,
                'class'    => 'validate-number'
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

    protected function render_enable_disable_options($enable_disable_options, $fieldset)
    {
        foreach($enable_disable_options as $_option) {
            $fieldset->addField(
                $_option['name'],
                'radios',
                [
                    'name' => $_option['name'],
                    'label' => __($_option['label']),
                    'title' => __($_option['label']),
                    'values' => $this->getYesNoOptions(),
                    'value' => $_option['value']
                ]
            );
        }
    }

    protected function _getHomepageCategories($appcode, $store)
    {
        $collection = $this->mobiadmin3ResourceAppsettingCollectionFactory->create();
        $collection->addFieldToFilter('app_code', $appcode)
            ->addFieldToFilter('setting_code', 'homepage_categories')
            ->addFieldToFilter('storeid', $store);
        
        return @unserialize($collection->getFirstItem()->getValue());
    }
}