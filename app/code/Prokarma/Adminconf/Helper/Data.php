<?php
namespace Prokarma\Adminconf\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    protected $_storeManager;
    protected $_objectManager;

    const XML_PATH_INCOMM = 'adminconf/';

    /**
     * Data constructor.
     * @param Context $context
     * @param ObjectManagerInterface $_objectManager
     * @param StoreManagerInterface $_storeManager
     */
    public function __construct(Context $context,
                                ObjectManagerInterface $_objectManager,
                                StoreManagerInterface $_storeManager
    )
    {
        $this->_objectManager = $_objectManager;
        $this->_storeManager  = $_storeManager;
        parent::__construct($context);
    }

    /**
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->_storeManager->getStore()->getId();
        }

        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );

    }


    /**
     * @param $code
     * @param null $storeId
     * @return mixed
     */
    public function getThresholdConfig($code, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->_storeManager->getStore()->getId();
        }

        return $this->getConfigValue(self::XML_PATH_INCOMM . $code, $storeId);

    }

    /**
     * @param $code
     * @return mixed|string
     */
    public function getContinueShoppingLink($code)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $url = $this->getConfigValue(self::XML_PATH_INCOMM . $code, $storeId);
        if ($url != '') {
            $url = $this->_storeManager->getStore()->getBaseUrl().$url;
        } else {
            $url = $this->_storeManager->getStore()->getBaseUrl().$url;
        }

        return $url;

    }

    /**
     * Personal message color from admin configuration
     * @param $code
     * @return string
     */
    public function getPersonalMessageColor($code)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $color = trim($this->getConfigValue(self::XML_PATH_INCOMM . $code, $storeId));

        return $color;

    }

    /**
     * Shipping amount threshold to restrict the standard shipping method
     * @param $code
     * @return string
     */
    public function getShippingRestAmount($code){
        $storeId = $this->_storeManager->getStore()->getId();
        $amount = trim($this->getConfigValue(self::XML_PATH_INCOMM . $code, $storeId));
        return $amount;
    }

    /**
     * Get shipping message
     * @param $code
     * @param null $storeId
     * @return mixed
     */
    public function getShippingMessage($code, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->_storeManager->getStore()->getId();
        }

        return $this->getConfigValue(self::XML_PATH_INCOMM . $code, $storeId);
    }
}