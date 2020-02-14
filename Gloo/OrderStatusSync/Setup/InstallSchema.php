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
                    ['identity' => true, 'unsugned' => true, 'nullable' => false, 'primary' => true],
                    'Entity ID'
                )
                ->addColumn(
                    'cart_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsugned' => true, 'nullable' => false],
                    'Cart ID'
                )
                ->addColumn(
                    'magento_order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsugned' => true, 'nullable' => false],
                    'Magento Order Id'
                )
                ->addColumn(
                    'increment_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsugned' => true, 'nullable' => false],
                    'Increment ID'
                )
                ->addColumn(
                    'original_increment_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsugned' => true, 'nullable' => false],
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
                    'cart_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsugned' => true, 'nullable' => false],
                    'Cart ID'
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
                    'is_sync',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    ['default' => 0],
                    'Is Sync'
                )
                ->setComment('Gloo Order Status Sync Module');

                $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}