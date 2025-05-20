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

use Magento\Payment\Helper\Data as PaymentHelper;
use Infocus\PartialPayments\Block\Adminhtml\Order\ZeroAmountAbstract;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ZeroAmount
 * @package Infocus\PartialPayments\Block\Adminhtml\Order\Invoice\Pay
 */
class ZeroAmount extends ZeroAmountAbstract
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * ZeroAmount constructor.
     * @param Context $context
     * @param PaymentHelper $paymentHelper
     * @param Registry $registry
     * @param QuoteRepository $quoteRepository
     * @param array $data
     * @throws LocalizedException
     */
    public function __construct(
        Context $context,
        PaymentHelper $paymentHelper,
        Registry $registry,
        QuoteRepository $quoteRepository,
        array $data = []
    ) {
        parent::__construct($context, $paymentHelper, $data);

        $this->registry = $registry;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        return $this->quoteRepository->get($this->getOrder()->getQuoteId(), [$this->getOrder()->getStoreId()]);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->registry->registry('current_invoice')->getOrder();
    }
}
