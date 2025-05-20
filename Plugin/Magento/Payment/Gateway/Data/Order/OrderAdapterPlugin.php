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

namespace Infocus\PartialPayments\Plugin\Magento\Payment\Gateway\Data\Order;

use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderAdapterPlugin
 * @package Infocus\PartialPayments\Plugin\Magento\Payment\Gateway\Data\Order
 */
class OrderAdapterPlugin
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * OrderAdapterPlugin constructor.
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterGetGrandTotalAmount($subject, $result)
    {
        $orderId = $subject->getId();
        $order = $this->orderRepository->get($orderId);
        $totalPaid = $order->getTotalPaid();
        if ($totalPaid) {
            return $result - $totalPaid;
        }
        return $result;
    }
}
