require(['jquery'], function ($) {
    'use strict';
    $(document).ready(function () {
        var submitButton = $('.submit-button.primary');
        var paymentMethods = $('input[name="payment[method]"]');
        if (paymentMethods.length > 0) {
            paymentMethods.on('change', function () {
                if (paymentMethods.is(':checked')) {
                    submitButton.prop('disabled', false);
                } else {
                    submitButton.prop('disabled', true);
                }
            });
        } else {
            submitButton.prop('disabled', false);
        }
    });
});
