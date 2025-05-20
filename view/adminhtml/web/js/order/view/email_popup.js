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
    'mage/translate',
    'jquery/ui',
    'mage/validation',
    'prototype'
], function (jQuery, t) {
    window.OutstandingEmailPopup = Class.create();
    OutstandingEmailPopup.prototype = {
        outstandingEmailWindow: '#outstanding_email_popup',
        openDialogButton: '[data-ui-id="page-actions-toolbar-send-outstanding-email"]',
        sendEmailButton: '#outstanding_email_send_button',
        nextAmountInput: '#next_installment_amount',
        outstandingEmailForm: '#outstanding_email_form',

        initialize: function () {
            var self = this,
                form = jQuery(this.outstandingEmailForm),
                maxAmount = jQuery(this.nextAmountInput).val()
            ;
            jQuery(this.outstandingEmailWindow).dialog({
                title: t('Send Remaining Payment Email'),
                autoOpen:   false,
                modal:      true,
                resizable:  false,
                dialogClass: 'outstanding-email-popup',
                minWidth:   500,
                width:      '75%',
                position: {
                    my: 'left+12.5% top',
                    at: 'center top',
                    of: 'body'
                },
                open: function () {
                    jQuery(this).closest('.ui-dialog').addClass('ui-dialog-active');
                    var topMargin = jQuery(this).closest('.ui-dialog').children('.ui-dialog-titlebar').outerHeight() + 30;
                    jQuery(this).closest('.ui-dialog').css('margin-top', topMargin).css('z-index', 9);
                },
                close: function () {
                    jQuery(this).closest('.ui-dialog').removeClass('ui-dialog-active');
                }
            });

            jQuery(this.openDialogButton).click(function () {
                jQuery(self.outstandingEmailWindow).dialog('open');
            });

            // Set validation
            form.validate({
                errorClass: 'mage-error',
                rules: {
                    next_installment_amount: {
                        max: maxAmount
                    }
                }
            })
        }
    };
});
