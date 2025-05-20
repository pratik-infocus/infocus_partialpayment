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

namespace Infocus\PartialPayments\Model\Payment\MyAccount;

use Infocus\PartialPayments\Helper\Data as HelperData;
use Infocus\PartialPayments\Model\Payment\MyAccount\Processor\AmountProcessor;
use Magento\Payment\Helper\Data as PaymentHelper;
use Infocus\PartialPayments\Model\Payment\MyAccount\Processor\DefaultPaymentProcessor;
use Magento\Customer\Model\Session;
use Infocus\PartialPayments\Model\Payment\MyAccount\Processor\PaymentProcessorInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MyAccountProcessor
 * @package Infocus\PartialPayments\Model\Payment\MyAccount
 */
class MyAccountProcessor
{
    const PAYMENT_METHOD = 'method';
    const PAYMENT = 'payment';

    const PAYMENT_METHOD_INSTANCE = 'method_instance';
    const AMOUNTS = 'amounts';

    /**
     * @var AmountProcessor
     */
    protected $amountProcessor;

    /**
     * @var array
     */
    protected $paymentProcessors;

    /**
     * @var DefaultPaymentProcessor
     */
    protected $defaultPaymentProcessor;

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * MyAccountProcessor constructor
     *
     * @param AmountProcessor $amountProcessor
     * @param DefaultPaymentProcessor $defaultPaymentProcessor
     * @param PaymentHelper $paymentHelper
     * @param Session $session
     * @param array $paymentProcessors
     */
    public function __construct(
        AmountProcessor $amountProcessor,
        DefaultPaymentProcessor $defaultPaymentProcessor,
        PaymentHelper $paymentHelper,
        Session $session,
        HelperData $helperData,
        array $paymentProcessors = []
    ) {
        $this->amountProcessor = $amountProcessor;
        $this->paymentProcessors = $paymentProcessors;
        $this->defaultPaymentProcessor = $defaultPaymentProcessor;
        $this->paymentHelper = $paymentHelper;
        $this->customerSession = $session;
        $this->helperData = $helperData;
    }

    /**
     * @param array $request
     * @return void
     */
    public function process(array $request)
    {
        $amounts = $this->amountProcessor->prepareAmounts($request);
        $paymentInformation = [
            self::AMOUNTS => $amounts,
            self::PAYMENT => $this->getPayment($request),
        ];
        $processor = $this->getPaymentProcessor($this->getPaymentMethodCodeFromRequest($this->getPayment($request)));
        $processor->process($paymentInformation, $request);
    }

    /**
     * @param string $paymentMethodCode
     * @return DefaultPaymentProcessor|PaymentProcessorInterface
     */
    protected function getPaymentProcessor($paymentMethodCode)
    {
        return isset($this->paymentProcessors[$paymentMethodCode])
        && $this->paymentProcessors[$paymentMethodCode] instanceof PaymentProcessorInterface
            ? $this->paymentProcessors[$paymentMethodCode]
            : $this->defaultPaymentProcessor;
    }

    /**
     * @param array $request
     * @return mixed|null
     */
    protected function getPaymentMethodCodeFromRequest(array $request)
    {
        return $this->helperData->getByKey($request, self::PAYMENT_METHOD);
    }

    /**
     * @param array $request
     * @return MethodInterface
     * @throws LocalizedException
     */
    protected function getPaymentMethodInstance(array $request)
    {
        return $this->paymentHelper->getMethodInstance(
            $this->helperData->getByKey($request[self::PAYMENT], self::PAYMENT_METHOD)
        );
    }

    /**
     * @param array $request
     * @return mixed|null
     */
    protected function getPayment(array $request = [])
    {
        return  $this->helperData->getByKey($request, self::PAYMENT);
    }
}
