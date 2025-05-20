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

namespace Infocus\PartialPayments\Model\Payment\Checks;

use Magento\Payment\Model\Checks\SpecificationInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

/**
 * Class CanCapturePartial
 * @package Infocus\PartialPayments\Model\Payment\Checks
 */
class CanCapturePartial implements SpecificationInterface
{
    /**
     * Check if method is applicable for partial payment invoice creation
     *
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return bool
     */
    public function isApplicable(MethodInterface $paymentMethod, Quote $quote)
    {
        return $paymentMethod->canCapturePartial();
    }
}
