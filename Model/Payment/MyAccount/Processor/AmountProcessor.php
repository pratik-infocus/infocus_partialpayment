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

namespace Infocus\PartialPayments\Model\Payment\MyAccount\Processor;

use Infocus\PartialPayments\Helper\Data as HelperData;

/**
 * Class AmountProcessor
 * @package Infocus\PartialPayments\Model\Payment\MyAccount\Processor
 */
class AmountProcessor
{
    const ORDER_IDS = 'order_ids';
    const ORDER_ID_PLACEHOLDER = '{order_id}';
    const ORDER_AMOUNT_TEMPLATE = 'order_' . self::ORDER_ID_PLACEHOLDER . '_amount';
    const ORDER_ID = 'order_id';
    const AMOUNT_TO_PAY = 'amount';

    /**
     * @var HelperData
     */
    protected $helperData;

    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param array $array
     * @return array|bool
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function prepareAmounts(array $array = [])
    {
        if (!$orderIds = $this->helperData->getByKey($array, self::ORDER_IDS)) {
            return false;
        }
        $orderArray = [];
        foreach ($orderIds as $key => $value) {
            $orderArray[] = [
                self::ORDER_ID => $value,
                self::AMOUNT_TO_PAY => $this->helperData->getByKeyWithDefault($array, $this->getAmountKey($value), 0),
            ];
        }
        return $orderArray;
    }

    /**
     * @param string $value
     * @return mixed
     */
    protected function getAmountKey($value)
    {
        return str_replace(self::ORDER_ID_PLACEHOLDER, $value, self::ORDER_AMOUNT_TEMPLATE);
    }
}
