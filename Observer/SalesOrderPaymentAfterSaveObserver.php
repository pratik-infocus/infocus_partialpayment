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
use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\InvoiceRepository;

/**
 * Class SalesOrderPaymentAfterSaveObserver
 * @package Infocus\PartialPayments\Observer
 */
class SalesOrderPaymentAfterSaveObserver implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * SalesOrderInvoiceBeforeSaveObserver constructor.
     * @param HelperData $helperData
     * @param Registry $registry
     * @param InvoiceRepository $invoiceRepository
     */
    public function __construct(
        HelperData $helperData,
        Registry $registry,
        InvoiceRepository $invoiceRepository
    ) {
        $this->helperData = $helperData;
        $this->registry = $registry;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var Invoice $invoice */
        $payment = $observer->getEvent()->getPayment();
        $invoice = $this->registry->registry('current_invoice');
        if ($invoice && !$invoice->getData('payment_id')) {
            $invoice->setData('payment_id', $payment->getId());
            $this->invoiceRepository->save($invoice);
        }
        return $this;
    }
}
