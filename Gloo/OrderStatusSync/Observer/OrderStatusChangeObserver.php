<?php
namespace Gloo\OrderStatusSync\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderStatusChangeObserver implements ObserverInterface
{
  public $logger;
  public $orderFactory;
  public $context;
  public $_storeManager;
  public $_productLoader;

  public function __construct(
    \Psr\Log\LoggerInterface $logger,
    \Gloo\OrderStatusSync\Model\OrderFactory $orderFactory,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Catalog\Model\ProductFactory $productLoader
  )
  {
    $this->logger = $logger;
    $this->orderFactory = $orderFactory;
    $this->_storeManager = $storeManager;
   $this->_productLoader = $productLoader;
  }

  public function execute(\Magento\Framework\Event\Observer $observer)
  {
      try {
        $order = $observer->getEvent()->getOrder();
        $magentoOrderId = $order->getData('entity_id');
        $orderTotal = $order->getData('total_due');
        $incrementId = $order->getData('increment_id');
        $originalIncrementId = $order->getData('original_increment_id');
        $status = $order->getData('status');
        $cartId = $order->getData('quote_id');
        $storeId = $order->getData('store_id');
        $storeCode = $this->_storeManager->getStore($storeId)->getCode();

        $products = [];

        foreach($order->getAllItems() as $item){
          $productId = $item->getData('product_id');
          $product = $this->_productLoader->create()->load($productId);
          $products[] = $product->getData();
        }
        $serializeProductsData = json_encode($products);

        $shippingAddress = $order->getShippingAddress();
        $serializeShippingAddressData = json_encode($shippingAddress->getData());

        $orderFactory = $this->orderFactory->create();
        $orderFactory->setMagentoOrderId($magentoOrderId);
        $orderFactory->setIncrementId($incrementId);
        $orderFactory->setOriginalIncrementId($originalIncrementId);
        $orderFactory->setOrderTotal($orderTotal);
        $orderFactory->setCartId($cartId);
        $orderFactory->setStatus($status);
        $orderFactory->setStoreCode($storeCode);
        $orderFactory->setProducts($serializeProductsData);
        $orderFactory->setAddressInformation($serializeShippingAddressData);
        $orderFactory->save();

      } catch(\Exception $e){
          $this->logger->critical('Exception from Gloo order status change module ===> '. $e->getMessage());
      }

    return $this;
  }
}