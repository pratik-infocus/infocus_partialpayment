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

namespace Infocus\PartialPayments\Block\Adminhtml\Order;

use Magento\Framework\View\Element\Template;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\MethodInterface;
use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ZeroAmountAbstract
 * @package Infocus\PartialPayments\Block\Adminhtml\Order
 */
abstract class ZeroAmountAbstract extends Template
{
    /**
     * @var PaymentHelper]
     */
    protected $paymentHelper;

    /**
     * @var MethodInterface|null
     */
    protected $method = null;

    /**
     * ZeroAmountAbstract constructor
     *
     * @param Context $context
     * @param PaymentHelper $paymentHelper
     * @param array $data
     * @throws LocalizedException
     */
    public function __construct(
        Context $context,
        PaymentHelper $paymentHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paymentHelper = $paymentHelper;
        $this->method = $paymentHelper->getMethodInstance(HelperData::PARTIAL_PAYMENT_METHOD);
    }

    /**
     * @return CartInterface|Quote
     * @throws NoSuchEntityException
     */
    abstract public function getQuote();

    /**
     *  _toHtml wrapper
     *
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _toHtml()
    {
        if ($this->method->isAvailable($this->getQuote())) {
            return parent::_toHtml();
        }
        return '';
    }
}
