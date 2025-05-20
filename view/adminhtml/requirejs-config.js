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

/* eslint no-unused-vars: [1] */
var config = {
    map: {
        '*': {
            'adminInvoicePayment': 'Infocus_PartialPayments/invoice/create/scripts',
            'invoicePaymentWidget': 'Infocus_PartialPayments/invoice/create/widget',
            'zeroInvoicePayment': 'Infocus_PartialPayments/js/order/zero',
            'patialInvoiceState': 'Infocus_PartialPayments/js/invoice-state'
        }
    },
    config: {
        mixins: {
            'Magento_Payment/transparent': {
                'Infocus_PartialPayments/transparent-mixin': true
            }
        }
    }
};
