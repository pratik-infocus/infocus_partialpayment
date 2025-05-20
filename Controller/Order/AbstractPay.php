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
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Url;
use Magento\Customer\Model\Session;
use Infocus\PartialPayments\Helper\Data as HelperData;

/**
 * Class AbstractPay
 * @package Infocus\PartialPayments\Controller\Order
 */
abstract class AbstractPay extends AbstractAccount
{
    const CONTROLLER_PATH = 'infocus_partialpayments/order';

    const ACTION_PATH_PAY = 'pay';
    const ACTION_PATH_LIST = 'orderList';

    /**
     * @var RequestValidator
     */
    protected $requestValidator;

    /**
     * @var MyAccountProcessor
     */
    protected $myAccountProcessor;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * AbstractPay constructor.
     * @param Context $context
     * @param MyAccountProcessor $myAccountProcessor
     * @param RequestValidator $requestValidator
     * @param Session $session
     * @param Url $url
     * @param HelperData $helperData
     */
    public function __construct(
        Context $context,
        MyAccountProcessor $myAccountProcessor,
        RequestValidator $requestValidator,
        Session $session,
        Url $url,
        HelperData $helperData
    ) {
        parent::__construct($context, $session, $url, $helperData);
        $this->myAccountProcessor = $myAccountProcessor;
        $this->requestValidator = $requestValidator;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $this->prePayProcess();
        $error = $success = $successMessage = '';
        try {
            $requestParams = $this->getRequest()->getParams();
            if ($this->requestValidator->validate($requestParams)) {
                $this->myAccountProcessor->process($requestParams);
            }
            $successMessage = 'Order was paid successfully';
            $this->messageManager->addSuccessMessage(__($successMessage));
            $success = true;
            $error = false;
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
            $this->messageManager->addErrorMessage(__($error));
            $success = false;
        } finally {
            return $this->returnResult(...[$error, $success]);
        }
    }

    /**
     * @return $this
     */
    protected function prePayProcess()
    {
        return $this;
    }

    /**
     * @param boolean $success
     * @param string $error
     * @param array ...$arguments
     * @return mixed
     */
    abstract protected function returnResult($success, $error, ... $arguments);
}
