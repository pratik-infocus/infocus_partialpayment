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

namespace Infocus\PartialPayments\Plugin\Sales\Block\Order\Tab;

use Magento\Sales\Block\Adminhtml\Order\View\Tab\Transactions as TransactionsTab;
use Infocus\PartialPayments\Helper\Data as HelperData;
use Infocus\PartialPayments\Model\ResourceModel\Order\Payment\Collection;

/**
 * Class Transactions
 * @package Infocus\PartialPayments\Plugin\Sales\Block\Order\Tab
 */
class Transactions
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
     * InvoiceService constructor
     *
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
     * @param TransactionsTab $subject
     * @param bool $result
     * @return bool
     */
    public function afterCanShowTab(TransactionsTab $subject, $result)
    {
        if (!$result) {
            $this->_paymentCollection->setOrderFilter($subject->getOrder());
            foreach ($this->_paymentCollection as $payment) {
                if ($payment->getMethodInstance()->isGateway()) {
                    return true;
                }
            }
        }
        return $result;
    }
}
