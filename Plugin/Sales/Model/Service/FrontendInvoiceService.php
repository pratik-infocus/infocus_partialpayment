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

use Infocus\PartialPayments\Model\PartialPaymentAvailabilityChecker;
use Magento\Sales\Model\Service\InvoiceService as MagentoInvoiceService;
use Magento\Sales\Model\Order;
use \Magento\Framework\Exception\LocalizedException;

/**
 * Class FrontendInvoiceService
 * @package Infocus\PartialPayments\Plugin\Sales\Model\Service
 */
class FrontendInvoiceService
{
    /**
     * @var PartialPaymentAvailabilityChecker
     */
    protected $partialPaymentAvailabilityChecker;

    /**
     * FrontendInvoiceService constructor.
     *
     * @param PartialPaymentAvailabilityChecker $partialPaymentAvailabilityChecker
     */
    public function __construct(
        PartialPaymentAvailabilityChecker $partialPaymentAvailabilityChecker
    ) {
        $this->partialPaymentAvailabilityChecker = $partialPaymentAvailabilityChecker;
    }

    /**
     * @param MagentoInvoiceService $subject
     * @param Order $order
     * @throws LocalizedException
     */
    public function beforePrepareInvoice(
        MagentoInvoiceService $subject,
        Order $order
    ) {
        if ($this->partialPaymentAvailabilityChecker->isNotAvailable($order)) {
            throw new LocalizedException(
                __('Only full outstanding amount could be paid.')
            );
        }
    }
}
