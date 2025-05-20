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

namespace Infocus\PartialPayments\Plugin\Sales\Model\Order;

use Infocus\PartialPayments\Helper\Data as HelperData;
use Infocus\PartialPayments\Model\ResourceModel\Order\Payment\CollectionFactory;
use Magento\Sales\Model\Order;
use Infocus\PartialPayments\Model\ZeroPayment;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice as MagentoInvoice;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Invoice
 * @package Infocus\PartialPayments\Plugin\Sales\Model\Order
 */
class Invoice
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var CollectionFactory
     */
    protected $paymentCollectionFactory;

    /**
     * @var ZeroPayment
     */
    protected $zeroPayment;

    /**
     * InvoiceService constructor.
     *
     * @param HelperData $helperData
     * @param CollectionFactory $paymentCollectionFactory
     * @param ZeroPayment $zeroPayment
     */
    public function __construct(
        HelperData $helperData,
        CollectionFactory $paymentCollectionFactory,
        ZeroPayment $zeroPayment
    ) {
        $this->helperData = $helperData;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        $this->zeroPayment = $zeroPayment;
    }

    /**
     * @param MagentoInvoice $subject
     * @param mixed $result
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return Order
     * @throws LocalizedException
     */
    public function afterGetOrder(MagentoInvoice $subject, $result)
    {
        $payment = $result->getData(OrderInterface::PAYMENT);
        if ($subject->getId() && $subject->getPaymentId() &&
            ($payment === null || ($payment->getId() !== $subject->getPaymentId()))

        ) {
            $paymentCollection = $this->paymentCollectionFactory->create();
            $paymentCollection->setOrderFilter($result)->setInvoiceFilter($subject);
            if ($subject->getId()) {
                foreach ($paymentCollection as $payment) {
                    $payment->setOrder($result);
                }
            }
            $paymentItems = $paymentCollection->getItems();
            if (count($paymentItems)) {
                $payment = reset($paymentItems);
                $result->setData(
                    OrderInterface::PAYMENT,
                    $payment
                );
            }
        }
        return $result;
    }

    /**
     * @param MagentoInvoice $invoice
     * @param \Closure $closure
     * @param mixed ...$args
     * @return mixed
     */
    public function aroundRegister(
        MagentoInvoice $invoice,
        \Closure $closure,
        ...$args
    ) {
        if (($isZeroPayment = $this->helperData->isZeroPayment()) && $invoice->getOrder()->getPayment()->getMethod() =="partial_payment") {
            $this->zeroPayment->createZeroPaymentInvoice($invoice);
            $items = $invoice->getAllItems();
            $invoice->setItems([]);
        } elseif ($isZeroPaymentChain = $this->helperData->isZeroPaymentChain($invoice)) {
            $this->zeroPayment->processZeroPaymentChainInvoice($invoice);
        }
        $result = $closure($args);
        if ($isZeroPayment && $invoice->getOrder()->getPayment()->getMethod() =="partial_payment") {
            $invoice->setItems($items);
            foreach ($items as $invoiceItem) {
                /** @var MagentoInvoice\Item $invoiceItem */
                $this->zeroPayment->createZeroPaymentInvoiceItem($invoiceItem);
            }
            $invoice->getOrder()->addRelatedObject($invoice);
        } elseif (!empty($isZeroPaymentChain)) {
            foreach ($invoice->getAllItems() as $invoiceItem) {
                $this->zeroPayment->processZeroPaymentChainInvoiceItem($invoiceItem);
            }
        }
        return $result;
    }
}
