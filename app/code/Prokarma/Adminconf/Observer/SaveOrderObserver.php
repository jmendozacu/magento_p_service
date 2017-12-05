<?php

namespace Prokarma\Adminconf\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Customer;

/**
 * Class SaveOrderObserver
 * @package Prokarma\Adminconf\Observer
 */
class SaveOrderObserver implements ObserverInterface
{
    const NODE_JS_PORT = '3000';
    const NODE_JS_SERVER = '10.42.16.197';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Customer
     */
    protected $_customer;

    /**
     * SaveOrderObserver constructor.
     * @param \Psr\Log\LoggerInterface $loggerInterface
     * @param Customer $customer
     */
    public function __construct(\Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Customer\Model\Customer $customer
    ) {
        $this->logger = $loggerInterface;
        $this->_customer = $customer;
    }

    /**
     * fires when sales_order_save_after is dispatched
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer) {
        $order = $observer->getEvent()->getData();
        $orderId = $order['order_ids'][0];
        if (empty($order)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
            $orderId = $order->getEntityId();
        }
        $orderId = $this->_zerofill($orderId);
        $jsonArray = array('order' => $orderId);
        $jsonObject = json_encode($jsonArray);
        // encode to Json
        $nodeBuffer = $this->_sendNode($jsonObject);
        if (isset($_SESSION['checkout'])) {
            $_SESSION['checkout']['buffer'] = $nodeBuffer;
        }
    }

    /**
     * @param $jsonObject
     * @return string
     * @throws \Exception
     */
    protected function _sendNode($jsonObject) {
        // API
        try {
            ob_start();
            $url = 'http://' . self::NODE_JS_SERVER . ':' . self::NODE_JS_PORT . '/services/V1/api/order/';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonObject);
            $authorization = "authorization: 123123123123";
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                $authorization
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_exec($ch);
            $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($returnCode == 200) {
                $nodeBuffer = ob_get_contents();
                curl_close($ch);
            } else {
                $nodeBuffer = '{"message":"no message"}';
            }
            ob_end_clean();
        } catch (\Exception $e) {
            \Magento\Framework\Profiler::stop('magento');
            throw $e;
        }

        return $nodeBuffer;
    }

    /**
     * @param $num
     * @param int $zerofill
     * @return string
     */
    protected function _zerofill($num, $zerofill = 9) {
        return str_pad($num, $zerofill, '0', STR_PAD_LEFT);
    }
}
