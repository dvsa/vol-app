<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData;

use Laminas\Validator\AbstractValidator;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;

/**
 * Class RegisteredBusRoute
 * @package Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData
 */
class RegisteredBusRoute extends AbstractValidator
{
    public const REGISTERED_BUS_ROUTE_ERROR = 'registered-bus-route-error';
    public const TYPE_CANCELLATION = 'cancellation';
    public const TYPE_VARIATION = 'variation';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::REGISTERED_BUS_ROUTE_ERROR => 'You can only create a %value% against a registered bus route'
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
        /** @var BusRegEntity $busReg */
        $busReg = $context['busReg'];

        //this check is not done for new applications
        if ($value['txcAppType'] !== BusRegEntity::TXC_APP_NEW && !$busReg->isRegistered()) {
            $type = $value['txcAppType'] === BusRegEntity::TXC_APP_CANCEL
                    ? self::TYPE_CANCELLATION
                    : self::TYPE_VARIATION;

            $this->error(self::REGISTERED_BUS_ROUTE_ERROR, $type);
            return false;
        }

        return true;
    }
}
