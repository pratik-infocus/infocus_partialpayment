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

namespace Infocus\PartialPayments\Model;

use Magento\Framework\Model\Context;
use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\OfflinePayments\Block\Form\Checkmo;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\OfflinePayments\Block\Info\Checkmo as InfoBlock;
use Magento\Framework\Registry;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Model\InfoInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Payment
 * @package Infocus\PartialPayments\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Payment extends AbstractMethod
{
    const ALLOW_FOR_FIRST_ORDER = 'allow_for_first_order';
    const CODE = 'partial_payment';

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var string
     */
    protected $_formBlockType = Checkmo::class;

    /**
     * @var string
     */
    protected $_infoBlockType = InfoBlock::class;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * @var CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param PaymentHelper $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param CollectionFactory $orderCollectionFactory
     * @param HelperData $helperData
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        PaymentHelper $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        CollectionFactory $orderCollectionFactory,
        HelperData $helperData,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
    }

    /**
     * @return string
     */
    public function getPayableTo()
    {
        return $this->getConfigData('payable_to');
    }

    /**
     * @return bool
     */
    public function canCaptureFirstInvoice()
    {
        return false;
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return $this
     * @throws LocalizedException
     */
    public function order(InfoInterface $payment, $amount)
    {
        $payAmount = $payment->getAdditionalInformation(HelperData::PAY_AMOUNT_FIELD_NAME);
        if ((double)$payAmount > HelperData::MINIMAL_AMOUNT_TO_PAY) {
            $invoice = $payment->getOrder()->prepareInvoice();
            $invoice->setRequestedCaptureCase(
                $this->canCaptureFirstInvoice() ? Invoice::CAPTURE_OFFLINE : Invoice::NOT_CAPTURE
            );
            $invoice->register();
            $payment->setCreatedInvoice($invoice);
            $payment->getOrder()->addRelatedObject($invoice);
        } elseif (!$payAmount && $payAmount == 0 && $payAmount != "" && $this->helperData->isAdminArea()) {
            $this->createInvoiceForZeroFirstPayment($payment);
        } else {
            $payment->getOrder()->setState(Order::STATE_NEW)->setStatus($this->helperData->getPartialPaymentOrderStatus($this->storeManager->getStore()->getWebsiteId()));
        }
        $payment->setSkipOrderProcessing(true);
        $payment->getOrder()->setData(HelperData::IS_ORDER_PARTIALLY_PAID_FLAG, true);
        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @return bool
     * @throws LocalizedException
     */
    public function createInvoiceForZeroFirstPayment(InfoInterface $payment)
    {
        if (!$this->helperData->isZeroPaymentPartialInvoiceForAdminEnabled()) {
            return false;
        }
        $payment->getOrder()->setData(
            HelperData::IS_ORDER_PARTIALLY_PAID_FLAG,
            HelperData::ORDER_PAID_PARTIALLY
        );

        /** @var Invoice $invoice */
        $invoice = $payment->getOrder()->prepareInvoice();
        $invoice->register();
        if ($payment instanceof InfoInterface) {
            $additionalInformation = $payment->getAdditionalInformation();
            unset($additionalInformation[HelperData::PAY_AMOUNT_FIELD_NAME]);
            $payment->setAdditionalInformation($additionalInformation);
            $payment->setCreatedInvoice($invoice);
        }
        return true;
    }

    /**
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        if (null !== $quote) {
            $storeId = $quote->getStoreId();
            $website = $this->storeManager->getStore($storeId)->getWebsiteId();
            if (!$this->helperData->isEnabled($website)) {
                return false;
            }
        }
        if (!$this->helperData->isEnabled() || $quote
            && !$this->getConfigData(self::ALLOW_FOR_FIRST_ORDER, $quote->getStoreId())
            && !$this->helperData->hasQuoteCustomerPlacedOrders($quote)
        ) {
            return false;
        }
        return parent::isAvailable($quote);
    }
}
