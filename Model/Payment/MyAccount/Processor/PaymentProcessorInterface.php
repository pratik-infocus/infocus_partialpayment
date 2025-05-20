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

namespace Infocus\PartialPayments\Model\Payment\MyAccount\Processor;

/**
 * Interface PaymentProcessorInterface
 * @package Infocus\PartialPayments\Model\Payment\MyAccount\Processor
 */
interface PaymentProcessorInterface
{
    /**
     * @param array $paymentInformation
     * @param array $request
     * @return mixed
     */
    public function process(array $paymentInformation, array $request = []);
}
