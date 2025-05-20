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

namespace Infocus\PartialPayments\Block\Sales\Order\Items;

use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order\Invoice\ItemFactory;
use Magento\Bundle\Block\Sales\Order\Items\Renderer as ItemRenderer;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Sales\Model\Order\Invoice\Item as MagentoInvoiceItem;

/**
 * Class Renderer
 * @package Infocus\PartialPayments\Block\Sales\Order\Items
 */
class Renderer extends ItemRenderer
{
    /**
     * @var HelperData
     */
    protected $dataHelper;

    /**
     * @var ItemFactory
     */
    protected $invoiceItemFactory;

    /**
     * Renderer constructor.
     *
     * @param Context $context
     * @param StringUtils $string
     * @param OptionFactory $productOptionFactory
     * @param HelperData $dataHelper
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        Context $context,
        StringUtils $string,
        OptionFactory $productOptionFactory,
        HelperData $dataHelper,
        array $data = [],
        Json $serializer = null
    ) {
        parent::__construct($context, $string, $productOptionFactory, $data, $serializer);
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param object $item
     * @return mixed
     */
    public function getParentItem($item)
    {
        return $item->getOrderItem() ? $item->getOrderItem()->getParentItem() : $item->getParentItem();
    }

    /**
     * @param object $item
     * @return MagentoInvoiceItem|null
     */
    public function getInvoiceItem($item)
    {
        if ($item instanceof MagentoInvoiceItem) {
            return $item;
        }

        /** @var MagentoInvoiceItem $invoice */
        foreach ($this->getInvoice()->getItems() as $invoiceItem) {
            if ($invoiceItem->getOrderItemId() === $item->getId()) {
                return $invoiceItem;
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getItemPrice($item)
    {
        $invoiceItem = $this->getInvoiceItem($item);
        if (!$invoiceItem) {
            $itemForPrice = clone $item;
            $itemForPrice->setRowTotal(0);
            $itemForPrice->setRowTotalInclTax(0);
            return parent::getItemPrice($itemForPrice);
        }
        $invoiceItem->setOrder($this->getOrder());
        return parent::getItemPrice($invoiceItem);
    }

    /**
     * @param object $item
     * @return integer
     */
    public function getItemQty($item)
    {
        $invoiceItem = $this->getInvoiceItem($item);
        if (!$invoiceItem) {
            return 0;
        }
        $qty = (float)$invoiceItem->getQty();
        return $this->dataHelper->isRoundPayedItemQuantityInEmail() ? floor($qty) : $qty;
    }

    /**
     * @param mixed $item
     * @return bool
     */
    public function canShowPriceInfo($item)
    {
        if ($this->getParentItem($item) && $this->isChildCalculated() ||
            !$this->getParentItem($item) && !$this->isChildCalculated()
        ) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($item, $checkPartiallyPaid = false)
    {
        if (!$checkPartiallyPaid
            || !($item instanceof MagentoInvoiceItem)
            || !$item->getOrder()->getIsPartiallyPaid()
        ) {
            return parent::getChildren($item);
        }
        $items = $item->getOrder()->getAllItems();
        if ($items) {
            /** @var MagentoInvoiceItem $orderItem */
            foreach ($items as $orderItem) {
                $parentItem = $orderItem->getParentItem();
                if ($parentItem) {
                    $itemsArray[$parentItem->getId()][$orderItem->getId()] = $orderItem;
                } else {
                    $itemsArray[$orderItem->getId()][$orderItem->getId()] = $orderItem;
                }
            }
        }
        if (isset($itemsArray[$item->getOrderItem()->getId()])) {
            return $itemsArray[$item->getOrderItem()->getId()];
        }
        return null;
    }
}
