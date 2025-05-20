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

namespace Infocus\PartialPayments\Block\Adminhtml\Order\Invoice\Pay;

use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

/**
 * Class Amount
 * @package Infocus\PartialPayments\Block\Adminhtml\Order\Invoice\Pay
 */
class Amount extends Template
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var PriceHelper
     */
    protected $_priceHelper;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * Amount constructor.
     *
     * @param Context $context
     * @param HelperData $helperData
     * @param PriceHelper $priceHelper
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        Registry $registry,
        PriceHelper $priceHelper,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->_registry = $registry;
        $this->_priceHelper = $priceHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve pay_amount field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return HelperData::PAY_AMOUNT_FIELD_NAME;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getInvoice()->getOrder();
    }

    /**
     * Check do we have applicable payment method, which can catur partial
     *
     * @return bool
     */
    public function canPartialPay()
    {
        return $this->helperData->isOrderPartiallyPaid($this->getOrder());
    }

    /**
     *  _toHtml wrapper
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->helperData->isEnabled() && $this->canPartialPay()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Retrieve current pay amount
     *
     * @return float|null
     */
    public function getPayAmount()
    {
        return $this->getInvoice()->getGrandTotal();
    }

    /**
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->_priceHelper->currency($price, true, false);
    }

    /**
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getInvoice()
    {
        return $this->_registry->registry('current_invoice');
    }


    // /**
    //  * @return bool
    //  */
    // public function canBeZeroAmount()
    // {
    //     return $this->_partialInvoiceHelper->isZeroPaymentPartialInvoiceForAdminEnabled();
    // }
}
