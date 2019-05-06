<?php
namespace Mobicommerce\Mobiservices3\Model\Version3;


/**
 * Added by Yash
 * For rating andf review related functions
 * Date: 27-10-2014
 */
class Review extends \Mobicommerce\Mobiservices3\Model\AbstractModel {

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    protected $customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    protected $review;
    protected $reviewFactory;
    protected $ratingFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,      
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\Session $customerSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Review\Model\Review $review,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory
    )
    {
        $this->registry = $registry;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->review = $review;
        $this->reviewFactory = $reviewFactory;
        $this->ratingFactory = $ratingFactory;
        parent::__construct($context, $registry, $storeManager, $eventManager);
        $this->getModel('Mobicommerce\Mobiservices3\Model\User')->autoLoginMobileUser();
    }

    public function getReviews($data = [])
    {
        $page       = isset($data['page']) ? $data['page'] : 1;
        $limit      = isset($data['limit']) ? $data['limit'] : 20;
        $product_id = isset($data['product_id']) ? $data['product_id'] : 0;
        $store      = isset($data['store']) ? $data['store'] : 0;

        if(empty($store)){
            $store = $this->storeManager->getStore()->getId();
        }

        $reviews = [];
        $collection = $this->review->getCollection()->addStoreFilter($store)
            ->addEntityFilter('product', $product_id)
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setPageSize($limit)->setCurPage($page - 1)
            ->setDateOrder()
            ->addRateVotes();

        foreach ($collection->getItems() as $_collection) {
            $_review = [
                'product_id' => $product_id,
                'created_at' => $_collection->getCreatedAt(),
                'title'      => $_collection->getTitle(),
                'detail'     => $_collection->getDetail(),
                'nickname'   => $_collection->getNickname(),
                ];

            $averageRating = 0;
            $votes = $_collection->getRatingVotes();
            foreach($votes as $vote) {
                $averageRating += $vote->getValue();
            }
            $averageRating = round($averageRating / $votes->count(), 2);
            $_review['averageRating'] = $averageRating;
            $reviews[] = $_review;
        }
        $info = $this->successStatus();
        $info['data']['product_id'] = $product_id;
        $info['data']['reviews'] = $reviews;
        return $info;
    }
    
    /**
     * Submit new review action
     * @param productId=1&nickname=&title=&detail=&ratings[1]=1to5&ratings[2]:1to5&ratings[3]:1to5
     */
    public function submitReview($data = null)
    {        
        $rating = $data['ratings'];
        $productId = isset($data['productId'])?$data['productId']:0;
        if (($product = $this->_initProduct($productId)) && !empty($data)) {
            $session    = $this->customerSession;
            /* @var $session Mage_Core_Model_Session */
            $review     = $this->review->setData($data);
            /* @var $review Mage_Review_Model_Review */

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($product->getId())
                        ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING)
                        ->setCustomerId($this->customerSession->getCustomerId())
                        ->setStoreId($this->storeManager->getStore()->getId())
                        ->setStores([$this->storeManager->getStore()->getId()])
                        ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        $this->getCoreModel('\Magento\Review\Model\Rating')
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->setCustomerId($this->customerSession->getCustomerId())
                        ->addOptionVote($optionId, $product->getId());
                    }

                    $review->aggregate();
                    $responseData = $this->successStatus(__('Your review has been accepted for moderation.'));
                    $productInfo = $this->getModel('Mobicommerce\Mobiservices3\Model\Catalog\Catalog')->productInfo(['product_id' => $productId]);
                    $responseData['data']['product_details'] = $productInfo['data']['product_details'];
                    return $responseData;
                }
                catch (Exception $e) {
                    return $this->errorStatus(__('Unable to post the review.'));
                }
            }
            else {
                $session->setFormData($data);
                if (is_array($validate)) {
                    $errorMessages = [];
                    foreach ($validate as $errorMessage) {
                        $errorMessages[] = $errorMessage;
                    }
                    return $this->errorStatus(implode(",", $errorMessages));
                }
                else {
                    return $this->errorStatus(__('Unable to post the review.'));
                }
            }
            return $this->errorStatus("product_not_available");
        }

    }

    /**
     * Initialize and check product
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function _initProduct($productId = 0)
    {
        $this->eventManager->dispatch('review_controller_product_init_before', ['controller_action' => $this]);
        $categoryId = 0;
        $productId  = (int) $productId;

        $product = $this->_loadProduct($productId);
        if (!$product) {
            return false;
        }

        if ($categoryId) {
            $category = $this->getCoreModel('\Magento\Catalog\Model\CategoryFactory')->load($categoryId);
            $this->registry->register('current_category', $category);
        }

        try {
            $this->eventManager->dispatch('review_controller_product_init', ['product' => $product]);
            $this->eventManager->dispatch('review_controller_product_init_after', [
                'product'           => $product,
                'controller_action' => $this
            ]);
        } catch (Mage_Core_Exception $e) {
            $this->logger->critical($e);
            return false;
        }

        return $product;
    }

    /**
     * Load product model with data by passed id.
     * Return false if product was not loaded or has incorrect status.
     *
     * @param int $productId
     * @return bool|\Magento\Catalog\Model\Product
     */
    protected function _loadProduct($productId)
    {
        if (!$productId) {
            return false;
        }

        $product = $this->getCoreModel('Magento\Catalog\Model\Product')
            ->setStoreId($this->storeManager->getStore()->getId())
            ->load($productId);
        /* @var $product Mage_Catalog_Model_Product */
        if (!$product->getId() || !$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
            return false;
        }

        $this->registry->register('current_product', $product,true);
        $this->registry->register('product', $product,true);

        return $product;
    }

    /**
     * Added by Yash
     * To get rating options to show in product detail page
     * Date: 28-10-2014
     */
    public function _getRatingOptions($data, $storeId = null)
    {
        if(empty($storeId))
            $storeId = $this->storeManager->getStore()->getId();
        
        $ratingsOptions = $this->ratingFactory->create()->getResourceCollection()->addEntityFilter(
            'product' 
        )->setPositionOrder()->setStoreFilter(
            $this->storeManager->getStore()->getId()
        )->addRatingPerStoreName(
            $this->storeManager->getStore()->getId()
        )->load();
        
        $ratingData = [];
        if($ratingsOptions):
            $key = 0;
            foreach($ratingsOptions as $_rating):
                $ratingData[$key] = $_rating->getData();
                $ratingData[$key]['summary'] = round($_rating->getSummary());
                $ratingData[$key]['options'] = [];
                $options = $_rating->getOptions();
                if($options){
                    foreach($options as $_option){
                        $ratingData[$key]['options'][] = $_option->getData();
                    }
                }
                if(empty($ratingData[$key]['summary']))
                    $ratingData[$key]['summary'] = 0;
                $key++;
            endforeach;
        endif;

    	return $ratingData;
    }
}