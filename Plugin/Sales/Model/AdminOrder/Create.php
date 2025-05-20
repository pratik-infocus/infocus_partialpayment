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

namespace Infocus\PartialPayments\Plugin\Sales\Model\AdminOrder;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Sales\Model\AdminOrder\Create as AdminOrderCreate;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class Create
 * @package Infocus\PartialPayments\Plugin\Sales\Model\AdminOrder
 */
class Create
{
    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Create constructor.
     *
     * @param InvoiceSender $invoiceSender
     * @param HelperData $helperData
     */
    public function __construct(
        InvoiceSender $invoiceSender,
        HelperData $helperData
    ) {
        $this->invoiceSender = $invoiceSender;
        $this->helperData = $helperData;
    }

    /**
     * @param AdminOrderCreate $subject
     * @param Order $order
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateOrder(AdminOrderCreate $subject, $order)
    {
        $isPartial = $this->helperData->isOrderPartiallyPaid($order);
        /** @var Invoice $invoice */
        if ($isPartial
            && ($invoice = $this->getInvoice($order))
            && $invoice->getState() == Invoice::STATE_PAID
            && !$invoice->getEmailSent()
        ) {
            $this->invoiceSender->send($invoice);
        }
        return $order;
    }

    /**
     * @param Order $order
     * @return Invoice|null
     */
    protected function getInvoice($order)
    {
        $payment = $order->getPayment();
        $invoice = $payment->getCreatedInvoice();
        if ($invoice) {
            return $invoice;
        } elseif (!$invoice && $order->getRelatedObjects()) {
            foreach ($order->getRelatedObjects() as $object) {
                if ($object instanceof Invoice) {
                    return $object;
                }
            }
        }
        return null;
    }
}
