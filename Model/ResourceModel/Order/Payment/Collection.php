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

namespace Infocus\PartialPayments\Model\ResourceModel\Order\Payment;

use Magento\Sales\Model\ResourceModel\Order\Payment\Collection as PaymentCollection;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class Collection
 * @package Infocus\PartialPayments\Model\ResourceModel\Order\Payment
 */
class Collection extends PaymentCollection
{
    /**
     * @param Invoice|int $invoice
     * @return $this
     */
    public function setInvoiceFilter($invoice)
    {
        $invoiceId = $invoice;
        if (($invoice instanceof Invoice) && (!$invoiceId = $invoice->getId())) {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
            return $this;
        }
        $this->getSelect()->joinLeft(
            ['invoice' => $this->getTable('sales_invoice')],
            'invoice.payment_id = main_table.entity_id',
            []
        )->where('invoice.entity_id = ?', $invoiceId);
        return $this;
    }

    /**
     * Check for Customer Orders
     * @param mixed $collection
     * @param mixed $paymentMethods
     * @return static
     */
    public function checkCustomerOrder($collection, $paymentMethods)
    {
        $orderCollection = $collection->getSelect()->joinLeft(
            ['sop' => $collection->getTable('sales_order_payment')],
            'main_table.entity_id = sop.parent_id',
            []
        )->where('sop.method IN (?)', $paymentMethods);
        return $orderCollection;
    }
}
