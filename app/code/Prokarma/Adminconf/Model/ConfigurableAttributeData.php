<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Prokarma\Adminconf\Model;

/**
 * Class ConfigurableAttributeData
 * @api
 * @since 100.0.2
 */
class ConfigurableAttributeData extends \Magento\ConfigurableProduct\Model\ConfigurableAttributeData
{
	
	protected $storeManager;
	
	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager
	)
     {
		$this->storeManager = $storeManager;
	}	

    /**
     * @param Attribute $attribute
     * @param array $config
     * @return array
     */
    protected function getAttributeOptionsData($attribute, $config)
    {
		$currencyCode = '';
		if($attribute->getProductAttribute()->getAttributeCode() == 'gift_card'){
			$currencyCode = $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
		}	
        $attributeOptionsData = [];
        foreach ($attribute->getOptions() as $attributeOption) {
            $optionId = $attributeOption['value_index'];
            $attributeOptionsData[] = [
                'id' => $optionId,
                'label' => $currencyCode . $attributeOption['label'],
                'products' => isset($config[$attribute->getAttributeId()][$optionId])
                    ? $config[$attribute->getAttributeId()][$optionId]
                    : [],
            ];
        }
        return $attributeOptionsData;
    }
}
