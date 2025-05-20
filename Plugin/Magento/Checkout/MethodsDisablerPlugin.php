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

namespace Infocus\PartialPayments\Plugin\Magento\Checkout;

use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class MethodsDisablerPlugin
 * @package Infocus\PartialPayments\Plugin\Magento\Checkout
 */
class MethodsDisablerPlugin
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * MethodsDisablerPlugin constructor
     *
     * @param HelperData $helperData
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(HelperData $helperData, StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    protected function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    protected function isVisibleOnFrontend()
    {
        return $this->helperData->isVisibleOnFrontEnd($this->getWebsiteId());
    }

    /**
     * @param array $paymentMethods
     * @return array
     * @throws NoSuchEntityException
     */
    protected function iteratePaymentMethods(array $paymentMethods = [])
    {
        if ($this->isVisibleOnFrontend()) {
            return $paymentMethods;
        }
        foreach ($paymentMethods as $key => $paymentMethod) {
            if ($paymentMethod->getCode() == HelperData::PARTIAL_PAYMENT_METHOD) {
                unset($paymentMethods[$key]);
                break;
            }
        }
        return $paymentMethods;
    }
}
