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
    'ko',
    'jquery',
    'uiComponent',
    'Infocus_PartialPayments/js/model/payment-service',
    'Magento_Ui/js/model/messages',
    'uiLayout',
    'partialInvoiceData'
], function (
    ko,
    $,
    Component,
    paymentService,
    Messages,
    layout,
    partialInvoiceData
) {
    'use strict';

    return Component.extend({
        isPlaceOrderActionAllowed: ko.observable(false),

        /**
         * Initialize view.
         *
         * @return {exports}
         */
        initialize: function () {
            this._super().initChildren();
            this.bind();

            return this;
        },

        /**
         * Bind events
         */
        bind: function () {
            var self = this;
            $(document).on('PARTIAL_METHODS_DISABLED', function () {
                self.isPlaceOrderActionAllowed(false);
            });

            $(document).on('PARTIAL_METHODS_ENABLED', function () {
                self.isPlaceOrderActionAllowed(true);
            });
        },

        /**
         * Initialize child elements
         *
         * @returns {Component} Chainable.
         */
        initChildren: function () {
            this.messageContainer = new Messages();
            this.createMessagesComponent();

            return this;
        },

        /**
         * Create child message renderer component
         *
         * @returns {Component} Chainable.
         */
        createMessagesComponent: function () {
            var messagesComponent = {
                parent: this.name,
                name: this.name + '.messages',
                displayArea: 'messages',
                component: 'Magento_Ui/js/view/messages',
                config: {
                    messageContainer: this.messageContainer
                }
            };

            layout([messagesComponent]);

            return this;
        },

        /**
         * @return {Boolean}
         */
        selectPaymentMethod: function () {
            partialInvoiceData.paymentMethod(this.getData());
            partialInvoiceData.setSelectedPaymentMethod(this.item.method);
            return true;
        },

        isChecked: ko.computed(function () {
            return partialInvoiceData.getSelectedPaymentMethod() || null;
        }),

        isRadioButtonVisible: ko.computed(function () {
            return paymentService.getAvailablePaymentMethods().length !== 1;
        }),

        /**
         * Get payment method type.
         */
        getTitle: function () {
            return this.item.title;
        }
    });
});
