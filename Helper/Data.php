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

namespace Infocus\PartialPayments\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Sales\Model\Order\PaymentFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\App\State;
use Infocus\PartialPayments\Model\ResourceModel\Order\Payment\Collection as ResourceCollection;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;
use Infocus\PartialPayments\Logger\Logger;

/**
 * Class Data
 * @package Infocus\PartialPayments\Helper
 */
class Data extends AbstractHelper
{
    const ORDER_PAID_FULLY = 0;
    const ORDER_PAID_PARTIALLY = 1;
    const PAY_AMOUNT_FIELD_NAME = 'pay_amount';
    const IS_ORDER_PARTIALLY_PAID_FLAG = 'is_partially_paid';
    const MINIMAL_AMOUNT_TO_PAY = 1;
    const MINIMAL_TOTAL_DUE = 0;
    const ENABLE_CONFIG_PATH = 'infocus_partialpayments/settings/enabled';
    const AVAILABLE_ORDER_STATUSES_CONFIG_PATH = 'infocus_partialpayments/settings/available_order_statuses';
    const PAYMENT_PATH = 'payment';
    const PARTIAL_PAYMENT_PAGE_LABEL_PATH = 'infocus_partialpayments/settings/partial_payment_title';
    const SEPARATOR = '/';
    const PARTIAL_PAYMENT_METHOD = 'partial_payment';
    const IS_ALLOWED_FOR_FIRST_ORDER = 'allow_for_first_order';
    const PARTIAL_PAID_ORDER_STATUS = 'payment/partial_payment/order_status';
    const ROUND_PAYED_ITEM_QUANTITY_IN_EMAIL = 'infocus_partialpayments/settings/round_payed_item_quantity_in_email';
    const ENABLE_ZERO_PAYMENT_ADMIN_CONFIG_PATH = 'infocus_partialpayments/settings/enable_zero_payment_partial_payment_admin';
    const FULL_PAYMENT_ORDER_STATUS = 'infocus_partialpayments/settings/full_payment_order_status';
    const MINIMUM_AMOUNT_TYPE = 'infocus_partialpayments/settings/minimum_amount_type';
    const MINIMUM_AMOUNT_VALUE = 'infocus_partialpayments/settings/minimum_amount_value';
    const MINIMUM_AMOUNT_PERCENT = 'infocus_partialpayments/settings/minimum_amount_percent';
    const SHOW_CARDS_EXPIRY_MY_ACCOUNT = 'infocus_partialpayments/settings/show_cards_expiry_date_customer_account';
    const MINIMUM_MESSAGE = 'infocus_partialpayments/settings/minimum_message';
    const MINIMUM_THRESHOLD_MESSAGE = 'infocus_partialpayments/settings/threshold_message';
    const LOADING_ANIMATION_ENABLED = 'enable';
    const LOADING_ANIMATION_IMAGE = 'image';
    const LOADING_ANIMATION_TEXT = 'text';
    const LOADING_ANIMATION_UPLOAD_DIR = 'animation/image';
    const ENABLE_LOGGING = 'infocus_partialpayments/settings/enable_module_logging';

    /**
     * @var PaymentFactory
     */
    protected $_orderPaymentFactory;

    /**
     * @var ToOrderPayment
     */
    protected $_paymentConverter;

    /**
     * @var CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var Config
     */
    protected $_orderConfig;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @var JsonHelper
     */
    protected $_jsonHelper = null;

