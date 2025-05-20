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

namespace Infocus\PartialPayments\Model\Sales\ResourceModel;

use Magento\Sales\Model\ResourceModel\Grid;
use Magento\Framework\DB\Select;

/**
 * Class InvoiceGrid
 * @package Infocus\PartialPayments\Model\Sales\ResourceModel
 */
class InvoiceGrid extends Grid
{
    /**
     * @return Select
     */
    protected function getGridOriginSelect()
    {
        if (isset($this->joins['sales_order_payment'])) {
            unset($this->joins['sales_order_payment']);
        }
        $select = parent::getGridOriginSelect();
        $select->joinLeft(
            ['sales_order_payment' => $this->getTable('sales_order_payment')],
            'if(sales_invoice.payment_id IS NOT NULL, '
            . 'sales_invoice.payment_id = sales_order_payment.entity_id, '
            . 'sales_invoice.order_id = sales_order_payment.parent_id)',
            []
        );
        return $select;
    }
}
