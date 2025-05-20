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

namespace Infocus\PartialPayments\Observer;

use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Class PaymentMethodAvailabilityCheck
 * @package Infocus\PartialPayments\Observer
 */
class PaymentMethodAvailabilityCheck implements ObserverInterface
{
    /**
     * @var array
     */
    protected $_methodCodeArray;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * PaymentMethodAvailabilityCheck constructor
     *
     * @param HelperData $helperData
     * @param CollectionFactory $orderCollectionFactory
     * @param array $methodCodeArray
     */
    public function __construct(
        HelperData $helperData,
        CollectionFactory $orderCollectionFactory,
        array $methodCodeArray
    ) {
        $this->helperData = $helperData;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_methodCodeArray = $methodCodeArray;
    }

    /**
     * Set completion_date date
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $methodInstance = $observer->getEvent()->getMethodInstance();
        $result = $observer->getEvent()->getResult();
        $quote = $observer->getEvent()->getQuote();
        if (in_array($methodInstance->getCode(), $this->getMethodCodeArray())
            && $this->helperData->hasQuoteCustomerPlacedOrders($quote, $this->getMethodCodeArray())
        ) {
            if ($quote && ($customerId = $quote->getCustomer()->getId())) {
                $collection = $this->_orderCollectionFactory->create()
                    ->addAttributeToSearchFilter('customer_id', ['eq' => $customerId])
                    ->addAttributeToSearchFilter('status', ['in' => $this->getAvailableOrderStatuses($quote)]);
                if ($collection->getSize()) {
                    $result->setData('is_available', true);
                    return $this;
                }
            }
            $result->setData('is_available', false);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getMethodCodeArray()
    {
        return $this->_methodCodeArray;
    }

    /**
     * @param Quote $quote
     * @return array
     */
    public function getAvailableOrderStatuses(Quote $quote)
    {
        return $this->helperData->getAvailableOrderStatusesArray($quote->getStore()->getWebsiteId());
    }
}
