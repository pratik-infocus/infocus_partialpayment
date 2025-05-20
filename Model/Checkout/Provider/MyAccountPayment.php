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

namespace Infocus\PartialPayments\Model\Checkout\Provider;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\MethodInterface;
use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MyAccountPayment
 * @package Infocus\PartialPayments\Model\Checkout\Provider
 */
class MyAccountPayment implements ConfigProviderInterface
{
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var null|MethodInterface
     */
    protected $method = null;

    /**
     * MyAccountPayment constructor.
     * @param PaymentHelper $paymentHelper
     * @throws LocalizedException
     */
    public function __construct(PaymentHelper $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
        $this->method = $paymentHelper->getMethodInstance(HelperData::PARTIAL_PAYMENT_METHOD);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getConfig()
    {
        if (null === $this->method) {
            $this->method = $this->paymentHelper->getMethodInstance(HelperData::PARTIAL_PAYMENT_METHOD);
        }
        return $this->method->isAvailable() ? [
            'payment' => [
                HelperData::PARTIAL_PAYMENT_METHOD => [
                ],
            ],
        ] : [];
    }
}
