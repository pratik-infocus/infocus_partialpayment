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
    'underscore',
    'uiComponent',
    'ko',
    'Infocus_PartialPayments/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'partialLoader'
], function ($, _, Component, ko, paymentService, methodConverter, partialLoader) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Infocus_PartialPayments/payment'
        },
        isVisible: ko.observable(false),
        initialize: function () {
            partialLoader.loaderStart();
            paymentService.setPaymentMethods(methodConverter(window.checkoutConfig.paymentMethods));
            this._super();
        },

        isPaymentMethodsAvailable: ko.computed(function () {
            return paymentService.getAvailablePaymentMethods().length > 0;
        }),

        getFormKey: function () {
            return window.checkoutConfig.formKey;
        }
    });
});
