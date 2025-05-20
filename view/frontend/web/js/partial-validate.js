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
    'jquery/ui',
    'jquery/validate',
    'mage/mage'
], function ($) {
    'use strict';
    $.widget('infocus.partialValidate', {
        options: {
            invoiceItem: '[data-role="invoice-item"]',
            invoiceItemTrigger: '[data-role="invoice-trigger"]',
            invoiceItemSum: '[data-role="invoice-sum"]',
            extraValidation: true,
            negativeValidation: false,
            validateClass: 'validate-min-pay-amount required-entry',
            errorMessage: 'Minimum payment amount for this order is ',
            paymoreMessage: 'Maximum payment amount for this order is ',
            thresholdMessage: 'Balance amount after this payment is lesser than minimum amount. Please pay full amount.',
            invoiceInput: '.selected-invoice',
            negativeValidateClass: 'validate-greater-than-zero',
            negativeInput: '-negative',
            errorClass: 'mage-error'
        },
        _create: function () {
            this._bind();
            this._initValidation();
        },
        _bind: function () {
            var self = this;
            $(self.options.invoiceItemTrigger).on('change', function () {
                var isSelected = $(this).prop('checked'),
                    input = $(this).closest(self.options.invoiceItem).find(self.options.invoiceItemSum);
                if (isSelected) {
                    input.attr('max', input.data('max'));
                    input.addClass(self.options.validateClass);
                    if (self.options.negativeValidation && !input.hasClass(self.options.negativeInput)) {
                        input.addClass(self.options.negativeValidateClass);
                    }
                } else {
                    input.attr('max', '');
                    input.removeClass(self.options.validateClass).removeClass(self.options.errorClass);
                    if (self.options.negativeValidation) {
                        input.removeClass(self.options.negativeValidateClass);
                    }
                }
                self.validateInvoices();
            });
            $(document).on('keyup', this.options.invoiceInput, $.proxy(this.validateInvoices, this));
        },
        _initValidation: function () {
            var self = this;
            if (this.options.extraValidation) {
                $.validator.addMethod('validate-min-pay-amount', function (value, element) {
                    var minValue = $(element).data('min');
                    var maxValue = $(element).data('max');
                    var minConfig = $(element).data('min-amount');
                    var numericPattern = /^[0-9]+(\.[0-9]{0,2})?$/;
                    if (!numericPattern.test(value)) {
                        var value = value.replace(/[^\d.]/g, '');
                        $(element).val(value);
                        if (value.includes('.')) {
                            var decimalPart = value.split('.')[1];
                            if (decimalPart && decimalPart.length > 2) {
                                var truncatedValue = value.slice(0, value.indexOf('.') + 3);
                                $(element).val(truncatedValue);
                            }
                        }
                    }
                    var getMinimumMessage = window.getMinimumMessage;
                    var getMinimumThresholdMessage = window.getMinimumThresholdMessage;
                    if(getMinimumMessage == "")
                    {
                        getMinimumMessage = self.options.errorMessage;
                    }
                    if(getMinimumThresholdMessage == "")
                    {
                        getMinimumThresholdMessage = self.options.thresholdMessage;
                    }
                    this.validateMessage = $.mage.__(getMinimumMessage + " "+ minValue + '.');
                    if(value > parseFloat(maxValue)){
                        $('.primary.checkout').prop('disabled', true);
                        this.validateMessage = $.mage.__(self.options.paymoreMessage + maxValue + '.');
                        return false;
                    }
                    if(((maxValue - value) < minConfig) && (maxValue != value) && (maxValue != minValue)){
                        this.validateMessage = $.mage.__(getMinimumThresholdMessage);
                        $('.primary.checkout').prop('disabled', true);
                        return false;
                    }
                    if((value == parseFloat(minValue)) && (value == parseFloat(maxValue)))
                    {
                        $('.primary.checkout').prop('disabled', false);
                        return true;
                    }
                    if(value >= parseFloat(minValue))
                    {
                        $('.primary.checkout').prop('disabled', false);
                        return true;
                    }
                    else
                    {
                        $('.primary.checkout').prop('disabled', true);
                        return false;
                    }
                }, function () {
                    return this.validateMessage;
                });
            }
            this.element.mage('validation', {});
        },
        validateInvoices: function () {
            return this.element.validation('isValid');
        }
    });
    return $.infocus.partialValidate;
});
