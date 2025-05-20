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

namespace Infocus\PartialPayments\Model;

use Magento\Sales\Model\Order\Invoice as OrderInvoice;

/**
 * Class ZeroPayment
 * @package Infocus\PartialPayments\Model
 */
class ZeroPayment
{
    /**
     * @param OrderInvoice $invoice
     * @return OrderInvoice
     */
    public function createZeroPaymentInvoice($invoice)
    {
        $invoice
            ->setTotalQty(0)
            ->setGrandTotal(0)
            ->setBaseGrandTotal(0)
            ->setShippingAmount(0)
            ->setShippingTaxAmount(0)
            ->setShippingTaxAmount(0)
            ->setBaseShippingTaxAmount(0)
            ->setBaseSubtotal(0)
            ->setBaseSubtotalInclTax(0)
            ->setSubtotal(0)
            ->setSubtotalInclTax(0)
            ->setBaseTaxAmount(0)
            ->setTaxAmount(0)
            ->setDiscountAmount(0)
            ->setBaseDiscountAmount(0)
            ->setDiscountTaxCompensationAmount(0)
            ->setBaseDiscountTaxCompensationAmount(0)
            ->setDiscountDescription(null);
        $invoice->setRequestedCaptureCase(OrderInvoice::CAPTURE_OFFLINE);
        return $invoice;
    }

    /**
     * @param OrderInvoice\Item $invoiceItem
     * @return \Magento\Sales\Model\Order\Invoice\Item
     */
    public function createZeroPaymentInvoiceItem($invoiceItem)
    {
        $invoiceItem->setQty(0)
            ->setRowTotal(0)
            ->setBaseRowTotal(0)
            ->setRowTotalInclTax(0)
            ->setBaseRowTotalInclTax(0)
            ->setTaxAmount(0)
            ->setBaseTaxAmount(0)
            ->setDiscountAmount(0)
            ->setBaseDiscountAmount(0)
            ->setDiscountTaxCompensationAmount(0)
            ->setBaseDiscountTaxCompensationAmount(0)
            ->register();

        return $invoiceItem;
    }

    /**
     * @param OrderInvoice $invoice
     * @return OrderInvoice
     */
    public function processZeroPaymentChainInvoice($invoice)
    {
        return $invoice;
    }

    /**
     * @param OrderInvoice\Item $invoiceItem
     * @return OrderInvoice\Item
     */
    public function processZeroPaymentChainInvoiceItem($invoiceItem)
    {
        return $invoiceItem;
    }
}