    /**
     * @var Repository
     */
    protected $_assetRepo;

    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * @var ResourceCollection
     */
    protected $resourceCollection;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Data constructor
     *
     * @param Context $context
     * @param PaymentFactory $paymentFactory
     * @param ToOrderPayment $paymentConverter
     * @param CollectionFactory $orderCollectionFactory
     * @param Config $orderConfig
     * @param State $state
     * @param Context $context
     * @param JsonHelper $jsonHelper
     * @param Repository $assetRepo
     * @param Escaper $escaper
     * @param ResourceCollection resourceCollection
     * @param StoreManagerInterface $storeManager
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        PaymentFactory $paymentFactory,
        ToOrderPayment $paymentConverter,
        CollectionFactory $orderCollectionFactory,
        Config $orderConfig,
        State $state,
        JsonHelper $jsonHelper,
        Repository $assetRepo,
        Escaper $escaper,
        ResourceCollection $resourceCollection,
        StoreManagerInterface $storeManager,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->_orderPaymentFactory = $paymentFactory;
        $this->_paymentConverter = $paymentConverter;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_orderConfig = $orderConfig;
        $this->appState = $state;
        $this->_jsonHelper = $jsonHelper;
        $this->_assetRepo = $assetRepo;
        $this->_escaper = $escaper;
        $this->resourceCollection = $resourceCollection;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Prepare new order payment method for partial payment
     *
     * @param string $method
     * @return Payment
     */
    public function preparePayment($method)
    {
        return $this->_orderPaymentFactory->create()->setMethod($method);
    }

    /**
     * Check if order is paid partially
     *
     * @param Order $order
     * @return bool
     */
    public function isOrderPartiallyPaid(Order $order)
    {
        return (bool)$order->getData(self::IS_ORDER_PARTIALLY_PAID_FLAG);
    }

    /**
     * Reset invoice item qtys based on partial pay amount
     *
     * @param Invoice $invoice
     * @param float $payAmount
     * @throws LocalizedException
     * @return void
     */
    public function rebuildInvoice($invoice, $payAmount)
    {
        $order = $invoice->getOrder();
        $paymentAmount = $paidAmount = $payAmount;
        $minimumPay = 0;
        $minimumPay = $this->getMinimumAmountOfOrder($order->getGrandTotal(),$order->getTotalDue(),$order->getStoreId());
        $minimumPay = max(($order->getShippingAmount() - $order->getShippingInvoiced()),$minimumPay);
        $paymentAmount = $paymentAmount - $minimumPay;
        if ($paymentAmount < 0) {
            throw new LocalizedException(__('Minimum payment amount for this order is %1',$minimumPay));
        }
        $payAmount = $payAmount - $order->getShippingAmount() + $order->getShippingInvoiced();
        $totalQty = 0;
        /** @var Invoice\Item $item */
        foreach ($invoice->getAllItems() as $item) {
            $item->getOrderItem()->setIsQtyDecimal(true);
            $itemTotalAmount = $item->getRowTotal()
                - $item->getDiscountAmount()
                + $item->getTaxAmount()
                + $item->getDiscountTaxCompensationAmount();
            if ($payAmount >= $itemTotalAmount) {
                $payAmount -= $itemTotalAmount;
                $totalQty += $item->getQty();
            } elseif ($payAmount > 0) {
                $qtyToInvoice = $item->getQty() * $payAmount / $itemTotalAmount;
                $item->setQty($qtyToInvoice);
                $totalQty += $qtyToInvoice;
                $payAmount = 0;
            } else {
                $item->setQty(0);
            }
        }
        $invoice->setTotalQty($totalQty);
        $invoice->setGrandTotal(0)->setBaseGrandTotal(0);
        $invoice->collectTotals();
        $invoice->setGrandTotal($paidAmount);
        $this->log("Invoice created for amount ".$paidAmount." with order id ".$order->getIncrementId());
    }

