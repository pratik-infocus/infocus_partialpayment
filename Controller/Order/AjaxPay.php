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

namespace Infocus\PartialPayments\Controller\Order;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class AjaxPay
 * @package Infocus\PartialPayments\Controller\Order
 */
class AjaxPay extends AbstractPay
{
    /**
     * @param bool $success
     * @param string $error
     * @param array ...$arguments
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function returnResult($success, $error, ... $arguments)
    {
        $json = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $json->setData([
            'success' => $success,
            'error' => $error,
        ]);

        return $json;
    }
}
