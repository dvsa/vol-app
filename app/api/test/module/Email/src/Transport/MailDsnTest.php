<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Transport;

use Dvsa\Olcs\Email\Transport\MailDsn;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(MailDsn::class)]
final class MailDsnTest extends TestCase
{
    #[DataProvider('unset')]
    public function testUnset(mixed $dsn): void
    {
        $this->assertNull(MailDsn::configured($dsn));
    }

    public static function unset(): array
    {
        return [
            'missing' => [null],
            'not a string' => [123],
            'empty' => [''],
            'blank' => ['  '],
            'placeholder' => ['%olcs_mail_dsn%'],
            'padded placeholder' => [' %olcs_mail_dsn% '],
        ];
    }

    #[DataProvider('set')]
    public function testSet(string $dsn, string $expected): void
    {
        $this->assertSame($expected, MailDsn::configured($dsn));
    }

    public static function set(): array
    {
        return [
            'plain' => ['smtp://host:25', 'smtp://host:25'],
            'percent encoded' => ['govuknotify://user:ab%2Bcd@default', 'govuknotify://user:ab%2Bcd@default'],
            'padded' => [' govuknotify+mailpit://mailpit:1025 ', 'govuknotify+mailpit://mailpit:1025'],
        ];
    }
}
