<?php
namespace Gloo\OrderStatusSync\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderStatusChangeObserver implements ObserverInterface
{
  public $logger;
  public $orderFactory;
  public $context;
  public $_storeManager;
  public $orderStatusHelper;

  public function __construct(
    \Psr\Log\LoggerInterface $logger,
    \Gloo\OrderStatusSync\Model\OrderFactory $orderFactory,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Gloo\OrderStatusSync\Helpers\OrderStatusObserverHelper $orderStatusHelper
  )
  {
    $this->logger = $logger;
    $this->orderFactory = $orderFactory;
    $this->_storeManager = $storeManager;
    $this->orderStatusHelper = $orderStatusHelper;
  }

  public function execute(\Magento\Framework\Event\Observer $observer)
  {
      try {
        $orderData = $this->orderStatusHelper::extractOrderData($observer,$this->_storeManager);
        $this->orderStatusHelper::saveOrderData($this->orderFactory, $orderData);

      } catch(\Exception $e){
          $this->logger->critical('Exception from Gloo order status change module ===> '. $e->getMessage());
      }

    return $this;
  }
}