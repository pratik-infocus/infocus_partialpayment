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
    'ko',
    'Magento_Customer/js/customer-data'
], function ($, ko, storage) {
    'use strict';
    var cacheKey = 'partial-data',
        paymentMethod = ko.observable(null),

        /**
         * @param {Object} data
         */
        saveData = function (data) {
            storage.set(cacheKey, data);
        },

        /**
         * @return {*}
         */
        getData = function () {
            var data = storage.get(cacheKey)();

            if ($.isEmptyObject(data)) {
                data = {
                    'selectedPaymentMethod': null
                };
                saveData(data);
            }

            return data;
        };

    return {
        paymentMethod: paymentMethod,

        /**
         * @param {*} paymentMethodCode
         */
        setPaymentMethod: function (paymentMethodCode) {
            paymentMethod(paymentMethodCode);
        },

        /**
         * Setting the selected payment method pulled from persistence storage
         *
         * @param {Object} data
         */
        setSelectedPaymentMethod: function (data) {
            var obj = getData();

            obj.selectedPaymentMethod = data;
            saveData(obj);
        },

        /**
         * Pulling the payment method from persistence storage
         *
         * @return {*}
         */
        getSelectedPaymentMethod: function () {
            return getData().selectedPaymentMethod;
        }

    };
});
