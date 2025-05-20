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

namespace Infocus\PartialPayments\Block\Adminhtml\Order\View;

use Magento\Sales\Model\Order;
use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Backend\Block\Template;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;

/**
 * Class EmailPopup
 * @package Infocus\PartialPayments\Block\Adminhtml\Order\View
 */
class EmailPopup extends Template
{
    const ORDER_REGISTRY_KEY = 'sales_order';

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * EmailPopup constructor
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry(self::ORDER_REGISTRY_KEY);
    }

    /**
     * @return bool
     */
    public function isPopupEnabled()
    {
        return $this->_scopeConfig->isSetFlag(HelperData::ENABLE_CONFIG_PATH) && $this->getOrder()->getData(HelperData::IS_ORDER_PARTIALLY_PAID_FLAG);
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {
        return $this->getUrl(
            'infocus_partialpayments/outstandingEmail/send',
            ['order_id' => $this->getOrder()->getId()]
        );
    }
}
