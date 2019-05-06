<?php
namespace Mobicommerce\Mobiservices3\Block;

class Connector extends \Magento\Framework\View\Element\Template {

	private $_connectorVersion = '3.0.0';
    private $_mobiCacheChecked = false;
    private $_isMobiCacheEnabled = true;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $context,
            $data
        );
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function _getConnectorVersion()
    {
    	return $this->_connectorVersion;
    }

    public function _getConnectorModel($model)
    {
        $modelpath = $this->connectorDefinition($this->_connectorVersion, 'mobiservices');
        if(empty($modelpath))
            return $model;
        else
            return str_replace("Model", 'Model'."\\".$modelpath, $model);
    }

    protected function connectorDefinition($connectorVersion = null, $module = 'mobiservices')
    {
        if(empty($connectorVersion))
            return false;

        $connector = array(
            '3.0.0' => array(
                'mobiadmin' => array(
                    'version'   => '1.0.2',
                    'modelpath' => ''
                    ),
                'mobiservices' => array(
                    'version'   => '3.0.0',
                    'modelpath' => 'Version3'
                    ),
                ),
            );
        if(isset($connector[$connectorVersion][$module]['modelpath']) && !empty($connector[$connectorVersion][$module]['modelpath']))
            return $connector[$connectorVersion][$module]['modelpath'];
        return false;
    }

    public function isMobiCacheEnabled()
    {
        if($this->_mobiCacheChecked){
            return $this->_isMobiCacheEnabled;
        }
        else{
            $this->_isMobiCacheEnabled = $this->scopeConfig->getValue('mobicommerce3_config/cache/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if(!empty($this->_isMobiCacheEnabled)){
                $this->_isMobiCacheEnabled = true;
            }
            else{
                $this->_isMobiCacheEnabled = false;
            }
            $this->_mobiCacheChecked = true;
            $this->_isMobiCacheEnabled = false;
            return $this->_isMobiCacheEnabled;
        }
    }
}