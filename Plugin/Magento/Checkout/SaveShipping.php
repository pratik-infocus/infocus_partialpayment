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

use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class SaveShipping
 * @package Infocus\PartialPayments\Plugin\Magento\Checkout
 */
class SaveShipping extends MethodsDisablerPlugin
{
    /**
     * @param ShippingInformationManagementInterface $informationManagement
     * @param PaymentDetailsInterface $result
     * @return []
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSaveAddressInformation(
        ShippingInformationManagementInterface $informationManagement,
        $result
    ) {
        $result->setPaymentMethods($this->iteratePaymentMethods($result->getPaymentMethods()));
        return $result;
    }
}
