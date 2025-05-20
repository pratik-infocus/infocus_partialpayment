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

use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Payment
 * @package Infocus\PartialPayments\Plugin\Magento\Checkout
 */
class Payment extends MethodsDisablerPlugin
{
    /**
     * @param PaymentInformationManagementInterface $informationManagement
     * @param PaymentDetailsInterface $result
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterGetPaymentInformation(
        PaymentInformationManagementInterface $informationManagement,
        $result
    ) {
        if (!$this->isVisibleOnFrontend()) {
            $paymentMethods = $result->getPaymentMethods();
            foreach ($paymentMethods as $key => $paymentMethod) {
                if ($paymentMethod->getCode() == HelperData::PARTIAL_PAYMENT_METHOD) {
                    unset($paymentMethods[$key]);
                    break;
                }
            }
            $result->setPaymentMethods($paymentMethods);
        }
        return $result;
    }
}
