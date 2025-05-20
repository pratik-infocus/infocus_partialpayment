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

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Result\Page;
use Infocus\PartialPayments\Helper\Data as HelperData;
use Infocus\PartialPayments\Controller\Order\AbstractAccount;

/**
 * Class OrderList
 * @package Infocus\PartialPayments\Controller\Order
 */
class OrderList extends AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * OrderList constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Session $session
     * @param Url $url
     * @param HelperData $helperData
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Session $session,
        Url $url,
        HelperData $helperData,
        StoreManagerInterface $storeManager
        )
    {
        parent::__construct($context, $session, $url, $helperData);
        $this->resultPageFactory = $pageFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__($this->helperData->getPartialPageLabel($this->storeManager->getStore()->getId())));
        return $resultPage;
    }
}
