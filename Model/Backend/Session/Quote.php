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

namespace Infocus\PartialPayments\Model\Backend\Session;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Backend\Model\Session\Quote as SesssionQuote;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\ValidatorInterface;
use Magento\Framework\Session\StorageInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\App\State;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\QuoteFactory;
use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Framework\Registry;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Quote
 * @package Infocus\PartialPayments\Model\Backend\Session
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Quote extends SesssionQuote
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Order
     */
    protected $_partiallyPaidOrder;

    /**
     * Quote constructor
     * @param Http $request
     * @param SidResolverInterface $sidResolver
     * @param ConfigInterface $sessionConfig
     * @param SaveHandlerInterface $saveHandler
     * @param ValidatorInterface $validator
     * @param StorageInterface $storage
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param State $appState
     * @param CustomerRepositoryInterface $customerRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param OrderFactory $orderFactory
     * @param StoreManagerInterface $storeManager
     * @param GroupManagementInterface $groupManagement
     * @param QuoteFactory $quoteFactory
     * @param HelperData $helperData
     * @param Registry $registry
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Http $request,
        SidResolverInterface $sidResolver,
        ConfigInterface $sessionConfig,
        SaveHandlerInterface $saveHandler,
        ValidatorInterface $validator,
        StorageInterface $storage,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        State $appState,
        CustomerRepositoryInterface $customerRepository,
        CartRepositoryInterface $quoteRepository,
        OrderFactory $orderFactory,
        StoreManagerInterface $storeManager,
        GroupManagementInterface $groupManagement,
        QuoteFactory $quoteFactory,
        HelperData $helperData,
        Registry $registry
    ) {
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState,
            $customerRepository,
            $quoteRepository,
            $orderFactory,
            $storeManager,
            $groupManagement,
            $quoteFactory
        );
        $this->_coreRegistry = $registry;
        $this->helperData = $helperData;
    }

    /**
     * @return int|mixed|null
     */
    public function getCustomerId()
    {
        if ($this->getPartiallyPaidOrder()
            && $this->helperData->isEnabled($this->getPartiallyPaidOrder()->getStore()->getWebsiteId())
            && $this->helperData->isOrderPartiallyPaid($this->getPartiallyPaidOrder())) {
            return $this->getPartiallyPaidOrder()->getCustomerId();
        }
        return $this->getData('customer_id');
    }

    // /**
    //  * @return CartInterface
    //  * @throws NoSuchEntityException
    //  */
    // public function getPartiallyPaidQuote()
    // {
    //     return $this->quoteRepository->get($this->getPartiallyPaidOrder()->getQuoteId());
    // }

    /**
     * @return Order
     */
    public function getPartiallyPaidOrder()
    {
        if (!$this->_partiallyPaidOrder) {
            if ($currentInvoice = $this->_coreRegistry->registry('current_invoice')) {
                $this->_partiallyPaidOrder = $currentInvoice->getOrder();
            }
        }
        return $this->_partiallyPaidOrder;
    }
}
