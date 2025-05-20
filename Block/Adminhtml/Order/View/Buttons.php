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

namespace Infocus\PartialPayments\Block\Adminhtml\Order\View;

use Magento\Sales\Block\Adminhtml\Order\View;
use \Infocus\PartialPayments\Helper\Data as HelperData;
use Magento\Framework\View\Element\Template;

/**
 * Class Buttons
 * @package Infocus\PartialPayments\Block\Adminhtml\Order\View
 */
class Buttons extends Template
{
    /**
     * Add send email button on magento toolbar
     *
     * @return $this
     */
    public function addButtons()
    {
        /** @var View $parent */
        $parent = $this->getParentBlock();
        if (
            $this->_scopeConfig->isSetFlag(HelperData::ENABLE_CONFIG_PATH) && $parent->getOrder()->getData(HelperData::IS_ORDER_PARTIALLY_PAID_FLAG)
        ) {
            $this->addSendEmailButton();
        }
        return $this;
    }

    /**
     * Remove buttons on toolbar
     *
     * @return $this
     */
    public function removeButtons()
    {
        /** @var View $parent */
        $parent = $this->getParentBlock();
        if (
            $this->_scopeConfig->isSetFlag(HelperData::ENABLE_CONFIG_PATH) && $parent->getOrder()->getData(HelperData::IS_ORDER_PARTIALLY_PAID_FLAG)
        ) {
            $this->removeCreditMemoButton();
        }
        return $this;
    }

    /**
     * Add button
     *
     * @return Buttons
     */
    protected function addSendEmailButton()
    {
        /** @var View $parent */
        $parent = $this->getParentBlock();
        $parent->getToolbar()->addChild(
            'send_outstanding_email',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Request remaining payment')
            ]
        );

        return $this;
    }

    /**
     * Add button
     *
     * @return Buttons
     */
    protected function removeCreditMemoButton()
    {
        /** @var View $parent */
        $parent = $this->getParentBlock();
        $parent->getToolbar()->unsetChild('order_creditmemo_button');
        return $this;
    }
}
