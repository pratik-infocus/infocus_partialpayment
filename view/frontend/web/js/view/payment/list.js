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
    'underscore',
    'mageUtils',
    'uiComponent',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/model/payment/renderer-list',
    'uiLayout',
    'mage/translate',
    'uiRegistry',
    'partialLoader'
], function (_, utils, Component, paymentMethods, rendererList, layout, $t, registry, partialLoader) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/payment-methods/list',
            visible: paymentMethods().length > 0,
            configDefaultGroup: {
                name: 'methodGroup',
                component: 'Magento_Checkout/js/model/payment/method-group'
            },
            paymentGroupsList: [],
            defaultGroupTitle: $t('Select a new payment method')
        },

        /**
         * Initialize view.
         *
         * @returns {Component} Chainable.
         */
        initialize: function () {
            this._super().initDefaulGroup().initChildren();
            paymentMethods.subscribe(
                function (changes) {
                    // remove renderer for "deleted" payment methods
                    _.each(changes, function (change) {
                        if (change.status === 'deleted') {
                            this.removeRenderer(change.value.method);
                        }
                    }, this);
                    // add renderer for "added" payment methods
                    _.each(changes, function (change) {
                        if (change.status === 'added') {
                            this.createRenderer(change.value);
                        }
                    }, this);
                }, this, 'arrayChange');
            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe(['paymentGroupsList']);

            return this;
        },

        /**
         * Creates default group
         *
         * @returns {Component} Chainable.
         */
        initDefaulGroup: function () {
            layout([
                this.configDefaultGroup
            ]);

            return this;
        },

        /**
         * Create renders for child payment methods.
         *
         * @returns {Component} Chainable.
         */
        initChildren: function () {
            var self = this;

            _.each(paymentMethods(), function (paymentMethodData) {
                self.createRenderer(paymentMethodData);
            });

            return this;
        },

        /**
         * @returns
         */
        createComponent: function (payment) {
            var rendererTemplate,
                rendererComponent,
                templateData;

            templateData = {
                parentName: this.name,
                name: payment.name
            };
            rendererTemplate = {
                parent: '${ $.$data.parentName }',
                name: '${ $.$data.name }',
                displayArea: payment.displayArea,
                component: payment.component
            };
            rendererComponent = utils.template(rendererTemplate, templateData);
            utils.extend(rendererComponent, {
                item: payment.item,
                config: payment.config
            });

            return rendererComponent;
        },

        /**
         * Create renderer.
         *
         * @param {Object} paymentMethodData
         */
        createRenderer: function (paymentMethodData) {
            var isRendererForMethod = false,
                currentGroup;

            registry.get(this.configDefaultGroup.name, function (defaultGroup) {
                _.each(rendererList(), function (renderer) {
                    if (renderer.hasOwnProperty('typeComparatorCallback') &&
                        typeof renderer.typeComparatorCallback === 'function'
                    ) {
                        isRendererForMethod = renderer.typeComparatorCallback(renderer.type, paymentMethodData.method);
                    } else {
                        isRendererForMethod = renderer.type === paymentMethodData.method;
                    }

                    if (isRendererForMethod) {
                        currentGroup = renderer.group ? renderer.group : defaultGroup;

                        this.collectPaymentGroups(currentGroup);

                        layout([
                            this.createComponent(
                                {
                                    config: renderer.config,
                                    component: renderer.component,
                                    name: renderer.type,
                                    method: paymentMethodData.method,
                                    item: paymentMethodData,
                                    displayArea: currentGroup.displayArea
                                }
                            )]);
                    }
                }.bind(this));
            }.bind(this));
        },

        /**
         * Collects unique groups of available payment methods
         *
         * @param {Object} group
         */
        collectPaymentGroups: function (group) {
            var groupsList = this.paymentGroupsList(),
                isGroupExists = _.some(groupsList, function (existsGroup) {
                    return existsGroup.alias === group.alias;
                });

            if (!isGroupExists) {
                groupsList.push(group);
                groupsList = _.sortBy(groupsList, function (existsGroup) {
                    return existsGroup.sortOrder;
                });
                this.paymentGroupsList(groupsList);
            }
        },

        /**
         * Returns payment group title
         *
         * @param {Object} group
         * @returns {String}
         */
        getGroupTitle: function (group) {
            var title = group().title;

            if (group().isDefault() && this.paymentGroupsList().length > 1) {
                title = this.defaultGroupTitle;
            }

            return title;
        },

        /**
         * Checks if at least one payment method available
         *
         * @returns {String}
         */
        isPaymentMethodsAvailable: function () {
            partialLoader.loaderStop();
            return _.some(this.paymentGroupsList(), function (group) {
                return this.getRegion(group.displayArea)().length;
            }, this);
        },

        /**
         * Remove view renderer.
         *
         * @param {String} paymentMethodCode
         */
        removeRenderer: function (paymentMethodCode) {
            var items;

            _.each(this.paymentGroupsList(), function (group) {
                items = this.getRegion(group.displayArea);

                _.find(items(), function (value) {
                    if (value.item.method.indexOf(paymentMethodCode) === 0) {
                        value.disposeSubscriptions();
                        value.destroy();
                    }
                });
            }, this);
        }
    });
});
