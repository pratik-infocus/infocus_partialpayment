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

namespace Infocus\PartialPayments\Plugin\Sales\Model\ResourceModel;

use Magento\Sales\Model\ResourceModel\Grid as SalesGrid;
use Infocus\PartialPayments\Model\ResourceModel\Order\Payment\Collection;
use Infocus\PartialPayments\Helper\Data as HelperData;

/**
 * Class Grid
 * @package Infocus\PartialPayments\Plugin\Sales\Model\Order
 */
class Grid
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Collection
     */
    protected $_paymentCollection;

    /**
     * Grid constructor
     * @param HelperData $helperData
     * @param Collection $paymentCollection
     */
    public function __construct(
        HelperData $helperData,
        Collection $paymentCollection
    ) {
        $this->helperData = $helperData;
        $this->_paymentCollection = $paymentCollection;
    }

    /**
     * @param SalesGrid $subject
     * @param int $value
     * @param string $fied
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return mixed
     */
    public function beforeRefresh(SalesGrid $subject, $value, $field = null)
    {
        if ($subject->getGridTable() == $subject->getTable('sales_order_grid')) {
            $items = $this->_paymentCollection->setOrderFilter($value)->getItems();
            if (count($items) > 1) {
                $payment = reset($items);
                return [$payment->getId(), 'sales_order_payment.entity_id'];
            }
        }
        return [$value, $field];
    }
}
