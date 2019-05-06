<?php
namespace Mobicommerce\Mobiadmin3\Model;
 
use Mobicommerce\Mobiadmin3\Model\Resource\Applications\CollectionFactory;
 
class CreateappDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $applicationCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $applicationCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $applicationCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
 
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return [];
    }
}