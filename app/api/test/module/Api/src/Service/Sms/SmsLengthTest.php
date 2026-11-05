<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Sms;

use Dvsa\Olcs\Api\Service\Sms\SmsLength;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SmsLengthTest extends TestCase
{
    #[DataProvider('estimates')]
    public function testEstimate(string $text, int $characters, int $fragments): void
    {
        $this->assertSame(['characters' => $characters, 'fragments' => $fragments], SmsLength::estimate($text));
    }

    public static function estimates(): array
    {
        return [
            'empty' => ['', 0, 0],
            'one char' => ['a', 1, 1],
            '160 is one text' => [str_repeat('a', 160), 160, 1],
            '161 is two texts' => [str_repeat('a', 161), 161, 2],
            '306 is two texts' => [str_repeat('a', 306), 306, 2],
            '307 is three texts' => [str_repeat('a', 307), 307, 3],
            'gsm accented basic char' => ['é', 1, 1],
            'euro counts two' => ['€', 2, 1],
            'brackets count two' => ['[]{}^~|\\', 16, 1],
            'extension chars push over 160' => [str_repeat('a', 159) . '€', 161, 2],
            'emoji switches to ucs2' => ['hi 😀', 5, 1],
            'ucs2 70 is one text' => ['😀' . str_repeat('a', 68), 70, 1],
            'ucs2 71 is two texts' => ['😀' . str_repeat('a', 69), 71, 2],
            'ucs2 134 is two texts' => ['😀' . str_repeat('a', 132), 134, 2],
            'ucs2 135 is three texts' => ['😀' . str_repeat('a', 133), 135, 3],
        ];
    }
}
