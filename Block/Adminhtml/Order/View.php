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

namespace Infocus\PartialPayments\Block\Adminhtml\Order;

use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Sales\Api\Data\OrderInterface as MagentoOrderInterface;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Class View
 * @package Infocus\PartialPayments\Block\Adminhtml\Order
 */
class View extends Widget
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return MagentoOrderInterface
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('sales_order');
    }

    /**
     * @return bool
     */
    public function isOrderPartiallyPaid()
    {
        return (bool)$this->getOrder()->getData(HelperData::IS_ORDER_PARTIALLY_PAID_FLAG);
    }
}
