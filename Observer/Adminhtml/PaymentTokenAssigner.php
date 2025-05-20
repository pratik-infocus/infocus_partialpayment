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

namespace Infocus\PartialPayments\Observer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Class PaymentTokenAssigner
 * @package Infocus\PartialPayments\Observer\Adminhtml
 */
class PaymentTokenAssigner extends AbstractDataAssignObserver
{
    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * PaymentTokenAssigner constructor.
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     */
    public function __construct(
        PaymentTokenManagementInterface $paymentTokenManagement
    ) {
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     * @return void
     */
    public function execute(Observer $observer)
    {
        $dataObject = $this->readDataArgument($observer);
        $additionalData = $dataObject->getData(OrderPaymentInterface::ADDITIONAL_DATA);
        if (!is_array($additionalData) || !isset($additionalData[PaymentTokenInterface::PUBLIC_HASH])) {
            return;
        }
        $tokenPublicHash = $additionalData[PaymentTokenInterface::PUBLIC_HASH];
        if ($tokenPublicHash === null) {
            return;
        }
        /** @var Payment $paymentModel */
        $paymentModel = $this->readPaymentModelArgument($observer);
        if (!$paymentModel instanceof Payment) {
            return;
        }
        $order = $paymentModel->getOrder();
        $customerId = (int) $order->getCustomerId();
        $paymentToken = $this->paymentTokenManagement->getByPublicHash($tokenPublicHash, $customerId);
        if ($paymentToken === null) {
            return;
        }
        $paymentModel->setAdditionalInformation(
            [
                PaymentTokenInterface::CUSTOMER_ID => $customerId,
                PaymentTokenInterface::PUBLIC_HASH => $tokenPublicHash
            ]
        );
    }
}
