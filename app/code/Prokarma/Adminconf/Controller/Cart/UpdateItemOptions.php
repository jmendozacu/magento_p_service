<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Prokarma\Adminconf\Controller\Cart;

class UpdateItemOptions extends \Magento\Checkout\Controller\Cart\UpdateItemOptions
{
    /**
     * Update product configuration for a cart item
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if(!isset($params['super_attribute'])){
            $params['super_attribute'][$params['s_attr']] = $params['s_attr_val'];
            unset($params['s_attr']);
            unset($params['s_attr_val']);
        }

        if (!isset($params['options'])) {
			$options = explode(',', $params['opt_id']);
            $optionsVal = explode('#', $params['opt_val']);
			if (count($options)) {
				foreach ($options as $key=>$val) {
					if (isset($optionsVal[$key])) {
						$params['options'][$val] = $optionsVal[$key];
					} else {
						$params['options'][$val] = '';
					}
				}	
			} else{
				$params['options'][] = [];
			}
            unset($params['opt_id']);
            unset($params['opt_val']);
        }
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $this->cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t find the quote item.'));
            }

            //$item = $this->cart->updateItem($id, new \Magento\Framework\DataObject($params));
            //If zero entered in the qty field, then remove the cart item
            if ($params['qty'] > 0) {
                $item = $this->cart->updateItem($id, new \Magento\Framework\DataObject($params));
            } else {
                $item = $this->cart->removeItem($id);
				$this->cart->save();
				return $this->resultRedirectFactory->create()->setPath('*/*');
            }

            if (is_string($item)) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item));
            }
            if ($item->getHasError()) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item->getMessage()));
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            $this->_eventManager->dispatch(
                'checkout_cart_update_item_complete',
                ['item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );
            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = __(
                        '%1 was updated in your shopping cart.',
                        $this->_objectManager->get('Magento\Framework\Escaper')
                            ->escapeHtml($item->getProduct()->getName())
                    );
                    $this->messageManager->addSuccess($message);
                }
                return $this->_goBack($this->_url->getUrl('checkout/cart'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError($message);
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);
            if ($url) {
                return $this->resultRedirectFactory->create()->setUrl($url);
            } else {
                $cartUrl = $this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl();
                return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl($cartUrl));
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t update the item right now.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->_goBack();
        }
        return $this->resultRedirectFactory->create()->setPath('*/*');
    }
}
