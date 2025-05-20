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

namespace Infocus\PartialPayments\Plugin\Magento\Customer\Block\Account;

use Magento\Framework\View\LayoutInterface;
use Infocus\PartialPayments\Helper\Data as HelperData;
use Infocus\PartialPayments\Block\Customer\Account\Order\PartiallyPaidOrders;
use Magento\Customer\Block\Account\Navigation as AccountNavigation;

/**
 * Class Navigation
 * @package Infocus\PartialPayments\Plugin\Magento\Customer\Block\Account
 */
class Navigation
{
    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var PartiallyPaidOrders
     */
    protected $partiallyPaidOrders;

    /**
     * @var string
     */
    protected $partiallyPaidBlockName;

    /**
     * @var string
     */
    protected $parentBlockName;

    /**
     * Navigation constructor
     * @param LayoutInterface $layout
     * @param HelperData $helperData
     * @param PartiallyPaidOrders $partiallyPaidOrders
     * @param string $parentBlockName
     * @param string $partiallyPaidBlockName
     */
    public function __construct(
        LayoutInterface $layout,
        HelperData $helperData,
        PartiallyPaidOrders $partiallyPaidOrders,
        $parentBlockName,
        $partiallyPaidBlockName
    ) {
        $this->layout = $layout;
        $this->helperData = $helperData;
        $this->partiallyPaidOrders = $partiallyPaidOrders;
        $this->parentBlockName = $parentBlockName;
        $this->partiallyPaidBlockName = $partiallyPaidBlockName;
    }

    /**
     * @param AccountNavigation $subject
     * @return array
     */
    public function beforeGetLinks(AccountNavigation $subject)
    {
        $links = $this->layout->getChildBlocks($subject->getNameInLayout());
        if ($subject->getNameInLayout() == $this->parentBlockName
            && !empty($links[$this->partiallyPaidBlockName])
            && !$this->helperData->isEnabled()) {
            $orders = $this->partiallyPaidOrders->getOrders();
            if (!$orders || $orders->getSize() == 0) {
                $this->layout->unsetChild($subject->getNameInLayout(), $this->partiallyPaidBlockName);
            }
        }
        return [];
    }
}
