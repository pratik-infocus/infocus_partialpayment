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

use Magento\Framework\Api\DataObjectHelper;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Infocus\PartialPayments\Helper\Data as HelperData;
use Infocus\AdobePaymentService\Helper\Data;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class AbstractMyAccountProcessor
 * @package Infocus\PartialPayments\Model\Payment\MyAccount
 */
abstract class AbstractMyAccountProcessor
{
    /**
     * @var OrderPaymentRepositoryInterface
     */
    protected $orderPaymentRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var bool
     */
    protected $isLastOrder = false;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * AbstractMyAccountProcessor constructor.
     *
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param RequestInterface $request
     */
    public function __construct(
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        DataObjectHelper $dataObjectHelper,
        OrderRepositoryInterface $orderRepository,
        HelperData $helperData,
        RequestInterface $request
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->orderRepository = $orderRepository;
        $this->helperData = $helperData;
        $this->request = $request;
    }

    /**
     * @param array $payment
     * @return array|mixed
     */
    protected function getAmounts(array $payment)
    {
        return $this->helperData->getByKeyWithDefault($payment, 'amounts', []);
    }

    /**
     * @return OrderPaymentInterface
     */
    protected function getPayment()
    {
        return $this->orderPaymentRepository->create();
    }

    /**
     * @param array $payment
     * @return mixed|null
     */
    protected function getAdditionalInformation(array $payment)
    {
        return $this->helperData->getByKeyWithDefault($payment, 'additional_information', []);
    }

    /**
     * @param array $request
     * @return mixed|null
     */
    protected function getPaymentRequest(array $request)
    {
        return $this->helperData->getByKey($request, MyAccountProcessor::PAYMENT);
    }

    /**
     * @param array $paymentInformation
     * @param string $param
     * @return mixed|null
     */
    protected function getParamFromAdditionalData(array $paymentInformation, $param)
    {
        return $this->helperData->getByKey($this->getAdditionalInformation($paymentInformation), $param);
    }

    /**
     * @param array $paymentInformation
     * @return array
     */
    protected function prePayCallback(array $paymentInformation)
    {
        return $paymentInformation;
    }

    /**
     * @param array $paymentI
     * @return void
     */
    protected function doPayment(array $paymentI)
    {
        $amounts = $this->getAmounts($paymentI);
        $ordersCount = count($amounts);
        if ($ordersCount == 1) {
            $this->isLastOrder = true;
        }
        $orderCounter = 1;
        foreach ($this->getAmounts($paymentI) as $orderInformation) {
            $paymentI = $this->prePayCallback($paymentI);
            $orderId = $orderInformation['order_id'];
            $amount = $orderInformation['amount'];
            $preparedPayment = $paymentI['payment'];
            $preparedPayment['additional_information']['pay_amount'] = $amount;
            $preparedPayment['checks'] = $this->getChecks();
            $this->payOrder($preparedPayment, $orderId);
            $orderCounter++;
            if ($orderCounter == $ordersCount) {
                $this->isLastOrder = true;
            }
        }
    }

    /**
     * @param array $preparedPayment
     * @param int $orderId
     * @throws \Throwable
     * @return void
     */
    protected function payOrder(array $preparedPayment, $orderId)
    {
        /**
         * @var $method \Magento\Payment\Model\Method\Adapter
         */
        $payment = $this->getPayment();
        if ($payment instanceof Payment) {
            $order = $this->orderRepository->get($orderId);
            $this->dataObjectHelper->populateWithArray(
                $payment,
                $preparedPayment,
                OrderPaymentInterface::class
            );
            $payment->setOrder($order);
            $order->setPayment($payment);
            try {
                $partialPaymentMethods = array(
                    'payment_services_paypal_hosted_fields',
                    'payment_services_paypal_vault'
                );
                $orderPaymentMethod = $payment->getMethod();
                if(in_array($orderPaymentMethod, $partialPaymentMethods)) {
                    $requestData = $this->request->getPostValue();
                    $amountKey = 'order_' . $payment->getOrder()->getId() . '_amount';
                    if (isset($requestData[$amountKey])){
                        $amount = $requestData[$amountKey];
                    }
                    elseif(isset($requestData['payment']['pay_amount'])){
                        $amount = $requestData['payment']['pay_amount'];
                    }
                    $this->helperData->log("Processing authorize request for order: ".$payment->getOrder()->getOrderIncrementId());
                    if(isset($amount)){
                        $payment->authorize(true, $amount);
                    }
                    else{
                        $payment->authorize(true, $payment->getOrder()->getGrandTotal());
                    }
                    $configValue = $this->helperData->getConfigurationValue(Data::SLEEP_AUTHORIZE_PATH,ScopeInterface::SCOPE_STORE);
                    if($configValue > 0){
                        sleep($configValue);
                    }
                    else{
                        sleep(5);
                    }
                }
                $this->helperData->log("Processing capture request for order: ".$payment->getOrder()->getOrderIncrementId());
                $payment->capture();
                $this->orderRepository->save($order);
                $this->doSuccess($payment);
            } catch (\Throwable $throwable) {
                $this->doOnCatch($throwable);
            } finally {
                $this->doFinally($payment);
            }
        }
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return OrderPaymentInterface
     */
    protected function doSuccess(OrderPaymentInterface $payment)
    {
        return $payment;
    }

    /**
     * @param \Throwable $throwable
     * @throws \Throwable
     * @return void
     */
    protected function doOnCatch(\Throwable $throwable)
    {
        throw $throwable;
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return OrderPaymentInterface
     */
    protected function doFinally(OrderPaymentInterface $payment)
    {
        return $payment;
    }

    /**
     * @return array
     */
    protected function getChecks()
    {
        return [
            AbstractMethod::CHECK_USE_CHECKOUT,
            AbstractMethod::CHECK_USE_FOR_COUNTRY,
            AbstractMethod::CHECK_USE_FOR_CURRENCY,
            AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
        ];
    }
}
