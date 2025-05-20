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

namespace Infocus\PartialPayments\Model\Order\Email;

use Infocus\PartialPayments\Model\Order\Email\Container\OutstandingIdentity;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\SenderBuilderFactory;
use Magento\Sales\Model\Order\Email\Container\Template;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class OutstandingSender
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OutstandingSender extends Sender
{
    /**
     * Additional vars to pass into template
     * @var array
     */
    protected $additionalVars = [];

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * OrderSender constructor.
     * @param Template $templateContainer
     * @param OutstandingIdentity $identityContainer
     * @param SenderBuilderFactory $senderBuilderFactory
     * @param LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Template $templateContainer,
        OutstandingIdentity $identityContainer,
        SenderBuilderFactory $senderBuilderFactory,
        LoggerInterface $logger,
        Renderer $addressRenderer,
        PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer);
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Sends outstanding payment invoice email to the customer.
     *
     * @param Order $order
     * @return bool
     */
    public function send(Order $order)
    {
        return $this->checkAndSend($order);
    }

    /**
     * Prepare email template with variables
     *
     * @param Order $order
     * @return void
     */
    protected function prepareTemplate(Order $order)
    {
        $transport = [
            'order' => $order,
            'billing' => $order->getBillingAddress(),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'formattedTotalPaid' => $this->priceCurrency->format($order->getTotalPaid(), false),
            'formattedTodalDue' => $this->priceCurrency->format($order->getTotalDue(), false)
        ];
        //set additional vars
        $transport = array_merge($transport, $this->getAdditionalVars());
        if (isset($transport['next_installment_amount'])) {
            $transport['formattedNextAmount'] = $this->priceCurrency->format(
                $transport['next_installment_amount'],
                false
            );
        }
        $transport = new \Magento\Framework\DataObject($transport);
        $this->templateContainer->setTemplateVars($transport->getData());
        parent::prepareTemplate($order);
    }

    /**
     * @param array $additionalVars
     * @return $this
     */
    public function setAdditionalVars($additionalVars)
    {
        $this->additionalVars = $additionalVars;
        return $this;
    }

    /**
     * @return array
     */
    public function getAdditionalVars()
    {
        return $this->additionalVars;
    }
}
