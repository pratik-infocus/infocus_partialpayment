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

namespace Infocus\PartialPayments\Plugin\Sales\Model\Service;

use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\State;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService as MagentoInvoiceService;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class InvoiceService
 * @package Infocus\PartialPayments\Plugin\Sales\Model\Service
 */
class InvoiceService
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HttpRequest
     */
    protected $_request;

    /**
     * @var State
     */
    protected $state;

    /**
     * InvoiceService constructor.
     *
     * @param HelperData $helperData
     * @param HttpRequest $request
     * @param State $state
     */
    public function __construct(
        HelperData $helperData,
        HttpRequest $request,
        State $state
    ) {
        $this->helperData = $helperData;
        $this->_request = $request;
        $this->state = $state;
    }

    /**
     * @param MagentoInvoiceService $subject
     * @param Invoice $invoice
     * @return Invoice
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPrepareInvoice(
        MagentoInvoiceService $subject,
        Invoice $invoice,
        ... $args
    ) {
        $order = $invoice->getOrder();
        if ($this->helperData->isEnabled($order->getStore()->getWebsiteId())) {
            if ($this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML &&
                !$order->getPayment()->getAdditionalInformation(HelperData::PAY_AMOUNT_FIELD_NAME)
            ) {
                $paymentData = ['additional_data' => $this->_request->getPost('payment')];
                if ($payment = $this->_request->getPost('payment')) {
                    $paymentData['checks'] = [
                        AbstractMethod::CHECK_USE_INTERNAL,
                        AbstractMethod::CHECK_USE_FOR_COUNTRY,
                        AbstractMethod::CHECK_USE_FOR_CURRENCY,
                        AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                        AbstractMethod::CHECK_ZERO_TOTAL,
                    ];
                    $order->setPayment($this->helperData->preparePayment($payment['method']));
                    $paymentDataObject = new \Magento\Framework\DataObject($paymentData);
                    $order->getPayment()->getMethodInstance()->assignData($paymentDataObject);
                }
            }
            if ($order->getPayment()->getMethodInstance()->canCapturePartial()) {
                $payAmount = (float)$order->getPayment()
                    ->getAdditionalInformation(HelperData::PAY_AMOUNT_FIELD_NAME);
                if ($payAmount && ($order->getTotalDue() > $payAmount)) {
                    $this->helperData->rebuildInvoice($invoice, $payAmount);
                    $order->setData(
                        HelperData::IS_ORDER_PARTIALLY_PAID_FLAG,
                        HelperData::ORDER_PAID_PARTIALLY
                    );
                    $additionalInformation = $order->getPayment()->getAdditionalInformation();
                    unset($additionalInformation[HelperData::PAY_AMOUNT_FIELD_NAME]);
                    $order->getPayment()->setAdditionalInformation($additionalInformation);
                }
            }
        }
        return $invoice;
    }
}
