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

use Infocus\PartialPayments\Model\Payment\MyAccount\MyAccountProcessor;
use Infocus\PartialPayments\Model\Payment\Validator\RequestValidator;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Infocus\PartialPayments\Helper\Data as HelperData;
use Infocus\PartialPayments\Block\Customer\Account\Order\PartiallyPaidOrders;

/**
 * Class Pay
 * @package Infocus\PartialPayments\Controller\Order
 */
class Pay extends AbstractPay
{
    const DEFAULT_CONTROLLER_PATH = 'sales/order';
    const DEFAULT_ACTION_PATH = 'history';

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var PartiallyPaidOrders
     */
    protected $partiallyPaidOrders;

    /**
     * Pay constructor
     * @param Context $context
     * @param MyAccountProcessor $myAccountProcessor
     * @param RequestValidator $requestValidator
     * @param Session $session
     * @param Url $url
     * @param HelperData $helperData
     * @param PartiallyPaidOrders $partiallyPaidOrders
     */
    public function __construct(
        Context $context,
        MyAccountProcessor $myAccountProcessor,
        RequestValidator $requestValidator,
        Session $session,
        Url $url,
        HelperData $helperData,
        PartiallyPaidOrders $partiallyPaidOrders
    ) {
        parent::__construct(
            $context,
            $myAccountProcessor,
            $requestValidator,
            $session,
            $url,
            $helperData
        );
        $this->helperData = $helperData;
        $this->partiallyPaidOrders = $partiallyPaidOrders;
    }

    /**
     * @param bool $success
     * @param string $error
     * @param array ...$arguments
     * @return Redirect
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function returnResult($success, $error, ... $arguments)
    {
        $redirect = $this->resultRedirectFactory->create();
        $orders = $this->partiallyPaidOrders->getOrders();
        $path = parent::CONTROLLER_PATH . '/' . parent::ACTION_PATH_LIST;
        if (!$this->helperData->isEnabled() && $orders->getSize() == 0) {
            $path = self::DEFAULT_CONTROLLER_PATH. '/'. self::DEFAULT_ACTION_PATH;
        }
        return $redirect->setPath($path);
    }
}
