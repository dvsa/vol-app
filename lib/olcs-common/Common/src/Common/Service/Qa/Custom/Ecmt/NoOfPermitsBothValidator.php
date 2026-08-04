<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Laminas\Validator\AbstractValidator;

class NoOfPermitsBothValidator extends AbstractValidator
{
    public const PERMITS_REMAINING_THRESHOLD = 'permitsRemainingThreshold';

    public const PERMITS_REMAINING_THRESHOLD_TEMPLATE = 'qanda.ecmt.number-of-permits.error.permits-remaining-exceeded.%s';

    protected $messageTemplates = [
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
        $permitsRequired = (int) $value;

        if ($permitsRequired > $this->permitsRemaining) {
            $this->abstractOptions['messageTemplates'][self::PERMITS_REMAINING_THRESHOLD] = sprintf(
                self::PERMITS_REMAINING_THRESHOLD_TEMPLATE,
                $this->getOption('emissionsCategory')
            );

            $this->error(self::PERMITS_REMAINING_THRESHOLD);
            return false;
        }

        return true;
    }
}
