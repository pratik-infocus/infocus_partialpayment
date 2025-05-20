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

namespace Infocus\PartialPayments\Plugin\Magento\Checkout\Block\Checkout;

use Infocus\PartialPayments\Helper\Data as HelperData;
use Infocus\PartialPayments\Plugin\Magento\Checkout\MethodsDisablerPlugin;
use Magento\Checkout\Block\Checkout\LayoutProcessor as MagentoCheckoutLayoutProcessor;

/**
 * Class LayoutProcessor
 * @package Infocus\PartialPayments\Plugin\Magento\Checkout\Block\Checkout
 */
class LayoutProcessor extends MethodsDisablerPlugin
{
    /**
     * @param MagentoCheckoutLayoutProcessor $layoutProcessor
     * @param [] $jsLayout
     * @return array
     */
    public function afterProcess(MagentoCheckoutLayoutProcessor $layoutProcessor, $jsLayout)
    {
        $configuration = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['renders']['children'] ?? [];
        if (empty($configuration)) {
            return $jsLayout;
        }
        $enabled = $this->isVisibleOnFrontend();
        if ($enabled) {
            return $jsLayout;
        }
        foreach ($configuration as $paymentGroup => $groupConfig) {
            if (isset($configuration[$paymentGroup]['methods'][HelperData::PARTIAL_PAYMENT_METHOD])) {
                unset($configuration[$paymentGroup]['methods'][HelperData::PARTIAL_PAYMENT_METHOD]);
                break;
            }
        }
        return $jsLayout;
    }
}
