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

use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Payment\Model\Info;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

/**
 * Class PartialPaymentAvailabilityChecker
 * @package Infocus\PartialPayments\Model
 */
class PartialPaymentAvailabilityChecker
{
    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * PartialPaymentAvailabilityChecker constructor
     *
     * @param HelperData $helper
     */
    public function __construct(HelperData $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function isAvailable(OrderInterface $order)
    {
        return !$this->isNotAvailable($order);
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function isNotAvailable(OrderInterface $order)
    {
        /**
         * @var $order Order
         * @var $payment Info
         */
        $payment = $order->getPayment();
        $payAmount = (float)$payment->getAdditionalInformation(HelperData::PAY_AMOUNT_FIELD_NAME);
        if (!$payAmount) {
            return true;
        }
        return (!$this->helper->isEnabled($order->getStore()->getWebsiteId())
            && $order->getTotalDue() != $payAmount
        );
    }
}
