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
            'partialInvoiceData': 'Infocus_PartialPayments/js/action/partial-invoice-data',
            'placePartialInvoice': 'Infocus_PartialPayments/js/action/place-order',
            'partialLoader': 'Infocus_PartialPayments/js/action/partial-loader',
            'partialValidate': 'Infocus_PartialPayments/js/partial-validate',
            'partialInvoice': 'Infocus_PartialPayments/js/partial-invoice'
        }
    }
};
