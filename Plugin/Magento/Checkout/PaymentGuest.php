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

namespace Infocus\PartialPayments\Plugin\Magento\Checkout;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Infocus\PartialPayments\Helper\Data as HelperData;

/**
 * Class PaymentGuest
 * @package Infocus\PartialPayments\Plugin\Magento\Checkout
 */
class PaymentGuest extends MethodsDisablerPlugin
{
    /**
     * @param GuestPaymentInformationManagementInterface $informationManagement
     * @param PaymentDetailsInterface $result
     * @return mixed
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetPaymentInformation(
        GuestPaymentInformationManagementInterface $informationManagement,
        $result
    ) {
        $result->setPaymentMethods($this->iteratePaymentMethods($result->getPaymentMethods()));
        return $result;
    }

    protected function iteratePaymentMethods(array $paymentMethods = [])
    {
        foreach ($paymentMethods as $key => $paymentMethod) {
            if ($paymentMethod->getCode() == HelperData::PARTIAL_PAYMENT_METHOD) {
                unset($paymentMethods[$key]);
                break;
            }
        }
        return $paymentMethods;
    }
}
