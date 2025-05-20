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

define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('infocus.zeroInvoicePayment', {
        options: {
            method: 'partial_payment',
            paymentList: ':radio[name="payment[method]"]',
            payAmountControl: '#order-pay_amount-control',
            payAmountInput: '#order-pay_amount-input',
            billingMethodForm: '#order-billing_method_form',
            billingMethodPayAmount: '#order-billing_method-pay_amount',
            captureCase: '#capture_case',
            resetCaptureCase: true
        },
        _create: function () {
            this._bind();
        },
        _bind: function () {
            this.element.on('change', $.proxy(function (e) {
                var $partialMethod = $(this.options.paymentList + '[value="' + this.options.method + '"]');
                if ($(e.currentTarget).val() === '1') {
                    if ($partialMethod.length && !$partialMethod.prop('disabled')) {
                        $(this.options.captureCase).val('offline').closest('.admin__field').hide();
                        $(this.options.captureCase).trigger('change');
                    }
                    $partialMethod.trigger('click');
                    // '1' - Set as enabled
                    $(this.options.payAmountControl).val('1').trigger('change');
                    $(this.options.payAmountInput).removeClass('validate-greater-than-zero').addClass('validate-zero-or-greater').val(0);
                    if ($partialMethod.length && !$partialMethod.prop('disabled')) {
                        $(this.options.billingMethodForm).hide();
                    }
                    $(this.options.billingMethodPayAmount).hide();
                } else {
                    $(this.options.billingMethodForm).show();
                    $(this.options.billingMethodPayAmount).hide();
                    $(this.options.payAmountInput).val("");
                    if (this.options.resetCaptureCase) {
                        $(this.options.captureCase).val('');
                        $(this.options.captureCase).trigger('change');
                    }
                    $(this.options.captureCase).closest('.admin__field').show();
                }
            }, this));
        }
    });

    return $.infocus.zeroInvoicePayment;
});
