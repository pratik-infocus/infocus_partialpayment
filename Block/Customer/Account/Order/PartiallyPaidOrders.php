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

namespace Infocus\PartialPayments\Block\Customer\Account\Order;

use Infocus\PartialPayments\Helper\Data as HelperData;
use Infocus\PartialPayments\Model\Payment\AmountToPay;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\FormatInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Block\Order\History;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\Order\Config;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Store\Model\Store;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PartiallyPaidOrders
 * @package Infocus\PartialPayments\Block\Customer\Account\Order
 */
class PartiallyPaidOrders extends History
{
    const LABEL = 'label';
    const PATH = 'path';
    const AVAILABLE_PAYMENT_METHODS = 'available_payment_methods';

    /**
     * @var AmountToPay
     */
    protected $amountToPay;

    /**
     * @var FormatInterface
     */
    protected $localeFormat;

    /**
     * @var HelperData
     */
    protected $dataHelper;

    /**
     * @var array
     */
    protected $allowedOrders = [];

    /**
     * @var array
     */
    protected $disallowedOrders = [];

    /**
     * @var CollectionFactoryInterface
     */
    protected $orderCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * PartiallyPaidOrders constructor.
     *
     * @param Context $context
     * @param CollectionFactory $orderCollectionFactory
     * @param Session $customerSession
     * @param Config $orderConfig
     * @param AmountToPay $amountToPay
     * @param FormatInterface $format
     * @param HelperData $dataHelper
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $orderCollectionFactory,
        Session $customerSession,
        Config $orderConfig,
        AmountToPay $amountToPay,
        FormatInterface $format,
        HelperData $dataHelper,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->amountToPay = $amountToPay;
        $this->localeFormat = $format;
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        Template::_construct();
    }

    /**
     * Check if Partial Payment module is enabled for store
     * @return bool
     */
    public function isPartialPaymentEnabled()
    {
        return $this->dataHelper->isEnabled($this->_storeManager->getStore()->getWebsiteId());
    }

    /**
     * @return bool|Collection
     */
    public function getOrders()
    {
        $collection = parent::getOrders();
        if ($collection) {
            $collection->addAttributeToFilter(HelperData::IS_ORDER_PARTIALLY_PAID_FLAG, 1);
            $newCollection = $this->getOrderCollectionFactory()->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('entity_id', ['in' => $collection->getAllIds()]);
            $removeItemIds = [];
            foreach ($newCollection as $order) {
                if ($this->canInvoiceOrder($order)) {
                    $this->allowedOrders[] = $order;
                } else {
                    $this->disallowedOrders[] = $order;
                    $removeItemIds[] = $order->getId();
                }
            }
            if (!empty($removeItemIds)) {
                $collection->addAttributeToFilter('entity_id', ['nin' => $removeItemIds]);
            }
        }
        return $collection;
    }

    /**
     * @return mixed
     */
    public function getOrderCollectionFactory()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->orderCollectionFactory;
    }

    // /**
    //  * @return array
    //  */
    // public function getAllowedOrders()
    // {
    //     return $this->allowedOrders;
    // }

    // /**
    //  * @return array
    //  */
    // public function getDisallowedOrders()
    // {
    //     return $this->disallowedOrders;
    // }

    /**
     * @return array|mixed
     */
    public function getActions()
    {
        $defaultActions = [
            'view' => [
                self::LABEL => 'View Order',
                self::PATH => 'sales/order/view',
            ],
        ];
        $actions = $this->getData('actions');
        if (!$actions || !is_array($actions)) {
            $actions = [];
        }
        return array_merge($defaultActions, $actions);
    }

    /**
     * @param string $action
     * @param OrderInterface $order
     * @return string
     */
    public function getUrlWithOrderParameter($action, OrderInterface $order)
    {
        return $this->getUrl($action, ['order_id' => $order->getEntityId()]);
    }

    /**
     * @param array $array
     * @param string $key
     * @return mixed|null
     */
    public function getDataByKeyFromArray(array $array, $key)
    {
        return $this->dataHelper->getByKey($array, $key);
    }

    /**
     * @param OrderInterface $order
     * @return float|null
     */
    public function getAmountToPay(OrderInterface $order)
    {
        return $this->amountToPay->getAmountToPay($order);
    }

    // /**
    //  * @param OrderInterface $order
    //  * @return bool
    //  */
    // public function isPaymentAllowed(OrderInterface $order)
    // {
    //     return (double)$this->getAmountToPay($order) != (double)0;
    // }

    // /**
    //  * @param array $params
    //  * @return string
    //  */
    // public function getPaymentUrl(array $params = [])
    // {
    //     return $this->getUrl('infocus_partialpayments/order/pay', $params);
    // }

    /**
     * @return mixed
     */
    public function getAvailablePaymentMethods()
    {
        return $this->getData(self::AVAILABLE_PAYMENT_METHODS);
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getPriceFormat()
    {
        $store = $this->_storeManager->getStore();
        return $this->localeFormat->getPriceFormat(null, $store->getCurrentCurrencyCode());
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function getMinimalAmountToPay(OrderInterface $order)
    {
        $minimumPay=0;
        $minimumPay = $this->dataHelper->getMinimumAmountOfOrder($order->getGrandTotal(),$order->getTotalDue(),$order->getStoreId());
        return max(($order->getShippingAmount() - $order->getShippingInvoiced()),$minimumPay);
    }

    /**
     * get minimum amount from store config
     * @return void
     */
    public function getMinimalAmountConfig(OrderInterface $order)
    {
        $minimumAmount = $this->dataHelper->getMinimumAmountConfig($order->getGrandTotal(),$order->getStoreId());
        return $minimumAmount;
    }

    /**
     * @param OrderItemInterface|Order\Item $item
     * @return bool
     */
    public function canInvoice(OrderItemInterface $item)
    {
        $qtys = [];
        if ($item->getLockedDoInvoice()) {
            return false;
        }
        if ($item->isDummy()) {
            if ($item->getHasChildren()) {
                foreach ($item->getChildrenItems() as $child) {
                    if (empty($qtys)) {
                        if ($child->getQtyToInvoice() > 0) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } elseif ($item->getParentItem()) {
                $parent = $item->getParentItem();
                if (empty($qtys)) {
                    return $parent->getQtyToInvoice() > 0;
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $item->getQtyToInvoice() > 0;
        }
    }

    /**
     * @param OrderInterface|Order $order
     * @return bool
     */
    public function canInvoiceOrder(OrderInterface $order)
    {
        if ($order->canUnhold()) {
            return false;
        }
        foreach ($order->getAllItems() as $item) {
            if ($this->canInvoice($item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * whether to show expiry date or not in frontend
     * @return bool
     */
    public function getShowExpiryYesNo()
    {
        return $this->dataHelper->showCardsExpiryFrontend($this->storeManager->getStore()->getId());
    }

    /**
     * get minimum message
     * @return string
     */
    public function getMinimumMessage()
    {
        return $this->dataHelper->getMinimumMessage($this->storeManager->getStore()->getId());
    }

    /**
     * get minimum threshold message
     * @return string
     */
    public function getMinimumThresholdMessage()
    {
        return $this->dataHelper->getMinimumThresholdMessage($this->storeManager->getStore()->getId());
    }
}
