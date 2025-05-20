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

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Infocus\PartialPayments\Helper\Data as HelperData;

/**
 * Class SalesOrderBeforeSaveObserver
 * @package Infocus\PartialPayments\Observer
 */
class SalesOrderBeforeSaveObserver implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * SalesOrderBeforeSaveObserver constructor.
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * Set completion_date date
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($this->helperData->isOrderPartiallyPaid($order) && $this->helperData->isOrderPaidFully($order)) {
            $order->setData(HelperData::IS_ORDER_PARTIALLY_PAID_FLAG, HelperData::ORDER_PAID_FULLY);
        }
        return $this;
    }
}
