<?php
namespace Gloo\OrderStatusSync\Helpers;

class OrderStatusObserverHelper {

    public static function extractOrderData(
        \Magento\Framework\Event\Observer $observer, 
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productLoader
    )
    {
        $order = $observer->getEvent()->getOrder();
        $data = [];
        $data['magentoOrderId'] = $order->getData('entity_id');
        $data['orderTotal'] = $order->getData('total_due');
        $data['incrementId'] = $order->getData('increment_id');
        $data['originalIncrementId'] = $order->getData('original_increment_id');
        $data['status'] = $order->getData('status');
        $data['cartId'] = $order->getData('quote_id');
        $data['storeId'] = $order->getData('store_id');
        $data['storeCode'] = $storeManager->getStore($data['storeId'])->getCode();

        $data['products'] = [];

        foreach($order->getAllItems() as $item){
          $productId = $item->getData('product_id');
          $product = $productLoader->create()->load($productId);
          $data['products'][] = $product->getData();
        }
        $data['serializeProductsData'] = json_encode($data['products']);

        $shippingAddress = $order->getShippingAddress();
        $data['serializeShippingAddressData'] = json_encode($shippingAddress->getData());

        return $data;
    }

    public static function saveOrderData(
        \Gloo\OrderStatusSync\Model\OrderFactory $orderFactory,
        Array $orderData
    )
    {
        $orderFactory = $orderFactory->create();
        $existingOrder = $orderFactory->load($orderData['incrementId'], 'increment_id');
        if($existingOrder){
          $orderFactory = $existingOrder;
        }
        $orderFactory->setMagentoOrderId($orderData['magentoOrderId']);
        $orderFactory->setIncrementId($orderData['incrementId']);
        $orderFactory->setOriginalIncrementId($orderData['originalIncrementId']);
        $orderFactory->setOrderTotal($orderData['orderTotal']);
        $orderFactory->setCartId($orderData['cartId']);
        $orderFactory->setStatus($orderData['status']);
        $orderFactory->setStoreCode($orderData['storeCode']);
        $orderFactory->setProducts($orderData['serializeProductsData']);
        $orderFactory->setAddressInformation($orderData['serializeShippingAddressData']);
        $orderFactory->save();
    }
}