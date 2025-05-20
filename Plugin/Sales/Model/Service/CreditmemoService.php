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

namespace Infocus\PartialPayments\Plugin\Sales\Model\Service;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\Service\CreditMemoService as MagentoCreditMemoService;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order;
use Infocus\PartialPayments\Helper\Data as HelperData;

/**
 * Class CreditmemoService
 * @package Infocus\PartialPayments\Plugin\Sales\Model\Service
 */
class CreditmemoService
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * InvoiceService constructor.
     *
     * @param HelperData $helperData
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(
        HelperData $helperData,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->helperData = $helperData;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @param MagentoCreditMemoService $subject
     * @param CreditmemoInterface $creditmemo
     * @param bool $offlineRequested
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeRefund(
        MagentoCreditMemoService $subject,
        CreditmemoInterface $creditmemo,
        $offlineRequested = false
    ) {
        $order = $creditmemo->getOrder();
        if ($this->helperData->isEnabled($order->getStore()->getWebsiteId())
            && $this->helperData->isOrderPartiallyPaid($order)
            && !$creditmemo->getInvoiceId()
        ) {
            $invoice = $creditmemo->getInvoice();
            if ($invoice && $offlineRequested) {
                $invoice->setIsUsedForRefund(true);
                $invoice->setBaseTotalRefunded(
                    $invoice->getBaseTotalRefunded() + $creditmemo->getBaseGrandTotal()
                );
                $creditmemo->setInvoiceId($invoice->getId());
                $this->invoiceRepository->save($invoice);
            }
        }
        return [$creditmemo, $offlineRequested];
    }
}
