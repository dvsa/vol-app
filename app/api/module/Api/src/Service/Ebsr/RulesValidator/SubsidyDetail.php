<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\RulesValidator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\StringLength;

class SubsidyDetail extends AbstractValidator
{
    public const RULES_ERROR = 'subsidy-detail-too-long';

    protected $messageTemplates = [
        self::RULES_ERROR => 'Combined SubsidisingAuthority names must not exceed 1000 characters',
    ];

    #[\Override]
    public function isValid($value)
    {
        $validator = new StringLength(['max' => 1000, 'encoding' => 'UTF-8']);
        if (!$validator->isValid($value['subsidyDetail'] ?? '')) {
            $this->error(self::RULES_ERROR);
            return false;
        }

        return true;
    }
}
