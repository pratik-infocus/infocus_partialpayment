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
    'Magento_Catalog/js/price-utils',
    'jquery/ui'
], function ($, priceUtils) {
    'use strict';

    $.widget('infocus.partialInvoice', {
        options: {
            wrapper: '.partially-wrapper',
            invoiceItemTrigger: '[data-role="invoice-trigger"]',
            invoiceItem: '[data-role="invoice-item"]',
            invoiceItemSum: '[data-role="invoice-sum"]',
            selectedFieldClass: 'selected-invoice',
            totalsBlock: '[data-role="partially-total"]',
            visibleClass: '-visible',
            totalSum: '[data-role="partially-sum"]',
            priceFormat: {},
            hideNegativeSum: true
        },

        _create: function () {
            this._bind();
        },

        _bind: function () {
            var self = this;
            $(this.options.invoiceItemTrigger).on('change', function () {
                $(this).closest(self.options.wrapper).find(self.options.invoiceItemSum).removeClass(self.options.selectedFieldClass);
                $(this).closest(self.options.wrapper).find(self.options.invoiceItemSum).removeClass("mage-error");
                $(this).closest(self.options.wrapper).find(".mage-error").remove();
                $(this).closest(self.options.wrapper).find(self.options.invoiceItemSum).each(function() {
                    var maxValue = $(this).data('max');
                    $(this).val(maxValue).change();
                });
                var isSelected = $(this).is(':checked'),
                    input = $(this).closest(self.options.invoiceItem).find(self.options.invoiceItemSum);
                if (isSelected) {
                    input.addClass(self.options.selectedFieldClass);
                    input.focus();
                    $('.primary.checkout').prop('disabled', false);
                    $('input[name="payment[method]"]').prop('disabled', false);
                } else {
                    input.removeClass(self.options.selectedFieldClass);
                }
                self._checkSumInvoice();
            });
            this.element.on('change', '.' + this.options.selectedFieldClass, $.proxy(this._checkSumInvoice, this));
        },

        /**
         * Checks invoice sum
         * @private
         */
        _checkSumInvoice: function () {
            var payment = this.getInvoiceSum();
            if (payment > 0) {
                this._enablePayments();
            } else {
                this._disablePayments();
            }
            this._showTotalsBlock(payment);
            window.checkoutConfig.partialPaymentSum = payment;
        },

        /**
         * Retuns invoice sum
         * @return {number}
         * @private
         */
        getInvoiceSum: function () {
            var sum = 0;
            $('.' + this.options.selectedFieldClass).each(function () {
                sum += parseFloat($(this).val());
            });
            return sum;
        },

        /**
         * Enable payments
         * @private
         */
        _enablePayments: function () {
            $(document).trigger('PARTIAL_METHODS_ENABLED');
        },

        /**
         * Disable payments
         * @private
         */
        _disablePayments: function () {
            $(document).trigger('PARTIAL_METHODS_DISABLED');
        },

        /**
         * Shows and hides tottals block
         * @param {number} payment
         * @private
         */
        _showTotalsBlock: function (payment) {
            if (this.options.hideNegativeSum && payment > 0 || !this.options.hideNegativeSum && this._checkSelectedInvoices()) {
                $(this.options.wrapper).addClass(this.options.visibleClass);
                $(this.options.totalsBlock).addClass(this.options.visibleClass);
                $(this.options.totalSum).text(priceUtils.formatPrice(payment, this.options.priceFormat));
            } else {
                $(this.options.wrapper).removeClass(this.options.visibleClass);
                $(this.options.totalsBlock).removeClass(this.options.visibleClass);
                $(this.options.totalSum).text(priceUtils.formatPrice(0, this.options.priceFormat));
            }
        },

        /**
         * Check selected invoices
         * @return {boolean}
         * @private
         */
        _checkSelectedInvoices: function () {
            var checked = false;
            $(this.options.invoiceItemTrigger).each(function () {
                if ($(this).prop('checked')) {
                    checked = true;
                    return false;
                }
            });
            return checked;
        }
    });

    return $.infocus.partialInvoice;
});
