<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;

/**
 * Class BusRegNotFound
 * @package Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData
 */
class BusRegNotFound extends AbstractValidator
{
    public const BUS_REG_NOT_FOUND_ERROR = 'bus-reg-not-found-error';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::BUS_REG_NOT_FOUND_ERROR => 'The bus registration number you provided wasn\'t found: %value%'
    ];

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param array $value   input value
     * @param array $context context value
     *
     * @return bool
     */
    public function isValid($value, $context = [])
    {
        //this check is only done for records which are not new applications
        if ($value['txcAppType'] !== BusRegEntity::TXC_APP_NEW && !$context['busReg'] instanceof BusRegEntity) {
            $this->error(self::BUS_REG_NOT_FOUND_ERROR, $value['existingRegNo']);
            return false;
        }

        return true;
    }
}
