<?php

namespace PMNTS\Gateway\Model\Config;

use Magento\Checkout\Model\ConfigProviderInterface;
use \Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;


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
                    'isSandbox' => $this->getIsSandbox()

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
        $hash_payload = "{$nonce}:1.0:AUD";
        $hash = hash_hmac ("md5", $hash_payload, $shared_secret);

        $base_url = "https://uatsecurepay.incomm.com/payment.aspx?";
        if($is_sandbox) {
            $base_url = "https://uatsecurepay.incomm.com/payment.aspx?";
        }

		
		
        $url = "{$base_url}tx_config=eComm&tx_transdataiv=xZXKGNkK8SM%3D&tx_transdata=J1tph4DTT5bJEiK%2FB4Q%2Ftxcv5UjNVKnX7JeS8BfRUqTr4xuY8UrZlvHfme2%2FnSdI2F24IWB3R9kAirgLHYwxKh9qFrsiDX4GUe7nc9%2FeWJqNPEAwM0TpkjJdtb9IVyiKy6T1WUT%2F5ZxeJ3FL%2FVLhsMITg%2Fc7aWd1B3TOaVGMBaTG620v57sK3CVFLRli%2Baz3u6L3EaJaZ9zjTl6oz2FTu8WvRzAXxouT%2Bnb6r2jGTgTm9zzxCLD5Z2V3zkI0sQvskiEtHaZZ588F1bbGH35eMTCoaK3PIxb9HUZ4uJTzgrra2PEM5IaIlV9kB%2BGNpjnF4MqpsF1SY3etNqtQ7AEoBrKGKG7Sv4%2Fp3SoPbH7HsYSpbo2HrEKMfs3siIBo0p%2BW75ogbLl9UI0wD9nmNnhaMJKOynjYWZ4AoWawu%2FBW43TWiAthJGDp9WvmXCqJdYwAF0peZ0D3iCsmVWYuEnDOuXPRqD65mU1NemZyuxMQvRFxIxPoHwQdsizr560lPqfNjR0rrDBQkP3aowyrczuCb2L%2F%2FYkBXLNGMjoN1pxKwSA%3D";

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
