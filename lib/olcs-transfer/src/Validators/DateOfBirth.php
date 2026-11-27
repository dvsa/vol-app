<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Validators;

use Laminas\Validator\Date;

final class DateOfBirth extends Date
{
    public const IN_FUTURE = 'inFuture';

    private readonly \Closure $clock;

    protected $messageTemplates = [
        self::INVALID => 'Invalid type given. String, integer, array or DateTime expected',
        self::INVALID_DATE => 'The input does not appear to be a valid date',
        self::FALSEFORMAT => "The input does not fit the date format '%format%'",
        self::IN_FUTURE => 'Date of birth must not be in the future',
    ];

    public function __construct($options = [], ?\Closure $clock = null)
    {
        parent::__construct($options);
        $this->clock = $clock ?? static fn (): \DateTimeImmutable => new \DateTimeImmutable();
    }

    #[\Override]
    public function isValid($value)
    {
        if (!parent::isValid($value)) {
            return false;
        }

        $date = $this->convertToDateTime($value, false);
        $today = ($this->clock)()->setTimezone(new \DateTimeZone('Europe/London'));
        if ((int) $date->format('Ymd') > (int) $today->format('Ymd')) {
            $this->error(self::IN_FUTURE);
            return false;
        }

        return true;
    }
}
