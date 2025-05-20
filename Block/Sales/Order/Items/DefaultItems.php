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
use Magento\Framework\View\Element\Template;
use Magento\Sales\Block\Order\Email\Items\DefaultItems as MagentoDefaultItems;

/**
 * Class DefaultItems
 * @package Infocus\PartialPayments\Block\Sales\Order\Items
 */
class DefaultItems extends MagentoDefaultItems
{
    /**
     * @var HelperData
     */
    protected $dataHelper;

    /**
     * DefaultItems constructor.
     *
     * @param Template\Context $context
     * @param HelperData $dataHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        HelperData $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param object $item
     * @return integer
     */
    public function getItemQty($item)
    {
        return $this->dataHelper->isRoundPayedItemQuantityInEmail() ? floor($item->getQty()) : $item->getQty();
    }
}
