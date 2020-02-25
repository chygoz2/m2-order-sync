<?php
namespace Gloo\OrderStatusSync\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SChemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
            $table = $setup->getConnection()
                ->newTable($setup->getTable('gloo_orderStatusSync_order'))
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity ID'
                )
                ->addColumn(
                    'cart_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Cart ID'
                )
                ->addColumn(
                    'magento_order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Magento Order Id'
                )
                ->addColumn(
                    'increment_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    32,
                    [],
                    'Increment ID'
                )
                ->addColumn(
                    'original_increment_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    32,
                    [],
                    'Original Increment ID'
                )
                ->addColumn(
                    'order_total',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Order Total'
                )
                ->addColumn(
                    'customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Customer Id'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    64,
                    [],
                    'Status'
                )
                ->addColumn(
                    'store_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    64,
                    [],
                    'Store Code'
                )
                ->addColumn(
                    'products',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '16M',
                    [],
                    'Product Object'
                )
                ->addColumn(
                    'address_information',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '16M',
                    [],
                    'Address Info'
                )
                ->addColumn(
                    'payment_method',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    64,
                    [],
                    'Payment Method'
                )
                ->addColumn(
                    'shipping_method',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    64,
                    [],
                    'Shipping Method'
                )
                ->addColumn(
                    'is_sync',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    ['default' => 0],
                    'Is Sync'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created at'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated at'
                )
                ->setComment('Gloo Order Status Sync Module');

                $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}