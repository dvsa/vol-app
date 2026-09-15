<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Laminas\Validator\AbstractValidator;

class NoOfPermitsEitherValidator extends AbstractValidator
{
    public const MAX_PERMITTED_THRESHOLD = 'maxPermittedThreshold';

    public const PERMITS_REMAINING_THRESHOLD = 'permitsRemainingThreshold';

    public const PERMITS_REMAINING_THRESHOLD_TEMPLATE = 'qanda.ecmt.number-of-permits.error.permits-remaining-exceeded.%s';

    protected $messageTemplates = [
        self::MAX_PERMITTED_THRESHOLD => 'qanda.ecmt.number-of-permits.error.total-max-exceeded',
        self::PERMITS_REMAINING_THRESHOLD => 'updatedAtRuntime'
    ];

    protected $messageVariables = [
        'permitsRemaining' => 'permitsRemaining'
    ];

    /** @var int */
    protected $permitsRemaining;

    /**
     * {@inheritdoc}
     *
     * @psalm-param array{emissionsCategory: mixed}|null $context
     */
    #[\Override]
    public function isValid($value, $context = null)
    {
        $emissionsCategoryPermitsRemaining = $this->getOption('emissionsCategoryPermitsRemaining');
        $maxPermitted = $this->getOption('maxPermitted');

        $selectedEmissionsCategory = $context['emissionsCategory'];

        if (!isset($emissionsCategoryPermitsRemaining[$selectedEmissionsCategory])) {
            // no radio button selected - return true as this will be caught by the radio button validator
            return true;
        }

        $this->permitsRemaining = $emissionsCategoryPermitsRemaining[$selectedEmissionsCategory];

        $thresholdValue = $maxPermitted;
        $thresholdMessage = self::MAX_PERMITTED_THRESHOLD;
        if ($this->permitsRemaining < $thresholdValue) {
            $thresholdValue = $this->permitsRemaining;
            $thresholdMessage = self::PERMITS_REMAINING_THRESHOLD;

            $this->abstractOptions['messageTemplates'][self::PERMITS_REMAINING_THRESHOLD] = sprintf(
                self::PERMITS_REMAINING_THRESHOLD_TEMPLATE,
                $selectedEmissionsCategory
            );
        }

        $permitsRequired = (int) $value;

        if ($permitsRequired > $thresholdValue) {
            $this->error($thresholdMessage);
            return false;
        }

        return true;
    }
}
