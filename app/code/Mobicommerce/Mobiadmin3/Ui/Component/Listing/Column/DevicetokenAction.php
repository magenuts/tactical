<?php
/**
 * @author Mobicommerce Team
 * @copyright Copyright (c) 2018 Mobicommerce (https://www.mobicommerce.com)
 * @package Mobicommerce_Deliverydate
 */

namespace Mobicommerce\Mobiadmin3\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class DevicetokenAction extends Column
{
    protected $_urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('config/indexField')])) {
                    $deleteUrlPath = $this->getData('config/deleteUrlPath') ?: '#';
                    $title = $item['md_devicetoken'];

                    $urlEntityParamName = $this->getData('config/urlEntityParamName');
                    $item[$this->getData('name')] = [
                        'delete' => [
                            'href' => $this->_urlBuilder->getUrl(
                                $deleteUrlPath,
                                [
                                    $urlEntityParamName => $item[$this->getData('config/indexField')]
                                ]
                            ),
                            'confirm' => [
                                'title' => __('Delete %1', $title),
                                'message' => __('Are you sure you want to delete a %1 record?', $title)
                            ],
                            'label' => __('Delete')
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
