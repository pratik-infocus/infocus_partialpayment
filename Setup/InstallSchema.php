<?php
/**
 * Infocus
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Infocus-solution.com license that is
 * available through the world-wide-web at this URL:
 * https://infocus-solution.com/license.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @author Infocus Solutions
 * @copyright Copyright (c) 2024 Infocus (https://infocus-solution.com)
 * @package Partial Payment module for Magento 2
 */

namespace Infocus\PartialPayments\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Infocus\PartialPayments\Helper\Data as HelperData;

/**
 * Class InstallSchema
 * @package Infocus\PartialPayments\Setup
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $setup->getConnection();
        $salesOrderTable = $setup->getTable('sales_order');
        $connection->addColumn(
            $salesOrderTable,
            HelperData::IS_ORDER_PARTIALLY_PAID_FLAG,
            [
                'type'     => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default'  => HelperData::ORDER_PAID_FULLY,
                'comment'  => 'Is Order Partially Paid'
            ]
        );
        $salesOrderTable = $setup->getTable('sales_order_grid');
        $connection->addColumn(
            $salesOrderTable,
            HelperData::IS_ORDER_PARTIALLY_PAID_FLAG,
            [
                'type'     => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default'  => HelperData::ORDER_PAID_FULLY,
                'comment'  => 'Is Order Paid Partially'
            ]
        );
        $installer->endSetup();
    }
}
