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

namespace Infocus\PartialPayments\Plugin\Sales\Model\Order\Email\Sender;

use Magento\Sales\Model\Order\Email\Sender\InvoiceSender as Subject;
use Magento\Sales\Model\Order\Invoice;
use Infocus\PartialPayments\Model\Flags\FlagInterface;
use Infocus\PartialPayments\Helper\Data as HelperData;

/**
 * Class InvoiceSenderPlugin
 * @package Infocus\PartialPayments\Plugin\Sales\Model\Order\Email\Sender
 */
class InvoiceSenderPlugin
{
    const FLAG_SKIP_SEND_EMAIL_INVOICE = 'skip_send_email_invoice';

    /**
     * @var FlagInterface
     */
    protected $skipEmailCopyOfInvoiceFlag;

    /**
     * InvoiceSenderPlugin constructor.
     *
     * @param FlagInterface $skipEmailCopyOfInvoiceFlag
     */
    public function __construct(
        FlagInterface $skipEmailCopyOfInvoiceFlag
    ) {
        $this->skipEmailCopyOfInvoiceFlag = $skipEmailCopyOfInvoiceFlag;
    }

    /**
     * @param Subject $invoiceSender
     * @param \Closure $proceed
     * @param Invoice $invoice
     * @param bool $forceSyncMode
     * @return bool
     */
    public function aroundSend(
        Subject $invoiceSender,
        \Closure $proceed,
        Invoice $invoice,
        $forceSyncMode = false
    ) {
        if ($this->skipEmailCopyOfInvoiceFlag->hasFlag(self::FLAG_SKIP_SEND_EMAIL_INVOICE)) {
            return false;
        }
        if ($invoice->getOrder()->getPayment()->getMethod() === HelperData::PARTIAL_PAYMENT_METHOD
            && $invoice->getDontSkipInvoiceEmailSendFlag() !== true
            && !$invoice->getOrder()->getCustomerNoteNotify()
            && !$invoice->getEmailSent()
        ) {
            return false;
        }
        return $proceed($invoice, $forceSyncMode);
    }
}
