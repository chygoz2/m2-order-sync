<?php
namespace Gloo\OrderStatusSync\Helpers;

class OrderStatusObserverHelper {

    public static function extractOrderData(
        \Magento\Framework\Event\Observer $observer, 
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $order = $observer->getEvent()->getOrder();
        $data = [];
        $data['magentoOrderId'] = $order->getData('entity_id');
        $data['orderTotal'] = $order->getData('total_due');
        $data['incrementId'] = $order->getData('increment_id');
        $data['createdAt'] = $order->getData('created_at');
        $data['updatedAt'] = $order->getData('updated_at');
        $data['originalIncrementId'] = $order->getData('original_increment_id');
        $data['status'] = $order->getData('status');
        $data['cartId'] = $order->getData('quote_id');
        $data['storeId'] = $order->getData('store_id');
        $data['customerId'] = $order->getData('customer_id');
        $data['storeCode'] = $storeManager->getStore($data['storeId'])->getCode();
        $data['shippingMethod'] = $order->getShippingMethod();
        $data['paymentMethod'] = $order->getPayment()->getMethod();

        $data['products'] = [];

        foreach($order->getAllItems() as $item){
          $data['products'][] = $item->getData();
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
        $orderFactory->setCreatedAt($orderData['createdAt']);
        $orderFactory->setUpdatedAt($orderData['updatedAt']);
        $orderFactory->setCustomerId($orderData['customerId']);
        $orderFactory->setProducts($orderData['serializeProductsData']);
        $orderFactory->setAddressInformation($orderData['serializeShippingAddressData']);
        $orderFactory->setShippingMethod($orderData['shippingMethod']);
        $orderFactory->setPaymentMethod($orderData['paymentMethod']);

        $orderFactory->save();
    }
}