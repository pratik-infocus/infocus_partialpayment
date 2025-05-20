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

namespace Infocus\PartialPayments\Block\Customer\Account;

use Magento\Checkout\Block\Onepage;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\Template\Context;
use Infocus\PartialPayments\Model\Checkout\Provider\CompositeFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Payment
 * @package Infocus\PartialPayments\Block\Customer\Account
 * @codingStandardsIgnoreFile
 */
class Payment extends Onepage
{
    /**
     * Payment constructor.
     * @param Context $context
     * @param FormKey $formKey
     * @param CompositeFactory $configProviderFactory
     * @param array $layoutProcessors
     * @param array $data
     * @param array $removeProviders
     * @param Json|null $serializer
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        CompositeFactory $configProviderFactory,
        array $layoutProcessors = [],
        array $data = [],
        array $removeProviders = [
            'Magento\Reward\Model\ConfigProvider',
            'Magento\GiftWrapping\Model\ConfigProvider',
            'Magento\GiftRegistry\Model\GiftRegistryConfigProvider',
            'Magento\GiftCardAccount\Model\GiftCardConfigProvider',
            'Magento\CustomerBalance\Model\ConfigProvider',
            'Magento\Tax\Model\TaxConfigProvider',
            'Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider'
        ],
        Json $serializer = null
    ) {
        $providersToRemove = $data['remove_providers'] ?? [];
        $providersToRemove = array_merge($providersToRemove, $removeProviders);
        $configProvider = $configProviderFactory->create(
            [
                'providersToRemove' => $providersToRemove,
            ]
        );
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data, $serializer);
    }
}
