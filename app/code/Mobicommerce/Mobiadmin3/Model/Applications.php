<?php
namespace Mobicommerce\Mobiadmin3\Model;

class Applications extends \Magento\Framework\Model\AbstractModel {

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Applications\CollectionFactory
     */
    protected $mobiadmin3ResourceApplicationsCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\ApplicationsFactory
     */
    protected $mobiadmin3ApplicationsFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\AppsettingFactory
     */
    protected $mobiadmin3AppsettingFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Helper\Data
     */
    protected $mobiadmin3Helper;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\WidgetFactory
     */
    protected $mobiadmin3WidgetFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory
     */
    protected $mobiadmin3ResourceAppsettingCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Widget\CollectionFactory
     */
    protected $mobiadmin3ResourceWidgetCollectionFactory;

    /**
     * @var \Mobicommerce\Mobiadmin3\Model\Resource\Devicetokens\CollectionFactory
     */
    protected $mobiadmin3ResourceDevicetokensCollectionFactory;
    
    protected $_dir;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mobicommerce\Mobiadmin3\Model\Resource\Applications\CollectionFactory $mobiadmin3ResourceApplicationsCollectionFactory,
        \Mobicommerce\Mobiadmin3\Model\ApplicationsFactory $mobiadmin3ApplicationsFactory,
        \Mobicommerce\Mobiadmin3\Model\AppsettingFactory $mobiadmin3AppsettingFactory,
        \Mobicommerce\Mobiadmin3\Helper\Data $mobiadmin3Helper,
        \Mobicommerce\Mobiadmin3\Model\WidgetFactory $mobiadmin3WidgetFactory,
        \Mobicommerce\Mobiadmin3\Model\Resource\Appsetting\CollectionFactory $mobiadmin3ResourceAppsettingCollectionFactory,
        \Mobicommerce\Mobiadmin3\Model\Resource\Widget\CollectionFactory $mobiadmin3ResourceWidgetCollectionFactory,
        \Mobicommerce\Mobiadmin3\Model\Resource\Devicetokens\CollectionFactory $mobiadmin3ResourceDevicetokensCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->mobiadmin3ResourceApplicationsCollectionFactory = $mobiadmin3ResourceApplicationsCollectionFactory;
        $this->mobiadmin3ApplicationsFactory = $mobiadmin3ApplicationsFactory;
        $this->mobiadmin3AppsettingFactory = $mobiadmin3AppsettingFactory;
        $this->mobiadmin3Helper = $mobiadmin3Helper;
        $this->mobiadmin3WidgetFactory = $mobiadmin3WidgetFactory;
        $this->mobiadmin3ResourceAppsettingCollectionFactory = $mobiadmin3ResourceAppsettingCollectionFactory;
        $this->mobiadmin3ResourceWidgetCollectionFactory = $mobiadmin3ResourceWidgetCollectionFactory;
        $this->mobiadmin3ResourceDevicetokensCollectionFactory = $mobiadmin3ResourceDevicetokensCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        $this->_init('Mobicommerce\Mobiadmin3\Model\Resource\Applications');
    }
    
    public function getCoreModel($modelPath)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection
         $productCollection */
        $model = $objectManager->create($modelPath);
        /** Apply filters here */
        return $model;
    }
    
	public function saveApplicationData($data)
	{
        $appid = null;
		$errors = [];
		$groupId = $data['groupId'];
		$stores = $this->storeManager->getStores();

		$appcode = $data['app_code'];
		$appExist = $this->mobiadmin3ResourceApplicationsCollectionFactory->create()->addFieldToFilter('app_code', $appcode)
			->addFieldToFilter('app_key', $data['app_key'])->count();
		if(!$appExist){
			$this->_create_mobi_media_dir($appcode, $data['app_theme_folder_name']);
			$applicationData = [
				'app_name'         => $data['app_name'],
				'app_code'         => $appcode,
				'app_key'          => $data['app_key'],
				'app_license_key'  => $data['app_license_key'],
				'app_storegroupid' => $groupId,
				'app_mode'         => 'demo',
				'created_time'     => date('Y-m-d H:i:s'),
				'android_url'      => $data['android_url'],
				'android_status'   => $data['android_status'],
				'ios_url'          => $data['ios_url'],
				'ios_status'       => $data['ios_status'],
				'version_type'     => $data['version_type']
				];

			try{
				$appid = $this->mobiadmin3ApplicationsFactory->create()->setData($applicationData)->save()->getId();
			}catch(Exception $e){
				$errors[] = $e->getMessage();   
			}

			$appinfo = serialize([
				'bundle_id'			   => 'com.mobicommerce.sampleapp',
				'iosappid'			   => '910995460',
				'android_appname'      => $data['app_name'],
				'android_appweburl'    => 'https://play.google.com/store/apps/details?id=com.mobicommerce.sampleapp',
				'ios_appname'          => $data['app_name'],
				'ios_appweburl'        => 'https://itunes.apple.com/in/app/mobicommerce-mobile-app/id910995460?mt=8',
				'app_description'      => 'Have you tried this app ?',
				'app_share_image'      => '',
				]);

			$this->mobiadmin3AppsettingFactory->create()->setData([
				'app_code'     => $appcode,
				'setting_code' => 'appinfo',
				'value'        => $appinfo
				])->save();
	
			$pushValue = serialize([
				'active_push_notification' => 1,
				'android_key'			   => 'AIzaSyAzvHE5MnSBq_R-SmBzgCqMn1vV03Khi2M',
				'android_sender_id'        => '881306584774',
				'upload_iospem_file'       => null,
				'pem_password'             => null,
				'sandboxmode'              => 0
				]);

			$this->mobiadmin3AppsettingFactory->create()->setData([
				'app_code'     => $appcode,
				'setting_code' => 'push_notification',
				'value'        => $pushValue
				])->save();

			$this->mobiadmin3AppsettingFactory->create()->setData([
				'app_code'     => $appcode,
				'setting_code' => 'theme_folder_name',
				'value'        => $data['app_theme_folder_name']
				])->save();

			$this->mobiadmin3AppsettingFactory->create()->setData([
				'app_code'     => $appcode,
				'setting_code' => 'theme_android',
				'value'        => $data['theme_android']
				])->save();
			$this->mobiadmin3AppsettingFactory->create()->setData([
				'app_code'     => $appcode,
				'setting_code' => 'theme_ios',
				'value'        => $data['theme_ios']
				])->save();

			$cms_contents = [
				"contact_information" => [
					"company_name"    => "Your Company Name",
					"company_address" => "Your company addresss here",
					"phone_number"    => "+0-000-000-0000",
					"email_address"   => "mail@yourdomain.com",
                    "menu_icon"       => "",
					"latitude"        => "20.5937",
					"longitude"       => "78.9629",
					"zoom_level"      => "8",
					"pin_color"       => "000",
					],
				"social_media" => [
					"facebook" => [
						"checked" => "1",
						"url"     => "https://www.facebook.com/mobi.commerce.platform"
						],
					"twitter" => [
						"checked" => "1",
						"url"     => "https://twitter.com/mobicommerceapp"
						],
					"linkedin" => [
						"checked" => "0",
						"url"     => ""
						],
					"googleplus" => [
						"checked" => "0",
						"url"     => ""
						],
					"instagram" => [
						"checked" => "0",
						"url"     => ""
						],
					"youtube" => [
						"checked" => "0",
						"url"     => ""
						],
					"pinterest" => [
						"checked" => "0",
						"url"     => ""
						],
					"blog" => [
						"checked" => "0",
						"url"     => ""
						],
					],
				"cms_pages" => [],
				];
			$cmsdata = [
				'app_code'     => $appcode,
				'setting_code' => 'cms_settings',
				'value'        => serialize($cms_contents)
				];

			foreach($stores as $_store){
				$cmsdata['storeid'] = $_store->getStoreId();
				$this->mobiadmin3AppsettingFactory->create()->setData($cmsdata)->save();
			}

			$googleanalytics = serialize([
				'android' => [
					'status' => '0',
					'code'   => ''
					],
				'ios' => [
					'status' => '0',
					'code'   => ''
					],
				]);

			$this->mobiadmin3AppsettingFactory->create()->setData([
				'app_code'     => $appcode,
				'setting_code' => 'googleanalytics',
				'value'        => $googleanalytics
				])->save();

			$storegroup_categories = [];
			foreach ($this->storeManager->getWebsites() as $website){
	            foreach ($website->getGroups() as $group){
					$root_category_id = $group->getRootCategoryId();
                    $children = $this->getCoreModel('Magento\Catalog\Model\Category')->getCategories($root_category_id);

			        $categories = [];
			        if($children){
			        	$index = 0;
			            foreach ($children as $category) {
			                $categories[$category->getId()] = $index++;
			            }
			        }
			        $storegroup_categories[$group->getId()]['categories'] = $categories;

			        $homepage_categories = [
						'app_code'     => $appcode,
						'setting_code' => 'homepage_categories',
						'value'        => serialize($categories)
						];

			        foreach($group->getStores() as $_store){
						$homepage_categories['storeid'] = $_store->getStoreId();
						$this->mobiadmin3AppsettingFactory->create()->setData($homepage_categories)->save();
					}
	            }
	        }

			$advanceSettings = [
				'app_code'     => $appcode,
				'setting_code' => 'advance_settings',
				'value'        => serialize([
					"image"      => [
						"category_ratio_width"  => "1",
						"category_ratio_height" => "1",
						"product_ratio_width"   => "1",
						"product_ratio_height"  => "1",
						],
					"miscellaneous" => [
						"enable_rating"                => "1",
						"enable_wishlist"              => "1",
						"enable_socialsharing"         => "1",
						"enable_discountcoupon"        => "1",
						"enable_productsearch"         => "1",
						"enable_qrcodescan"            => "0",
						"enable_nfcscan"         	   => "0",
						"enable_guestcheckout"         => "0",
						"enable_estimatedshippingcost" => "0",
						"enable_categoryicon"          => "0",
						"enable_categorywidget"        => "0",
						"enable_sociallogin"           => "1",
						"show_push_in_preferences"     => "0",
						"show_max_subcategory"         => "3",
						],
					"productlist" => [
						"showname"                => "1",
						"showprice"               => "1",
						"showrating"              => "1",
						"enablesort"              => "1",
						"default_sorting"         => "popularity",
						"default_view"            => "list",
						"persistent_view"		  => "1",
						"enablechangeproductview" => "1",
						"enablefilter"            => "1",
						"enablemasonry"           => "1",
						],
					"productdetail" => [
						"enable_productzoom"        => "1",
						"enable_endless_slider"     => "0",
						"enable_youmaylike_slider"  => "1",
						"show_max_related_products" => "4",
						"showattribute"             => [],
						"showattribute_popup"       => [],
						"show_max_attributes"       => "3",
						]
					])
				];
			$this->mobiadmin3AppsettingFactory->create()->setData($advanceSettings)->save();
			$this->setDefaultWidgetData($appcode, $groupId, $data['version_type'], $data['app_theme_folder_name'], $storegroup_categories);
        }
		return [
			'appid' => $appid,
			'errors' => $errors
			];
	}

	public function setDefaultWidgetData($appcode, $groupId, $version_type, $theme, $categories)
	{
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
        $base_dir = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();

        $base_url = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

		//professional
		if($version_type == '001'){
			@copy($base_dir.'/'.'mobi_assets'.'/'.'v'.'/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/'.'theme_files'.'/'.$theme.'/'.'professional'.'/'.'banners'.'/'.'banner1.jpg', $base_dir.'/'.'mobi_commerce'.'/'.$appcode.'/'.'home_banners'.'/'.'banner1.jpg');
			@copy($base_dir.'/'.'mobi_assets'.'/'.'v'.'/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/'.'theme_files'.'/'.$theme.'/'.'professional'.'/'.'banners'.'/'.'banner2.jpg', $base_dir.'/'.'mobi_commerce'.'/'.$appcode.'/'.'home_banners'.'/'.'banner2.jpg');
			@copy($base_dir.'/'.'mobi_assets'.'/'.'v'.'/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/'.'theme_files'.'/'.$theme.'/'.'professional'.'/'.'banners'.'/'.'banner3.jpg', $base_dir.'/'.'mobi_commerce'.'/'.$appcode.'/'.'home_banners'.'/'.'banner3.jpg');

			$widgets = [
				// image banner
				[
					'widget_label'    => 'Top Banners',
					'widget_code'     => 'widget_image_slider',
					'widget_status'   => '1',
					'widget_position' => '1',
					'widget_data'     => [
						'title'                    => '',
						'title_align'              => 'center',
						'slider_type'			   => 'sideview',
						'banners'                  => [
							[
								'banner_options'  => '1',
								'banner_url'      => 'mobi_commerce/'.$appcode.'/home_banners/banner1.jpg',
								'banner_position' => '1',
								'banner_status'   => '1',
								'banner_link'     => null,
								'banner_delete'   => '0'
								],
							[
								'banner_options'  => '2',
								'banner_url'      => 'mobi_commerce/'.$appcode.'/home_banners/banner2.jpg',
								'banner_position' => '2',
								'banner_status'   => '1',
								'banner_link'     => null,
								'banner_delete'   => '0'
								],
							[
								'banner_options'  => '2',
								'banner_url'      => 'mobi_commerce/'.$appcode.'/home_banners/banner3.jpg',
								'banner_position' => '3',
								'banner_status'   => '1',
								'banner_link'     => null,
								'banner_delete'   => '0'
								],
							]
						]
					],
				// category widget
				[
					'widget_label'    => 'Shop by Category',
					'widget_code'     => 'widget_category',
					'widget_status'   => '1',
					'widget_position' => '2',
					'widget_data'     => [
						'title'                      => 'SHOP BY CATEGORY',
						'title_align'                => 'left',
						'cat_layout'                 => 'list',
						'category_force_product_nav' => '0',
						'show_thumbnail'             => '0',
						'show_name'                  => '1',
						'categories'                 => null,
						]
					],
				// product slider widget
				[
					'widget_label'    => 'New Arrivals',
					'widget_code'     => 'widget_product_slider',
					'widget_status'   => '1',
					'widget_position' => '3',
					'widget_data'     => [
						'title'              => 'NEW ARRIVALS',
						'title_align'        => 'left',
						'type'               => 'grid',
						'maxItems'			 => 4,
						'limit'				 => 10,
						'show_name'          => '1',
						'show_price'         => '1',
						'show_review'        => '1',
						'productslider_type' => 'newarrivals',
						'products'           => json_encode([]),
						]
					],
				// product slider widget
				[
					'widget_label'    => 'Best Selling',
					'widget_code'     => 'widget_product_slider',
					'widget_status'   => '1',
					'widget_position' => '4',
					'widget_data'     => [
						'title'              => 'BEST SELLING',
						'title_align'        => 'left',
						'type'               => 'list',
						'maxItems'			 => 3,
						'limit'				 => 15,
						'show_name'          => '1',
						'show_price'         => '1',
						'show_review'        => '1',
						'productslider_type' => 'bestseller',
						'products'           => json_encode([]),
						]
					],
				// product slider widget
				[
					'widget_label'    => 'Hot Deals',
					'widget_code'     => 'widget_product_slider',
					'widget_status'   => '1',
					'widget_position' => '5',
					'widget_data'     => [
						'title'              => 'HOT DEALS',
						'title_align'        => 'left',
						'type'               => 'full',
						'maxItems'			 => 2,
						'limit'				 => 10,
						'show_name'          => '1',
						'show_price'         => '1',
						'show_review'        => '1',
						'productslider_type' => 'selected',
						'products'           => json_encode([]),
						]
					],
				// product slider widget
				[
					'widget_label'    => 'Recently viewed Products',
					'widget_code'     => 'widget_product_slider',
					'widget_status'   => '1',
					'widget_position' => '6',
					'widget_data'     => [
						'title'              => 'RECENTLY VIEWED',
						'title_align'        => 'left',
						'type'               => 'grid',
						'maxItems'			 => 4,
						'limit'				 => 10,
						'show_name'          => '1',
						'show_price'         => '1',
						'show_review'        => '1',
						'productslider_type' => 'productviewed',
						'products'           => json_encode([]),
						]
					],
			];
		}
		// enterprise
		else if($version_type == '002'){
			@copy($base_dir.'/mobi_assets/v/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/theme_files/'.$theme.'/enterprise/banners/banner1.jpg', $base_dir.'/mobi_commerce/'.$appcode.'/home_banners/banner1.jpg');
			@copy($base_dir.'/mobi_assets/v/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/theme_files/'.$theme.'/enterprise/banners/banner2.jpg', $base_dir.'/mobi_commerce/'.$appcode.'/home_banners/banner2.jpg');
			@copy($base_dir.'/mobi_assets/v/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/theme_files/'.$theme.'/enterprise/banners/banner3.jpg', $base_dir.'/mobi_commerce/'.$appcode.'/home_banners/banner3.jpg');

			$promotional_images = [
				'promo1.jpg',
				'promo2.jpg',
				'promo3.jpg',
				'promo4.jpg',
				'promo5.jpg',
				'promo6.jpg',
				];

			foreach($promotional_images as $_image) {
				@copy($base_dir.'/mobi_assets/v/'.$this->mobiadmin3Helper->getMobiBaseVersion().'/theme_files/'.$theme.'/enterprise/image/'.$_image, $base_dir.'/mobi_commerce/widget_image/'.$appcode.$_image);
			}
            
			$widgets = [
				// image banner
				[
					'widget_label'    => 'Top Banners',
					'widget_code'     => 'widget_image_slider',
					'widget_status'   => '1',
					'widget_position' => '1',
					'widget_data'     => [
						'title'                    => '',
						'title_align'              => 'center',
						'slider_type'			   => 'sideview',
						'banners'                  => [
							[
								'banner_options'  => '1',
								'banner_url'      => 'mobi_commerce/'.$appcode.'/home_banners/banner1.jpg',
								'banner_position' => '1',
								'banner_status'   => '1',
								'banner_link'     => null,
								'banner_delete'   => '0'
								],
							[
								'banner_options'  => '2',
								'banner_url'      => 'mobi_commerce/'.$appcode.'/home_banners/banner2.jpg',
								'banner_position' => '2',
								'banner_status'   => '1',
								'banner_link'     => null,
								'banner_delete'   => '0'
								],
							[
								'banner_options'  => '2',
								'banner_url'      => 'mobi_commerce/'.$appcode.'/home_banners/banner3.jpg',
								'banner_position' => '3',
								'banner_status'   => '1',
								'banner_link'     => null,
								'banner_delete'   => '0'
								],
							]
						]
					],
				// category widget
				[
					'widget_label'    => 'Shop by Category',
					'widget_code'     => 'widget_category',
					'widget_status'   => '1',
					'widget_position' => '2',
					'widget_data'     => [
						'title'                      => 'SHOP BY CATEGORY',
						'title_align'                => 'left',
						'cat_layout'                 => 'list',
						'category_force_product_nav' => '0',
						'show_thumbnail'             => '0',
						'show_name'                  => '1',
						'categories'                 => null,
						]
					],
				// product slider widget
				[
					'widget_label'    => 'New Arrivals',
					'widget_code'     => 'widget_product_slider',
					'widget_status'   => '1',
					'widget_position' => '3',
					'widget_data'     => [
						'title'              => 'NEW ARRIVALS',
						'title_align'        => 'left',
						'type'               => 'grid',
						'maxItems'			 => 4,
						'limit'				 => 10,
						'show_name'          => '1',
						'show_price'         => '1',
						'show_review'        => '1',
						'productslider_type' => 'newarrivals',
						'products'           => json_encode([]),
						]
					],
				// product slider widget
				[
					'widget_label'    => 'Hot Deals',
					'widget_code'     => 'widget_product_slider',
					'widget_status'   => '1',
					'widget_position' => '4',
					'widget_data'     => [
						'title'              => 'HOT DEALS',
						'title_align'        => 'left',
						'type'               => 'full',
						'maxItems'			 => 2,
						'limit'				 => 10,
						'show_name'          => '1',
						'show_price'         => '1',
						'show_review'        => '1',
						'productslider_type' => 'selected',
						'products'           => json_encode([]),
						]
					],
				// image widget
				[
					'widget_label'    => 'Promotional Banner 1',
					'widget_code'     => 'widget_image',
					'widget_status'   => '1',
					'widget_position' => '5',
					'widget_data'     => [
						'title'        => '',
						'title_align'  => 'center',
						'mapcode' => '<img src="mobi_commerce/widget_image/'.$appcode.'promo1.jpg" alt="" usemap="#map'.$appcode.'promo1.jpg"><map id="map'.$appcode.'promo1.jpg" name="map'.$appcode.'promo1.jpg"><area shape="rect" coords="2,2,494,495" title="undefined" alt="undefined" href="__CATEGORY_LINK__" target="_self"><area shape="rect" coords="500,4,999,245" title="undefined" alt="undefined" href="" target="_self"><area shape="rect" coords="504,254,1004,495" title="undefined" alt="undefined" href="__CATEGORY_LINK__" target="_self"><area shape="rect" coords="1002,493,1003,494" alt="Image HTML map generator" title="HTML Map creator" href="" target="_self"></map>',
						'widget_image' => 'mobi_commerce/widget_image/'.$appcode.'promo1.jpg',
						]
					],
				// product slider widget
				[
					'widget_label'    => 'Trending now',
					'widget_code'     => 'widget_product_slider',
					'widget_status'   => '1',
					'widget_position' => '6',
					'widget_data'     => [
						'title'              => 'TRENDING NOW',
						'title_align'        => 'left',
						'type'               => 'slider',
						'maxItems'			 => 10,
						'limit'				 => 20,
						'show_name'          => '1',
						'show_price'         => '1',
						'show_review'        => '1',
						'productslider_type' => 'selected',
						'products'           => json_encode([]),
						]
					],
				// image widget
				[
					'widget_label'    => 'Promo 2',
					'widget_code'     => 'widget_image',
					'widget_status'   => '1',
					'widget_position' => '7',
					'widget_data'     => [
						'title'        => '',
						'title_align'  => 'center',
						'mapcode'	   => '<img src="mobi_commerce/widget_image/'.$appcode.'promo2.jpg" alt="">',
						'widget_image' => 'mobi_commerce/widget_image/'.$appcode.'promo2.jpg',
						]
					],
				// image widget
				[
					'widget_label'    => 'Promo 3',
					'widget_code'     => 'widget_image',
					'widget_status'   => '1',
					'widget_position' => '8',
					'widget_data'     => [
						'title'        => '',
						'title_align'  => 'center',
						'mapcode'  	   => '<img src="mobi_commerce/widget_image/'.$appcode.'promo3.jpg" alt="" usemap="#map'.$appcode.'promo3.jpg"><map id="map'.$appcode.'promo3.jpg" name="map'.$appcode.'promo3.jpg"><area shape="rect" coords="8,11,1590,636" title="undefined" alt="undefined" href="__CATEGORY_LINK__" target="_self"><area shape="rect" coords="1598,648,1599,649" alt="Image HTML map generator" title="HTML Map creator" href="" target="_self"></map>',
						'widget_image' => 'mobi_commerce/widget_image/'.$appcode.'promo3.jpg',
						]
					],
				// image widget
				[
					'widget_label'    => 'Promo 4',
					'widget_code'     => 'widget_image',
					'widget_status'   => '1',
					'widget_position' => '9',
					'widget_data'     => [
						'title'        => '',
						'title_align'  => 'center',
						'mapcode'	   => '<img src="mobi_commerce/widget_image/'.$appcode.'promo4.jpg" alt="">',
						'widget_image' => 'mobi_commerce/widget_image/'.$appcode.'promo4.jpg',
						]
					],
				// image widget
				[
					'widget_label'    => 'Promo 5',
					'widget_code'     => 'widget_image',
					'widget_status'   => '1',
					'widget_position' => '10',
					'widget_data'     => [
						'title'        => '',
						'title_align'  => 'center',
						'mapcode'	   => '<img src="mobi_commerce/widget_image/'.$appcode.'promo5.jpg" alt="">',
						'widget_image' => 'mobi_commerce/widget_image/'.$appcode.'promo5.jpg',
						]
					],
				// product slider widget
				[
					'widget_label'    => 'Best Selling',
					'widget_code'     => 'widget_product_slider',
					'widget_status'   => '1',
					'widget_position' => '11',
					'widget_data'     => [
						'title'              => 'BEST SELLING',
						'title_align'        => 'left',
						'type'               => 'list',
						'maxItems'			 => 3,
						'limit'				 => 15,
						'show_name'          => '1',
						'show_price'         => '1',
						'show_review'        => '1',
						'productslider_type' => 'bestseller',
						'products'           => json_encode([]),
						]
					],
				// image widget
				[
					'widget_label'    => 'Promo 6',
					'widget_code'     => 'widget_image',
					'widget_status'   => '1',
					'widget_position' => '12',
					'widget_data'     => [
						'title'        => '',
						'title_align'  => 'center',
						'mapcode'	   => '<img src="mobi_commerce/widget_image/'.$appcode.'promo6.jpg" alt="">',
						'widget_image' => 'mobi_commerce/widget_image/'.$appcode.'promo6.jpg',
						]
					],
				// product slider widget
				[
					'widget_label'    => 'Recently viewed Products',
					'widget_code'     => 'widget_product_slider',
					'widget_status'   => '1',
					'widget_position' => '13',
					'widget_data'     => [
						'title'              => 'RECENTLY VIEWED',
						'title_align'        => 'left',
						'type'               => 'grid',
						'maxItems'			 => 4,
						'limit'				 => 10,
						'show_name'          => '1',
						'show_price'         => '1',
						'show_review'        => '1',
						'productslider_type' => 'productviewed',
						'products'           => json_encode([]),
						]
					],
			];
		}
		
		$stores = $this->storeManager->getStores();
		foreach($stores as $_store){
			foreach($widgets as $key => $value) {
				$_widget = $value;
				$_widget['widget_app_code'] = $appcode;
				$_widget['widget_store_id'] = $_store->getStoreId();

				//as category should be store group wise
				if($_widget['widget_code'] == 'widget_image_slider') {
					foreach ($_widget['widget_data']['banners'] as $_banner_key => $_banner_value) {
						$_widget['widget_data']['banners'][$_banner_key]['banner_link'] = $this->getBannerLink($categories[$_store->getGroupId()]['categories']);
					}
				}

				if($_widget['widget_code'] == 'widget_category') {
					$_widget['widget_data']['categories'] = json_encode($categories[$_store->getGroupId()]['categories']);
				}
				
				if($_widget['widget_code'] == 'widget_image') {
					$_widget['widget_data']['mapcode'] = str_replace(
						'__CATEGORY_LINK__',
						$this->getBannerLink($categories[$_store->getGroupId()]['categories']),
						$_widget['widget_data']['mapcode']
						);
				}

				if($_widget['widget_code'] == 'widget_product_slider'){
					if($_widget['widget_data']['productslider_type'] == 'selected'){
						$_products = $this->_getRandomProducts($_store->getStoreId(), 10);
						$_widget['widget_data']['products'] = json_encode($_products);
					}
				}

				$_widget['widget_data'] = serialize($_widget['widget_data']);
				$this->mobiadmin3WidgetFactory->create()->setData($_widget)->save();
			}
		}
		//echo '<pre>';print_r($widgets);exit;
	}

	protected function getBannerLink($categories)
	{
		if(!empty($categories))
			return 'category||'.array_rand($categories).'_1';

		return '';
	}

	public function _getRandomProducts($storeId, $limit=10)
	{
        $products = $this->getCoreModel('Magento\Catalog\Model\ResourceModel\Product\Collection')
            ->addAttributeToFilter('status', '1')
			->addAttributeToFilter('visibility', '4')
			->setStoreId($storeId);
        $products->getSelect()->limit($limit);
        
        
		$productsArray = [];
		if($products->getSize() > 0){
			foreach($products as $_product){
				$productsArray[$_product->getId()] = null;
			}
		}

		return $productsArray;
	}

	protected function _create_mobi_media_dir($app_code = null, $app_theme_folder_name = null)
	{
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
        $base_dir=$fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
        
        if(!(is_dir($base_dir.'/mobi_commerce') && file_exists($base_dir.'/mobi_commerce')))
            mkdir($base_dir.'/mobi_commerce', 0777, true);

        if(!(is_dir($base_dir.'/mobi_commerce/widget_image') && file_exists($base_dir.'/mobi_commerce/widget_image')))
            mkdir($base_dir.'/mobi_commerce/widget_image', 0777, true);

        if(!(is_dir($base_dir.'/mobi_commerce/'.$app_code) && file_exists($base_dir.'/mobi_commerce/'.$app_code)))
            mkdir($base_dir.'/mobi_commerce/'.$app_code, 0777, true);

        if(!(is_dir($base_dir.'/mobi_commerce/'.$app_code.'/home_banners') && file_exists($base_dir.'/mobi_commerce/'.$app_code.'/home_banners')))
            mkdir($base_dir.'/mobi_commerce/'.$app_code.'/home_banners', 0777, true);

        if(!(is_dir($base_dir.'/mobi_commerce/'.$app_code.'/appinfo') && file_exists($base_dir.'/mobi_commerce/'.$app_code.'/appinfo')))
            mkdir($base_dir.'/mobi_commerce/'.$app_code.'/appinfo', 0777, true);

        if(!(is_dir($base_dir.'/mobi_commerce/'.$app_code.'/personalizer') && file_exists($base_dir.'/mobi_commerce/'.$app_code.'/personalizer'))){
            mkdir($base_dir.'/mobi_commerce/'.$app_code.'/personalizer', 0777, true);

            if(!empty($app_theme_folder_name)){
                @copy($base_dir."/".'mobi_assets'."/".'v'."/".$this->mobiadmin3Helper->getMobiBaseVersion()."/".'theme_files'."/".$app_theme_folder_name."/".'personalizer'."/".'personalizer.xml', $base_dir."/".'mobi_commerce'."/".$app_code."/".'personalizer'."/".'personalizer.xml');
                @copy($base_dir."/".'mobi_assets'."/".'v'."/".$this->mobiadmin3Helper->getMobiBaseVersion()."/".'theme_files'."/".$app_theme_folder_name."/".'personalizer'."/".'personalizer.css', $base_dir."/".'mobi_commerce'."/".$app_code."/".'personalizer'."/".'personalizer.css');
            }
        }
    }

	public function deleteapps($appcodes = []) 
	{
		$deleteCount = 0;
		if(!empty($appcodes)){
			$appcodes = array_filter(array_unique(array_map('trim', $appcodes)));
			if(!empty($appcodes)){
				$records = $this->mobiadmin3ResourceAppsettingCollectionFactory->create()->addFieldToFilter('app_code', ['in' => $appcodes]);
				if($records->count()){
					foreach($records as $_record){
						$_record->delete();
					}
				}

				$records = $this->mobiadmin3ResourceWidgetCollectionFactory->create()->addFieldToFilter('widget_app_code', ['in' => $appcodes]);
				if($records->count()){
					foreach($records as $_record){
						$_record->delete();
					}
				}

				$records = $this->mobiadmin3ResourceDevicetokensCollectionFactory->create()->addFieldToFilter('md_appcode', ['in' => $appcodes]);
				if($records->count()){
					foreach($records as $_record){
						$_record->delete();
					}
				}

				$records = $this->mobiadmin3ResourceApplicationsCollectionFactory->create()->addFieldToFilter('app_code', ['in' => $appcodes]);
				if($records->count()){
					foreach($records as $_record){
						$deleteCount++;
						$_record->delete();
					}
				}
                
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
                $base_dir=$fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
                
				foreach($appcodes as $_appcode){
                    $dir = $base_dir . '/' . 'mobi_commerce' . '/' . $_appcode;
					if(file_exists($dir) && is_dir($dir)){
						Mage::helper('mobiservices3/mobicommerce')->rrmdir($dir);
					}
				}
			}
		}
        return $deleteCount;
	}
}