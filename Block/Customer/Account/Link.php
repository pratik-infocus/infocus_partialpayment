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

use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\DefaultPathInterface;

/**
 * Class Link
 * @package Infocus\PartialPayments\Block\Customer\Account
 */
class Link extends Current
{
    /**
     * @var ScopeInterface
     */
    protected $scopeConfig;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Link constructor
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param ScopeConfigInterface $scopeConfig
     * @param HelperData $helperData
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        ScopeConfigInterface $scopeConfig,
        HelperData $helperData,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
        parent::__construct($context, $defaultPath, $data);
    }
    public function getLabel()
    {
        return $this->helperData->getPartialPageLabel($this->storeManager->getStore()->getId());
    }
}
