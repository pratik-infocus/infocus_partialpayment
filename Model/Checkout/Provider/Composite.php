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

namespace Infocus\PartialPayments\Model\Checkout\Provider;

use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class Composite
 * @package Infocus\PartialPayments\Model\Checkout\Provider
 */
class Composite extends CompositeConfigProvider
{
    /**
     * Composite constructor.
     *
     * @param array|ConfigProviderInterface[] $configProviders
     * @param array $providersToRemove
     */
    public function __construct(
        $configProviders,
        array $providersToRemove = []
    ) {
        $configProviders = $this->filterProviders($configProviders, $providersToRemove);
        parent::__construct($configProviders);
    }

    /**
     * @param array $providers
     * @param array $providersToRemove
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function filterProviders(array $providers = [], array $providersToRemove = [])
    {
        if (!empty($providersToRemove)) {
            foreach ($providersToRemove as $key => $className) {
                if (isset($providers[$key])) {
                    unset($providers[$key]);
                }
            }
            $providersToRemove = array_flip($providersToRemove);
            foreach ($providers as $key => $providerInstance) {
                if (isset($providersToRemove[get_class($providerInstance)])) {
                    $providers[$key] = null;
                }
            }
        }
        return array_filter($providers);
    }
}
