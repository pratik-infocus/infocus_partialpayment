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

use Magento\Payment\Helper\Data as PaymentHelper;
use Infocus\PartialPayments\Block\Adminhtml\Order\ZeroAmountAbstract;
use Magento\Framework\View\Element\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ZeroAmount
 * @package Infocus\PartialPayments\Block\Adminhtml\Order\Pay
 */
class ZeroAmount extends ZeroAmountAbstract
{
    /**
     * @var Quote
     */
    protected $sessionQuote;

    /**
     * Amount constructor.
     *
     * @param Context $context
     * @param PaymentHelper $paymentHelper
     * @param Quote $sessionQuote
     * @param array $data
     * @throws LocalizedException
     */
    public function __construct(
        Context $context,
        PaymentHelper $paymentHelper,
        Quote $sessionQuote,
        array $data = []
    ) {
        parent::__construct($context, $paymentHelper, $data);
        $this->sessionQuote = $sessionQuote;
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->sessionQuote->getQuote();
    }
}
