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

/* global AdminInvoicePayment, invoicePayment */
define([
    'jquery',
    'adminInvoicePayment',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('infocus.adminInvoicePayment', {
        options: {
            captureData: null,
            loadBlockUrl: null,
            orderId: null,
            methodsCount: 0,
            selectedMethodCode: null
        },
        _create: function () {
            window.invoicePayment = new AdminInvoicePayment({'capture_data': this.options.captureData});
            invoicePayment.setLoadBaseUrl(this.options.loadBlockUrl);
            invoicePayment.setOrderId(this.options.orderId);

            if (this.options.methodsCount === 1) {
                invoicePayment.switchPaymentMethod(this.options.selectedMethodCode);
            } else {
                invoicePayment.setPaymentMethod(this.options.selectedMethodCode);
            }
        }
    });

    return $.infocus.adminInvoicePayment;
});
