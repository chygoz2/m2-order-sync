<?php
namespace Gloo\OrderStatusSync\Model;

class Order extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Gloo\OrderStatusSync\Model\ResourceModel\Order');
    }
}