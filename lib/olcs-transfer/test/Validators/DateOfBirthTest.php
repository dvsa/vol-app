<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\DateOfBirth;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DateOfBirthTest extends TestCase
{
    #[DataProvider('dates')]
    public function testDateOfBirthValidation(string $value, bool $valid, ?string $error): void
    {
        $validator = new DateOfBirth(['format' => 'Y-m-d']);

        self::assertSame($valid, $validator->isValid($value));
        if ($error !== null) {
            self::assertArrayHasKey($error, $validator->getMessages());
        }
    }

    public static function dates(): array
    {
        $today = new \DateTimeImmutable('now', new \DateTimeZone('Europe/London'));

        return [
            'past' => ['1990-01-01', true, null],
            'today' => [$today->format('Y-m-d'), true, null],
            'tomorrow' => [$today->modify('+1 day')->format('Y-m-d'), false, 'inFuture'],
            'far future' => ['2099-01-01', false, 'inFuture'],
            'invalid date' => ['2023-02-29', false, 'dateInvalidDate'],
            'malformed' => ['not-a-date', false, 'dateInvalidDate'],
        ];
    }

    #[DataProvider('londonMidnightDates')]
    public function testUsesLondonCalendarDate(
        string $instant,
        string $defaultTimezone,
        string $birthDate,
        bool $valid
    ): void {
        $originalTimezone = date_default_timezone_get();
        date_default_timezone_set($defaultTimezone);

        try {
            $validator = new DateOfBirth(
                ['format' => 'Y-m-d'],
                static fn (): \DateTimeImmutable => new \DateTimeImmutable($instant)
            );

            self::assertSame($valid, $validator->isValid($birthDate));
            if (!$valid) {
                self::assertArrayHasKey(DateOfBirth::IN_FUTURE, $validator->getMessages());
            }
        } finally {
            date_default_timezone_set($originalTimezone);
        }
    }

    public static function londonMidnightDates(): array
    {
        return [
            'BST London today while UTC is yesterday' => ['2026-07-01 23:30:00 UTC', 'UTC', '2026-07-02', true],
            'BST London tomorrow while UTC is yesterday' => ['2026-07-01 23:30:00 UTC', 'UTC', '2026-07-03', false],
            'BST before London midnight' => ['2026-07-01 22:30:00 UTC', 'Pacific/Kiritimati', '2026-07-02', false],
            'GMT after London midnight' => ['2026-01-02 00:30:00 UTC', 'America/Los_Angeles', '2026-01-02', true],
            'GMT before London midnight' => ['2026-01-01 23:30:00 UTC', 'Pacific/Kiritimati', '2026-01-02', false],
        ];
    }
}
