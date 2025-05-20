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

namespace Infocus\PartialPayments\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class SalesOrderInvoiceBeforeSaveObserver
 * @package Infocus\PartialPayments\Observer
 */
class SalesOrderInvoiceBeforeSaveObserver implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var Invoice $invoice */
        $invoice = $observer->getEvent()->getInvoice();
        if (!$invoice->hasData('payment_id')
            && ($payment = $invoice->getOrder()->getPayment())
            && $payment->getId()
        ) {
            $invoice->setData('payment_id', $invoice->getOrder()->getPayment()->getId());
        }
        return $this;
    }
}