    /**
     * @param int $websiteId
     * @return bool
     */
    public function isEnabled($websiteId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::ENABLE_CONFIG_PATH,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function isOrderPaidFully(Order $order)
    {
        return $order->getBaseTotalDue() == self::MINIMAL_TOTAL_DUE;
    }

    /**
     * Retrieve available order statuses
     *
     * @param null $websiteId
     * @return mixed
     */
    public function getAvailableOrderStatuses($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::AVAILABLE_ORDER_STATUSES_CONFIG_PATH,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * @param null $websiteId
     * @return array
     */
    public function getAvailableOrderStatusesArray($websiteId = null)
    {
        $availableOrderStatuses = explode(',', $this->getAvailableOrderStatuses($websiteId));
        return array_combine($availableOrderStatuses, $availableOrderStatuses);
    }

    /**
     * check if logging is enabled
     * @return Boolean
     */
    public function isLogsEnabled()
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_LOGGING,
            ScopeInterface::SCOPE_STORE,
        );
    }

    /**
     * getLogMessagePrefix
     * @return string
     */
    public function getLogMessagePrefix()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return "Store ".$storeId." : ";
    }

    /**
     * @param $message
     * @param false $error
     */
    public function log($message, $error = false)
    {
        if ($this->isLogsEnabled()) {
            $message = $this->getLogMessagePrefix() . $message;
            if ($error) {
                $this->logger->error($message);
            } else {
                $this->logger->info($message);
            }
        }
    }

    /**
     * @return bool
     */
    public function isZeroPaymentPartialInvoiceForAdminEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::ENABLE_ZERO_PAYMENT_ADMIN_CONFIG_PATH,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Check, if customer already had placed any orders
     *
     * @param CartInterface|null $quote
     * @param [] $paymentMethods
     * @return bool
     */
    public function hasQuoteCustomerPlacedOrders(
        CartInterface $quote = null,
        array $paymentMethods = []
    ) {
        if ($quote !== null) {
            if (!$customerId = $quote->getCustomer()->getId()) {
                return false;
            }
            $collection = $this->_orderCollectionFactory->create()
                ->addAttributeToSearchFilter('customer_id', ['eq' => $customerId]);
            if (!empty($paymentMethods)) {
                $collection->getSelect()->joinLeft(
                    ['sop' => $collection->getTable('sales_order_payment')],
                    'main_table.entity_id = sop.parent_id',
                    []
                )->where('sop.method IN (?)', $paymentMethods);
            }
            return (bool)$collection->getSize();
        }
        return false;
    }

    /**
     * @param null $website
     * @return bool|mixed
     */
    public function isAllowedForFirstOrder($website = null)
    {
        return $this->getSettingFromPaymentSection(self::IS_ALLOWED_FOR_FIRST_ORDER, $website);
    }

    /**
     * @param string $settingName
     * @param null $websiteId
     * @param bool $boolean
     * @return bool|mixed
     */
    protected function getSettingFromPaymentSection($settingName, $websiteId = null, $boolean = false)
    {
        $path = $this->buildPath([self::PAYMENT_PATH, self::PARTIAL_PAYMENT_METHOD, $settingName]);
        if ($boolean) {
            return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_WEBSITES, $websiteId);
        }
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_WEBSITES, $websiteId);
    }

    /**
     * @param array $pathParts
     * @return string
     */
    protected function buildPath(array $pathParts)
    {
        return implode(self::SEPARATOR, $pathParts);
    }

    /**
     * @param int $websiteId
     * @return mixed
     */
    public function isVisibleOnFrontEnd($websiteId)
    {
        return $this->scopeConfig->isSetFlag(
            'payment/partial_payment/enable_on_frontend',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * @return bool
     */
    public function isRoundPayedItemQuantityInEmail()
    {
        return $this->scopeConfig->isSetFlag(self::ROUND_PAYED_ITEM_QUANTITY_IN_EMAIL);
    }

    /**
     * Get partially paid orders by customer id
     *
     * @param int $customerId
     * @return Collection
     */
    public function getPartiallyPaidOrders($customerId)
    {
        $orders = $this->_orderCollectionFactory->create($customerId)->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'status',
            ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
        )->setOrder(
            'created_at',
            'desc'
        );
        $orders->addAttributeToFilter(self::IS_ORDER_PARTIALLY_PAID_FLAG, 1);
        return $orders;
    }

    /**
     * @return bool
     */
    public function isAdminArea()
    {
        try {
            $area = $this->appState->getAreaCode();
            return $area === Area::AREA_ADMINHTML;
        } catch (\Exception $e) {
            $this->_logger->error('Can\'t check an app state: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isZeroPayment()
    {
        return $this->isZeroPaymentPartialInvoiceForAdminEnabled()
            && $this->isAdminArea()
            && $this->isZeroAmountInvoice();
    }

    /**
     * @return bool
     */
    public function isZeroAmountInvoice()
    {
        return ($payment = $this->_getRequest()->getParam('payment'))
            && isset($payment[static::PAY_AMOUNT_FIELD_NAME])
            && !$payment[static::PAY_AMOUNT_FIELD_NAME];
    }

    /**
     * @param Invoice $invoice
     * @return bool
     */
    public function isZeroPaymentChain($invoice)
    {
        $order = $invoice->getOrder();
        return $this->isOrderPartiallyPaid($order)
            && count($order->getInvoiceCollection()) > 1
            && 0 == $order->getInvoiceCollection()->getFirstItem()->getGrandTotal();
    }

    /**
     * @param array $array
     * @param string $key
     * @return mixed|null
     */
    public function getByKey(array $array, $key)
    {
        return $array[$key] ?? null;
    }

    /**
     * @param array $array
     * @param string $key
     * @param null $default
     * @return null
     */
    public function getByKeyWithDefault(array $array, $key, $default = null)
    {
        return $this->getByKey($array, $key) ?: $default;
    }

    /**
     * Get value from stores/configuration/design/loading animation
     *
     * @param string $param
     * @return string
     */
    protected function _getLoadingAnimationSetting($param)
    {
        return $this->scopeConfig->getValue(
            'design/loading_animation/' . $param,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get value from store configuration for minimum amount value for partial order
     * @param int $storeId
     * @return int
     */
    public function getMinimumAmountValue($storeId)
    {
        return $this->scopeConfig->getValue(
            self::MINIMUM_AMOUNT_VALUE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get percent value from store configuration for minimum amount value for partial order
     * @param int $storeId
     * @return int
     */
    public function getMinimumAmountPercent($storeId)
    {
        return $this->scopeConfig->getValue(
            self::MINIMUM_AMOUNT_PERCENT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get value from store configuration for minimum amount type for partial order
     * @param int $storeId
     * @return string
     */
    public function getMinimumAmountType($storeId)
    {
        return $this->scopeConfig->getValue(
            self::MINIMUM_AMOUNT_TYPE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get value from store configuration for status or order after full payment of partial order
     *
     * @param int $websiteId
     * @return string
     */
    public function getFullPaymentOrderStatus($websiteId)
    {
        return (string)$this->scopeConfig->getValue(
            self::FULL_PAYMENT_ORDER_STATUS,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        ) ?? 'processing';
    }

    /**
     * Get value from store configuration for showing expiry date in my account section
     *
     * @param int $storeId
     * @return bool
     */
    public function showCardsExpiryFrontend($storeId)
    {
        return (int)$this->scopeConfig->getValue(
            self::SHOW_CARDS_EXPIRY_MY_ACCOUNT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get message from store configuration for minimum amount
     * @param int $storeId
     * @return string
     */
    public function getMinimumMessage($storeId)
    {
        return $this->scopeConfig->getValue(
            self::MINIMUM_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get value from store configuration
     * @param string $param
     * @param mixed|null $scope
     * @return mixed
     */
    public function getConfigurationValue($param,$scope = null)
    {
        return $this->scopeConfig->getValue(
            $param,
            $scope,
        );
    }

    /**
     * Get threshold  message from store configuration for minimum amount
     * @param int $storeId
     * @return string
     */
    public function getMinimumThresholdMessage($storeId)
    {
        return $this->scopeConfig->getValue(
            self::MINIMUM_THRESHOLD_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get value from store configuration for status of order after partial order
     *
     * @param int $websiteId
     * @return string
     */
    public function getPartialPaymentOrderStatus($websiteId)
    {
        return (string)$this->scopeConfig->getValue(
            self::PARTIAL_PAID_ORDER_STATUS,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        ) ?? 'pending';
    }

    /**
     * Get value from store configuration for the label
     *
     * @param int $storeId
     * @return string
     */
    public function getPartialPageLabel($storeId)
    {
        return $this->scopeConfig->getValue(
            self::PARTIAL_PAYMENT_PAGE_LABEL_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: __('My Partial Orders');
    }

    /**
     * Get minimum pay amount value for partial order
     * @param float $orderValue
     * @param float $orderDue
     * @param int $storeId
     * @return float
     */
    public function getMinimumAmountOfOrder($orderValue,$orderDue,$storeId)
    {
        $minimumAmount = 0;
        $minimumType = $this->getMinimumAmountType($storeId);
        if ($minimumType == 'percent')
        {
            $minimumValue = (float)$this->getMinimumAmountPercent($storeId);
            $minimumAmount = ($orderValue * $minimumValue)/100;
        }
        else {
            $minimumAmount = (float)$this->getMinimumAmountValue($storeId);
        }
        if($orderDue < 2 * $minimumAmount)
        {
            $minimumAmount = $orderDue;
        }
        if($minimumAmount > $orderDue)
        {
            $minimumAmount = $orderDue;
        }
        return $minimumAmount;
    }

    /**
     * Get minimum amount from store config
     * @param mixed $orderValue
     * @param mixed $storeId
     * @return mixed
     */
    public function getMinimumAmountConfig($orderValue,$storeId)
    {
        $minimumAmount = 0;
        $minimumType = $this->getMinimumAmountType($storeId);
        if ($minimumType == 'percent')
        {
            $minimumValue = (float)$this->getMinimumAmountPercent($storeId);
            $minimumAmount = ($orderValue * $minimumValue)/100;
        }
        else {
            $minimumAmount = (float)$this->getMinimumAmountValue($storeId);
        }
        return $minimumAmount;
    }

    /**
     * Check if custom animation is enabled
     * @return string
     */
    public function isAnimationEnabled()
    {
        return $this->_getLoadingAnimationSetting(self::LOADING_ANIMATION_ENABLED);
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        if ($this->isAnimationEnabled()) {
            $folderName = self::LOADING_ANIMATION_UPLOAD_DIR;
            $path = $folderName . '/' . $this->_getLoadingAnimationSetting(self::LOADING_ANIMATION_IMAGE);
            $logoUrl = $this->_urlBuilder
                    ->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . $path;
        } else {
            $logoUrl = $this->_assetRepo->getUrlWithParams('images/loader-2.gif', []);
        }
        return $logoUrl;
    }

    /**
     * Get loading animation text
     *
     * @return string
     */
    public function getText()
    {
        if ($this->isAnimationEnabled()) {
            $text = $this->_getLoadingAnimationSetting(self::LOADING_ANIMATION_TEXT);
            return $this->_escaper->escapeQuote(__($text));
        }
        return '';
    }

    // /**
    //  * Get custom animation config as json or as array
    //  *
    //  * @param bool $asJson
    //  * @return string | []
    //  */
    // public function getLoadingAnimationConfig($asJson = false)
    // {
    //     $array = [
    //         self::LOADING_ANIMATION_IMAGE => $this->getImage(),
    //         self::LOADING_ANIMATION_TEXT => $this->getText(),
    //         self::LOADING_ANIMATION_ENABLED => $this->isAnimationEnabled()
    //     ];

    //     if ($asJson) {
    //         return $this->_jsonHelper->jsonEncode($array);
    //     }

    //     return $array;
    // }
}
