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

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Url;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Class AbstractAccount
 * @package Infocus\PartialPayments\Controller\Order
 */
abstract class AbstractAccount extends Action
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Url
     */
    protected $customerUrl;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * AbstractAccount constructor.
     * @param Context $context
     * @param Session $session
     * @param Url $url
     * @param HelperData $helperData
     */
    public function __construct(
        Context $context,
        Session $session,
        Url $url,
        HelperData $helperData
    ) {
        parent::__construct($context);
        $this->customerSession = $session;
        $this->customerUrl = $url;
        $this->helperData =$helperData;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();
        if (!$this->customerSession->authenticate($loginUrl) || !$this->isEnabled()) {
            $this->getActionFlag()->set('', self::FLAG_NO_DISPATCH, true);
            $this->_forward('noroute');
        }
        return parent::dispatch($request);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        $customerId = $this->customerSession->getCustomerId();

        if (!$customerId) {
            return false;
        }

        if (!$this->helperData->isEnabled()
            && $this->helperData->getPartiallyPaidOrders($customerId)->getSize() == 0) {
            return false;
        }
        return true;
    }
}
