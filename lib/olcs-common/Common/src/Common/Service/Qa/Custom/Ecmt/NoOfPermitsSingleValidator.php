<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Laminas\Validator\AbstractValidator;

class NoOfPermitsSingleValidator extends AbstractValidator
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
     */
    #[\Override]
    public function isValid($value)
    {
        $this->permitsRemaining = $this->getOption('permitsRemaining');
        $maxPermitted = $this->getOption('maxPermitted');

        $thresholdValue = $maxPermitted;
        $thresholdMessage = self::MAX_PERMITTED_THRESHOLD;

        if ($this->permitsRemaining < $thresholdValue) {
            $thresholdValue = $this->permitsRemaining;
            $thresholdMessage = self::PERMITS_REMAINING_THRESHOLD;

            $this->abstractOptions['messageTemplates'][self::PERMITS_REMAINING_THRESHOLD] = sprintf(
                self::PERMITS_REMAINING_THRESHOLD_TEMPLATE,
                $this->getOption('emissionsCategory')
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
