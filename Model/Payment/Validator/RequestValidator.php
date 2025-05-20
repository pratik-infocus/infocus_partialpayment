<?php

namespace Infocus\PartialPayments\Model\Payment\Validator;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class RequestValidator
 * @package Infocus\PartialPayments\Model\Payment\Validator
 */
class RequestValidator implements MyAccountPaymentValidatorInterface
{
    const PAYMENT_KEY = 'payment';

    /**
     * @var array
     */
    protected $additionalValidators;

    /**
     * @var array
     */
    protected $errorMessages = [];

    /**
     * RequestValidator constructor.
     *
     * @param array $additionalValidators
     */
    public function __construct(array $additionalValidators = [])
    {
        $this->additionalValidators = $additionalValidators;
    }

    /**
     * @param array $request
     * @return bool
     * @throws LocalizedException
     */
    public function validate(array $request = [])
    {
        foreach ($this->additionalValidators as $validator) {
            if ($validator instanceof MyAccountPaymentValidatorInterface) {
                $valid = $validator->validate($request);
                if (!$valid) {
                    throw  new LocalizedException(__($validator->getErrorMessage()));
                }
            }
        }

        $this->validateRequest($request);
        if (!empty($this->errorMessages)) {
            throw  new LocalizedException(__($this->getErrorMessage()));
        }

        return true;
    }

    /**
     * @param array $request
     * @return void
     */
    protected function validateRequest(array $request = [])
    {
        if (empty($request)) {
            $this->addErrorMessage('Request is empty');
        }

        if (empty($request[self::PAYMENT_KEY])) {
            $this->addErrorMessage('Payment method is not specified');
        }
    }

    /**
     * @param string $message
     * @return void
     */
    protected function addErrorMessage($message)
    {
        $this->errorMessages[] = $message;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return implode('; ', $this->errorMessages);
    }
}
