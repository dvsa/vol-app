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
        return [
            'past' => ['1990-01-01', true, null],
            'today' => [date('Y-m-d'), true, null],
            'tomorrow' => [(new \DateTimeImmutable('tomorrow'))->format('Y-m-d'), false, 'inFuture'],
            'far future' => ['2099-01-01', false, 'inFuture'],
            'invalid date' => ['2023-02-29', false, 'dateInvalidDate'],
            'malformed' => ['not-a-date', false, 'dateInvalidDate'],
        ];
    }
}
