<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData;

use Laminas\Validator\AbstractValidator;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;

/**
 * Class VariationNumber
 * @package Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData
 */
class VariationNumber extends AbstractValidator
{
    public const CANCELLATION_VARIATION_NUMBER_ERROR = 'cancellation-variation-number-error';
    public const VARIATION_VARIATION_NUMBER_ERROR = 'variation-variation-number-error';
    public const NEW_VARIATION_NUMBER_ERROR = 'new-variation-number-error';
    public const EXPECTED_VARIATION_MSG = 'The expected variation number was %d';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::CANCELLATION_VARIATION_NUMBER_ERROR =>
            'For cancellations, the variation number must be equal to previous variation number. %value%',
        self::VARIATION_VARIATION_NUMBER_ERROR =>
            'For variations, the variation number should be one greater than the previous variation number. %value%',
        self::NEW_VARIATION_NUMBER_ERROR => 'For new applications, the variation number must be zero'
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

        if ($value['txcAppType'] === BusRegEntity::TXC_APP_CANCEL) {
            $expectedVariationNumber = $busReg->getVariationNo();
            if ($value['variationNo'] != $expectedVariationNumber) {
                $this->error(
                    self::CANCELLATION_VARIATION_NUMBER_ERROR,
                    sprintf(self::EXPECTED_VARIATION_MSG, $expectedVariationNumber)
                );
                return false;
            }
        } elseif ($value['txcAppType'] === BusRegEntity::TXC_APP_NEW) {
            if ($value['variationNo'] != 0) {
                $this->error(self::NEW_VARIATION_NUMBER_ERROR);
                return false;
            }
        } else {
            /**
             * When we fetch the previous bus reg record, we exclude certain statuses, and get the first record with a
             * valid status. Here we are getting the latest variation without excluding these statuses
             * (expired, withdrawn, refused)
             */
            $latestVariation = $busReg->getLicence()->getLatestBusVariation($busReg->getRegNo(), []);
            $expectedVariationNumber = $latestVariation->getVariationNo() + 1;

            if ($value['variationNo'] != $expectedVariationNumber) {
                $this->error(
                    self::VARIATION_VARIATION_NUMBER_ERROR,
                    sprintf(self::EXPECTED_VARIATION_MSG, $expectedVariationNumber)
                );
                return false;
            }
        }

        return true;
    }
}
