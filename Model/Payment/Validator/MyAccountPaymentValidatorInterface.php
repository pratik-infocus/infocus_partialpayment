<?php

namespace Infocus\PartialPayments\Model\Payment\Validator;

/**
 * Interface MyAccountPaymentValidatorInterface
 * @package Infocus\PartialPayments\Model\Payment\Validator
 */
interface MyAccountPaymentValidatorInterface
{
    /**
     * @param array $request
     * @return bool
     */
    public function validate(array $request = []);

    /**
     * @return string
     */
    public function getErrorMessage();
}
