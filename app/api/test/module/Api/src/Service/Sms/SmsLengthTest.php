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
            // Notify swaps these for GSM characters before sending; 100 a's would be two UCS-2 texts
            'curly apostrophe stays gsm' => [str_repeat('a', 100) . "I\u{2019}m", 103, 1],
            'curly apostrophe in a 160 text' => [str_repeat('a', 159) . "\u{2019}", 160, 1],
            'curly single quotes' => [str_repeat('a', 100) . "\u{2018}hi\u{2019}", 104, 1],
            'curly double quotes' => [str_repeat('a', 100) . "\u{201C}hi\u{201D}", 104, 1],
            'en and em dashes' => [str_repeat('a', 100) . "a\u{2013}b\u{2014}c", 105, 1],
            'ellipsis is three dots' => [str_repeat('a', 100) . "wait\u{2026}", 107, 1],
            'non-breaking space' => [str_repeat('a', 100) . "a\u{00A0}b", 103, 1],
            'other unicode still ucs2' => [str_repeat('a', 100) . "\u{0107}", 101, 2],
        ];
    }
}
