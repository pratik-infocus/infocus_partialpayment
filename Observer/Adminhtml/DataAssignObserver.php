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
use \Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Infocus\PartialPayments\Helper\Data as HelperData;

/**
 * Class DataAssignObserver
 * @package Infocus\PartialPayments\Observer\Adminhtml
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * DataAssignObserver constructor
     *
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }
        $paymentInfo = $this->readPaymentModelArgument($observer);
        $value = null;
        $payment = $this->request->getParam('payment');
        if ($payment && isset($payment[HelperData::PAY_AMOUNT_FIELD_NAME])) {
            $value = $payment[HelperData::PAY_AMOUNT_FIELD_NAME];
        } else {
            $value = $additionalData[HelperData::PAY_AMOUNT_FIELD_NAME] ?? null;
        }
        $paymentInfo->setAdditionalInformation(
            HelperData::PAY_AMOUNT_FIELD_NAME,
            $value
        );
        $paymentInfo->setData(
            HelperData::PAY_AMOUNT_FIELD_NAME,
            $value
        );
    }
}
