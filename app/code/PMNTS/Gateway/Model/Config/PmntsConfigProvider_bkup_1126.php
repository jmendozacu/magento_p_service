<?php

namespace PMNTS\Gateway\Model\Config;

use Magento\Checkout\Model\ConfigProviderInterface;
use \Magento\Customer\Helper\Session\CurrentCustomer;


class PmntsConfigProvider implements ConfigProviderInterface
{

    /**
    * @var string[]
    */
    protected $methodCode = \PMNTS\Gateway\Model\Payment::CODE;
    protected $method;
    protected $currentCustomer;



    /**
     * @param \Magento\Payment\Helper\Data $paymentHelper
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        CurrentCustomer $currentCustomer
    ) {
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->currentCustomer = $currentCustomer;
    }


    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                'pmntsGateway' => [
                    'iframeSrc' => $this->getIframeSrc(),
                    'isIframeEnabled' => $this->getIframeEnabled(),
                    'fraudFingerprintSrc' => $this->getFraudFingerprintSource(),
                    'isSandbox' => $this->getIsSandbox(),
                    'canSaveInfo' => $this->canSaveInfo(),
                    'customerHasSavedInfo' => $this->customerHasSavedInfo()
                ]
            ]
        ];
        return $config;
    }

    private function getConfigValue($key) {
      return $this->method->getConfigData($key);
    }

    private function getIframeEnabled() {
      return (bool)$this->getConfigValue("iframe_tokenization");
    }

    private function getFraudFingerprintSource() {
      $is_sandbox = $this->getConfigValue("sandbox_mode");
      if ($is_sandbox) {
          return "https://ci-mpsnare.iovation.com/snare.js";
      } else {
          return "https://mpsnare.iesnare.com/snare.js";
      }
    }

    private function getIframeSrc() {
        $is_sandbox = $this->getConfigValue("sandbox_mode");
        $username = $this->getConfigValue("username");
        $shared_secret = $this->getConfigValue("shared_secret");
        $nonce = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
        $hash_payload = "{$nonce}:1.0:USD";
        $hash = hash_hmac ("SHA256", $hash_payload, $shared_secret);

        $base_url = "https://uatsecurepay.incomm.com/payment.aspx?";
        if($is_sandbox) {
            $base_url = "https://uatsecurepay.incomm.com/payment.aspx?";
        }

		
			//$cart = Mage::getModel('checkout/cart')->getQuote()->getData();
			//print_r($cart);
			
			// $cart = Mage::helper('checkout/cart')->getCart()->getItemsCount();
			// print_r($cart);
			
			// $session = Mage::getSingleton('checkout/session');
			// foreach ($session->getQuote()->getAllItems() as $item) {
				// echo $item->getName();
				// Zend_Debug::dump($item->debug());
			// }		
		    // $items = Mage::getModel('checkout/cart')->getQuote()->getAllItems();
			// $items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();

			// foreach($items as $item) {
				// echo 'ID: '.$item->getProductId().'<br />';
				// echo 'Name: '.$item->getName().'<br />';
				// echo 'Sku: '.$item->getSku().'<br />';
				// echo 'Quantity: '.$item->getQty().'<br />';
				// echo 'Price: '.$item->getPrice().'<br />';
				// echo "<br />";
			// }
			// $totalItems = Mage::getModel('checkout/cart')->getQuote()->getItemsCount();
			// $totalQuantity = Mage::getModel('checkout/cart')->getQuote()->getItemsQty();	
		
		//$url = "{$base_url}/v2/{$username}/{$nonce}/AUD/1.0/{$hash}
        $url = "{$base_url}tx_config=ecomm&amp;tx_payment_config=AmexSandbox&amp;txType=AUTHONLY&amp;tx_transdataiv=TWlPf1CIQbA&amp;tx_transdata=DMiPaf8%2btb%2bWp8QfJ%2fNOYEaiEtzAaIwT3CTTiHal525ACgEmizspM2JshAhBQ0ZGHrhFaX1Ep9NikkAGRRFyndvD0udTwPOh28tb8TVv%2fcRzQEZnFkfFisCJStfidSksqtKTUyXMfb7LQnYLCyVsgfQh0Hw0iU6KNXAleCV1gCqZW3IQZy4pTkym4ckgUjjoNoPOuOCin8JxjPkgsbnvc0MUd3Q9vvbpb7GsDJGwTPtAKw5PVRAtJUXywMckLZpV13T3fndmRX3GSI%2f6Q2YzN3yhJS9YJCEhA8DrXq1fG5qDIVf%2fjKDTAfc5pP9%2bsE21V7rTQMu2jexToIf0pqA9ZY12brZur%2bW1j30xxOcm3bFJW9p0eQYc6ibYKZdLYT5ru5oizCMEoCV6QcmIu5df477UJ0ANTCg3SrU3T%2fdMlT%2b%2fmHPwE%2bX2%2b%2f8XI25P8GsmWEtuPA3B8VqH5cMuca3JO4RKD%2bT5QTyBYyAfQ4WvaAquADhUwpqN0WuUUYfnznCgFmg0CVKtwAim%2fcaM1CiUvzVKo4FSqwqqpCJI8zsfON8RLab8yxYsJTyBD5yB9UhJWCsdV0SgzaaKIwB%2bOiJg1a5LqXD0zo1J2FTmPpd6lPkfN9Q4IlIqHCtgkCZQryLC";

        // If CSS URL is set, generate signature, add to iframe URL
        $css_url = $this->getConfigValue("iframe_css");
        if (strlen($css_url) > 0) {
          $css_signature = hash_hmac("md5", $css_url, $shared_secret);
          //$url = $url . "&css={$css_url}&css_signature={$css_signature}";
        }

        return $url;
    }

    private function getIsSandbox() {
      $is_sandbox = $this->getConfigValue("sandbox_mode");

      return $is_sandbox;
    }

}
