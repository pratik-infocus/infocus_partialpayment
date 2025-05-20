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
 * @author Infocus Solutions]
 * @copyright Copyright (c) 2024 Infocus (https://infocus-solution.com)
 * @package Partial Payment module for Magento 2
 */

namespace Infocus\PartialPayments\Model\Checkout\Provider;

use Infocus\PartialPayments\Helper\Data;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Form\FormKey;
use Magento\Payment\Model\MethodList;
use Magento\Payment\Model\Method\InstanceFactory;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class DefaultConfigProvider
 * @package Infocus\PartialPayments\Model\Checkout\Provider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class DefaultConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * @var AddressConfig
     */
    protected $addressConfig;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var PaymentMethodListInterface
     */
    protected $paymentMethodList;

    /**
     * @var CollectionFactoryInterface
     */
    protected $orderCollectionFactory;

    /**
     * @var OrderConfig
     */
    protected $orderConfig;

    /**
     * DefaultConfigProvider constructor.
     *
     * @param CustomerRepository $customerRepository
     * @param CustomerSession $customerSession
     * @param HttpContext $httpContext
     * @param CollectionFactoryInterface $collectionFactory
     * @param Mapper $addressMapper
     * @param AddressConfig $addressConfig
     * @param FormKey $formKey
     * @param PaymentMethodListInterface $paymentMethodList
     * @param OrderConfig $config
     */
    public function __construct(
        CustomerRepository $customerRepository,
        CustomerSession $customerSession,
        HttpContext $httpContext,
        CollectionFactoryInterface $collectionFactory,
        Mapper $addressMapper,
        AddressConfig $addressConfig,
        FormKey $formKey,
        PaymentMethodListInterface $paymentMethodList,
        OrderConfig $config
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->addressMapper = $addressMapper;
        $this->addressConfig = $addressConfig;
        $this->formKey = $formKey;
        $this->paymentMethodList = $paymentMethodList;
        $this->orderCollectionFactory = $collectionFactory;
        $this->orderConfig = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $output['formKey'] = $this->formKey->getFormKey();
        $output['customerData'] = $this->getCustomerData();
        $output['paymentMethods'] = $this->getPaymentMethods();
        $baseCurrency = '';
        foreach ($this->getOrders() as $order) {
            /**
             * @var $order Order
             */
            $baseCurrency = $order->getBaseCurrencyCode();
            $output['orderCurrencyCodes'][] = $order->getOrderCurrencyCode();
        }
        $output['baseCurrencyCode'] = $baseCurrency;
        return $output;
    }

    /**
     * @return Collection
     */
    protected function getOrders()
    {
        $customerId = $this->customerSession->getCustomerId();
        $orders = $this->orderCollectionFactory->create($customerId)->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'status',
            ['in' => $this->orderConfig->getVisibleOnFrontStatuses()]
        )->setOrder(
            'created_at',
            'desc'
        );
        $orders->addAttributeToFilter(Data::IS_ORDER_PARTIALLY_PAID_FLAG, 1);
        return $orders;
    }

    /**
     * Retrieve customer data
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getCustomerData()
    {
        $customerData = [];
        if ($this->isCustomerLoggedIn()) {
            $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            $customerData = $customer->__toArray();
            foreach ($customer->getAddresses() as $key => $address) {
                $customerData['addresses'][$key]['inline'] = $this->getCustomerAddressInline($address);
            }
        }
        return $customerData;
    }

    /**
     * Set additional customer address data
     *
     * @param AddressInterface $address
     * @return string
     */
    private function getCustomerAddressInline($address)
    {
        $builtOutputAddressData = $this->addressMapper->toFlatArray($address);
        return $this->addressConfig
            ->getFormatByCode(AddressConfig::DEFAULT_ADDRESS_FORMAT)
            ->getRenderer()
            ->renderArray($builtOutputAddressData);
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     * @codeCoverageIgnore
     */
    private function isCustomerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * Returns array of payment methods
     *
     * @return array
     */
    private function getPaymentMethods()
    {
        $list = $this->paymentMethodList->getActiveList($this->customerSession->getCustomer()->getStoreId());
        $paymentMethods = [];
        foreach ($list as $paymentMethod) {
            $paymentMethods[] = [
                'code' => $paymentMethod->getCode(),
                'title' => $paymentMethod->getTitle(),
            ];
        }
        return $paymentMethods;
    }
}
