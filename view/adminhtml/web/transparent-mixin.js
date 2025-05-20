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

/* global FORM_KEY */
define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.transparent', widget, {
            options: {
                submitButtons: '[onclick="order.submit()"]'
            },

            _create: function () {
                var self = this;
                this._super();

                $(document).on('submitOrder.partial_payment', this.options.editFormSelector, function () {
                    if ($(self.options.editFormSelector).valid()) {
                        $(self.options.submitButtons).prop('disabled', true);
                    }
                });
            },

            /**
             * Handler for Place Order button to call gateway for credit card validation.
             * Save order and generate post data for gateway call.
             *
             * @private
             */
            _orderSave: function () {
                var postData = {
                        'form_key': FORM_KEY,
                        'cc_type': this.ccType()
                    },
                    sendEmailCheckbox = $('#send_email'),
                    partPaymentInput = $('#order-pay_amount-control');

                $(this.options.submitButtons).prop('disabled', true);

                if (partPaymentInput && partPaymentInput.val() === '1') {
                    postData['payment[pay_amount]'] = $('#order-pay_amount-input').val();
                }

                if (sendEmailCheckbox.length) {
                    postData['send_email'] = sendEmailCheckbox.is(':checked') ? 1 : 0;
                }

                this._getExtraPostData(postData);

                $.ajax({
                    url: this.options.orderSaveUrl,
                    type: 'post',
                    context: this,
                    data: postData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success && response[this.options.gateway]) {
                            this._postPaymentToGateway(response);
                        } else {
                            this._processErrors(response);
                        }
                    },
                    complete: function () {
                        $('body').trigger('processStop');
                    }
                });
            },

            _processErrors: function (response) {
                this._super(response);
                this.enableSubmit();
            },

            enableSubmit: function () {
                $(this.options.submitButtons).prop('disabled', false);
            },

            /**
             * Extend post data
             * @param data
             * @returns {*}
             * @private
             */
            _getExtraPostData: function (data) {
                try {
                    this._super(data);
                } catch (e) {
                }

                if (data) {
                    return data;
                }
            }
        });

        return $.mage.transparent;
    };
});
