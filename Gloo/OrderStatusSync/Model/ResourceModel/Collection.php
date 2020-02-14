<?php
namespace Gloo\OrderStatusSync\Model\ResourceModel\Department;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Gloo\OrderStatusSync\Model\Order',
            'Gloo\OrderStatusSync\Model\ResourceModel\Order'
        );
    }
}