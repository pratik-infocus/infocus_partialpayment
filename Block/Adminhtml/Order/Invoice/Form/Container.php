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

namespace Infocus\PartialPayments\Block\Adminhtml\Order\Invoice\Form;

use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Vault\Model\VaultPaymentInterface;
use Magento\Payment\Block\Form\Container as MagentoContainer;
use Magento\Payment\Helper\Data as PaymentHelperData;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Checks\SpecificationFactory;
use Magento\Framework\Registry;
use Magento\Quote\Model\QuoteRepository;

/**
 * Class Container
 * @package Infocus\PartialPayments\Block\Adminhtml\Order\Invoice\Form
 */
class Container extends MagentoContainer
{
    const CAPTURE_CASE_ONLINE = 'online';
    const CAPTURE_CASE_OFFLINE = 'offline';
    const CAPTURE_CASE_NOT_CAPTURE = 'not_capture';

    /**
     * @var PaymentHelperData
     */
    protected $_paymentHelper;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var QuoteRepository
     */
    protected $_quoteRepository;

    /**
     * @var JsonHelper
     */
    protected $_jsonHelper;

    /**
     * Container constructor.
     *
     * @param Context $context
     * @param PaymentHelperData $paymentHelper
     * @param SpecificationFactory $methodSpecificationFactory
     * @param Registry $registry
     * @param HelperData $helperData
     * @param QuoteRepository $quoteRepository
     * @param JsonHelper $jsonHelper
     * @param array $data
     * @param array $additionalChecks
     */
    public function __construct(
        Context $context,
        PaymentHelperData $paymentHelper,
        SpecificationFactory $methodSpecificationFactory,
        Registry $registry,
        HelperData $helperData,
        QuoteRepository $quoteRepository,
        JsonHelper $jsonHelper,
        array $data = [],
        array $additionalChecks = []
    ) {
        parent::__construct($context, $paymentHelper, $methodSpecificationFactory, $data, $additionalChecks);
        $this->_coreRegistry = $registry;
        $this->helperData = $helperData;
        $this->_quoteRepository = $quoteRepository;
        $this->_jsonHelper = $jsonHelper;
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        return $this->_quoteRepository->get($this->getOrder()->getQuoteId(), [$this->getOrder()->getStoreId()]);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_invoice')->getOrder();
    }

    /**
     * Check is order paid partially
     *
     * @return bool
     */
    public function isOrderPartiallyPaid()
    {
        return $this->helperData->isOrderPartiallyPaid($this->getOrder());
    }

    /**
     * Check if Infocus_PartialPayments module is enabled
     *
     * @return bool
     */
    public function isPartialPaymentEnabled()
    {
        return $this->helperData->isEnabled($this->getOrder()->getStore()->getWebsiteId());
    }

    /**
     * Retrieve url for loading blocks
     *
     * @return string
     */
    public function getLoadBlockUrl()
    {
        return $this->getUrl('sales/order_create/loadBlock');
    }

    /**
     * Retrieve available capture case for
     *
     * @return string
     */
    public function getPaymentCaptureDataJson()
    {
        $captureData = [
            self::CAPTURE_CASE_ONLINE => [],
            self::CAPTURE_CASE_OFFLINE => [],
            self::CAPTURE_CASE_NOT_CAPTURE => []
        ];
        foreach ($this->getMethods() as $methodInstance) {
            $methodCode = $methodInstance->getCode();
            switch (true) {
                case $methodInstance->isOffline():
                    $captureData[self::CAPTURE_CASE_OFFLINE][] = $methodCode;
                    $captureData[self::CAPTURE_CASE_NOT_CAPTURE][] = $methodCode;
                    break;
                case $methodInstance->isGateway() && ($methodInstance instanceof VaultPaymentInterface):
                    $captureData[self::CAPTURE_CASE_ONLINE][] = $methodCode;
                    $captureData[self::CAPTURE_CASE_NOT_CAPTURE][] = $methodCode;
                    break;
                case $methodInstance->isGateway():
                    $captureData[self::CAPTURE_CASE_ONLINE][] = $methodCode;
                    break;
                default:
                    break;
            }
        }

        return $this->_jsonHelper->jsonEncode($captureData);
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
     * Check do we have applicable payment method, which can capture partial payment
     *
     * @return bool
     */
    public function canPartialPay()
    {
        return $this->helperData->isOrderPartiallyPaid($this->getOrder());
    }

    /**
     * Change template if default one is used: default template is suitable for order create page only
     *
     * @param string $paymentFormBlockName
     * @return string
     */
    public function getPaymentFormHtml($paymentFormBlockName)
    {
        /** @var \Magento\Payment\Block\Adminhtml\Transparent\Form $paymentFormBlock */
        $paymentFormBlock = $this->getChildBlock($paymentFormBlockName);
        if ($paymentFormBlock->getTemplate() == 'Magento_Payment::transparent/form.phtml') {
            $paymentFormBlock->setTemplate('Infocus_PartialPayments::transparent/form.phtml');
        }

        return parent::getChildHtml($paymentFormBlockName);
    }

    /**
     * Check if gateway is associated with invoice order
     *
     * @return bool
     */
    public function isGatewayUsed($method)
    {
        return $method->isGateway();
    }
}
