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
    'Magento_Checkout/js/action/redirect-on-success',
    'mage/mage'
], function ($, redirectOnSuccessAction) {
    'use strict';

    return {
        redirectAfterPlaceOrder: !!window.checkoutConfig.defaultSuccessPageUrl,
        /**
         * Place Invoice
         * @param {object} data
         */
        placePartialInvoice: function (data) {
            var form = $('[data-role="partially-form"]');
            $('[data-role="payment-method-nonce"]').val(data.nonce);
            $('[data-role="payment-method"]').val(data.method);
            $('[data-role="payment-token"]').val(data.is_active_token);

            if (data.payments_order_id) {
                form.append('<input type="hidden" name="payment[additional_information][payments_order_id]" value="' + data.payments_order_id + '" >');
            }

            if (data.paypal_order_id) {
                form.append('<input type="hidden" name="payment[additional_information][paypal_order_id]" value="' + data.paypal_order_id + '" >');
            }
            if (data.public_hash) {
                form.append('<input type="hidden" name="payment[additional_information][public_hash]" value="' + data.public_hash + '" >');
            }

            if (data.device_data) {
                form.append('<input type="hidden" name="payment[additional_information][deviceData][' + data.device_key + ']" value="' + data.device_data[data.device_key] + '" >');
            }

            if (data.additional_data) {
                for (var key in data.additional_data) {
                    form.append('<input type="hidden" name="payment[additional_information][' + key + ']" value="' + data.additional_data[key] + '" >');
                }
            }
            if (this.redirectAfterPlaceOrder) {
                $.ajax({
                    method: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serialize()
                }).always(function () {
                    redirectOnSuccessAction.execute();
                });
            } else {
                form.submit();
            }
        }
    };
});
