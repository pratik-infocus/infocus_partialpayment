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

namespace Infocus\PartialPayments\Plugin\Magento\Checkout\Model;

use Infocus\PartialPayments\Helper\Data as HelperData;
use Infocus\PartialPayments\Plugin\Magento\Checkout\MethodsDisablerPlugin;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Checkout\Model\DefaultConfigProvider as MagentoCheckoutLayoutProcessor;

/**
 * Class LayoutProcessor
 * @package Infocus\PartialPayments\Plugin\Magento\Checkout\Model
 */
class DefaultConfigProvider extends MethodsDisablerPlugin
{
    /**
     * @param MagentoCheckoutLayoutProcessor $layoutProcessor
     * @param [] $jsLayout
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws NoSuchEntityException
     */
    public function afterGetConfig(MagentoCheckoutLayoutProcessor $layoutProcessor, $jsLayout)
    {
        $enabled = $this->isVisibleOnFrontend();
        if ($enabled) {
            return $jsLayout;
        }
        $methods = $jsLayout['paymentMethods'] ?? [];
        if (empty($methods)) {
            return $jsLayout;
        }
        foreach ($methods as $key => $method) {
            $methodCode = $method['code'] ?? null;
            if ($methodCode == HelperData::PARTIAL_PAYMENT_METHOD) {
                unset($methods[$key]);
                break;
            }
        }
        $methods = array_values($methods);
        $jsLayout['paymentMethods'] = $methods;
        return $jsLayout;
    }
}
