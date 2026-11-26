<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Validators;

use Laminas\Validator\Date;

final class DateOfBirth extends Date
{
    public const IN_FUTURE = 'inFuture';

    protected $messageTemplates = [
        self::INVALID => 'Invalid type given. String, integer, array or DateTime expected',
        self::INVALID_DATE => 'The input does not appear to be a valid date',
        self::FALSEFORMAT => "The input does not fit the date format '%format%'",
        self::IN_FUTURE => 'Date of birth must not be in the future',
    ];

    #[\Override]
    public function isValid($value)
    {
        if (!parent::isValid($value)) {
            return false;
        }

        $date = $this->convertToDateTime($value, false);
        if ($date->format('Y-m-d') > date('Y-m-d')) {
            $this->error(self::IN_FUTURE);
            return false;
        }

        return true;
    }
}
