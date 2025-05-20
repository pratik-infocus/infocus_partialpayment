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

namespace Infocus\PartialPayments\Block\Adminhtml\Order\Pay;

use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Payment\Block\Form\Container;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\Checks\SpecificationFactory;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Class Amount
 * @package Infocus\PartialPayments\Block\Adminhtml\Order\Pay
 */
class Amount extends Container
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
    * @var Quote
    */
    protected $_sessionQuote;

    /**
     * @var PriceHelper
     */
    protected $_priceHelper;

    /**
     * @var JsonHelper
     */
    protected $_jsonHelper;

    /**
     * Amount constructor
     *
     * @param Context $context
     * @param PaymentHelper $paymentHelper
     * @param SpecificationFactory $methodSpecificationFactory
     * @param HelperData $helperData
     * @param Quote $sessionQuote
     * @param PriceHelper $priceHelper
     * @param JsonHelper $jsonHelper
     * @param array $data
     * @param array $additionalChecks
     */
    public function __construct(
        Context $context,
        PaymentHelper $paymentHelper,
        SpecificationFactory $methodSpecificationFactory,
        HelperData $helperData,
        Quote $sessionQuote,
        PriceHelper $priceHelper,
        JsonHelper $jsonHelper,
        array $data = [],
        array $additionalChecks = []
    ) {
        $this->helperData = $helperData;
        $this->_sessionQuote = $sessionQuote;
        $this->_priceHelper = $priceHelper;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context, $paymentHelper, $methodSpecificationFactory, $data, $additionalChecks);
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
     *  _toHtml wrapper
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->helperData->isEnabled($this->getQuote()->getStore()->getWebsiteId())
            && $this->canPartialPay()
        ) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Check do we have applicable payment method, which can capture partial
     * @return bool
     */
    protected function canPartialPay()
    {
        foreach ($this->getMethods() as $method) {
            if ($method->canUseInternal() && $method->canCapturePartial()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->_sessionQuote->getQuote();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        return \Magento\Framework\View\Element\Template::_prepareLayout();
    }

    /**
     * Retrieve current pay amount
     *
     * @return float
     */
    public function getPayAmount()
    {
        return round($this->getQuote()->getGrandTotal(), 2);
    }

    // /**
    //  * Retrieve minimal pay amount
    //  *
    //  * @return float
    //  */
    // public function getMinPayAmount()
    // {
    //     $minPayAmount = round($this->getQuote()->getGrandTotal() - $this->getQuote()->getSubtotal(), 2);

    //     return max($minPayAmount, HelperData::MINIMAL_AMOUNT_TO_PAY);
    // }

    /**
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->_priceHelper->currency($price, true, false);
    }

    /**
     * @return string
     */
    public function getAvailablePaymentConfigJson()
    {
        $availableMethods = [];
        foreach ($this->getMethods() as $method) {
            $availableMethods[$method->getCode()] = [
                'available' => (int)($method->canUseInternal() && $method->canCapturePartial()),
                'current' => (int)($this->getSelectedMethodCode() == $method->getCode())
            ];
        }
        return $this->_jsonHelper->jsonEncode($availableMethods);
    }

    // /**
    //  * @return bool
    //  */
    // public function canBeZeroAmount()
    // {
    //     return $this->_partialInvoiceHelper->isZeroPaymentPartialInvoiceForAdminEnabled();
    // }
}
