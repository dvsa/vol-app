<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Rtf;

use Dvsa\Olcs\Api\Service\Document\Rtf\RtfEncoder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RtfEncoder::class)]
class RtfEncoderTest extends TestCase
{
    /**
     * Builds the RTF unicode entity string the encoder is expected to emit for
     * the given 16-bit code units, e.g. ent(233) === the entity for 'é'. Kept
     * as a helper so no literal \uNNNN escape sequence ever appears in this
     * file's source (which some tooling mistakes for a Unicode escape).
     */
    private static function ent(int ...$codeUnits): string
    {
        $out = '';
        foreach ($codeUnits as $unit) {
            $out .= '\uc0{\u' . $unit . '}';
        }

        return $out;
    }

    #[DataProvider('quoteRtfCodeProvider')]
    public function testQuoteRtfCode(string $input, bool $convertNewlines, string $expected): void
    {
        $this->assertSame($expected, RtfEncoder::quoteRtfCode($input, $convertNewlines));
    }

    /**
     * @return array<string, array{0: string, 1: bool, 2: string}>
     */
    public static function quoteRtfCodeProvider(): array
    {
        return [
            'plain ascii untouched' => ['Hello world', true, 'Hello world'],
            'single backslash escaped' => ['\\', true, '\\\\'],
            'braces escaped' => ['{}', true, '\{\}'],
            'backslash, braces and newline' => ["\\{}\n", true, '\\\\\{\}\line '],
            'lf converted' => ["a\nb", true, 'a\line b'],
            'crlf converted' => ["a\r\nb", true, 'a\line b'],
            'cr converted' => ["a\rb", true, 'a\line b'],
            'mixed newlines converted' => ["x\r\ny\rz\nq", true, 'x\line y\line z\line q'],
            'newlines preserved when disabled' => ["a\nb", false, "a\nb"],
            'braces still escaped when newlines disabled' => ["{a}\n", false, "\{a\}\n"],
        ];
    }

    #[DataProvider('getUnicodeEntitiesProvider')]
    public function testGetUnicodeEntities(string $input, string $expected): void
    {
        $this->assertSame($expected, RtfEncoder::getUnicodeEntities($input));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function getUnicodeEntitiesProvider(): array
    {
        return [
            'ascii passes through untouched' => ['abc123 !@#', 'abc123 !@#'],
            'rtf control chars are ascii and untouched' => ['\{}', '\{}'],
            'latin-1 accent (2-byte)' => ['à', self::ent(224)],
            'first 2-byte char U+0080' => ["\u{0080}", self::ent(128)],
            'last 2-byte char U+07FF' => ["\u{07FF}", self::ent(2047)],
            'first 3-byte char U+0800' => ["\u{0800}", self::ent(2048)],
            'cyrillic (3-byte)' => ['к', self::ent(1082)],
            'black square U+25A0 (3-byte)' => ['■', self::ent(9632)],
            'last 3-byte char U+FFFF' => ["\u{FFFF}", self::ent(65535)],
            'byte order mark is stripped' => ["\u{FEFF}", ''],
            'bom stripped but surrounding text kept' => ["a\u{FEFF}b", 'ab'],
            'mixed ascii and non-ascii' => ['café', 'caf' . self::ent(233)],
        ];
    }

    /**
     * Supplementary-plane characters (U+10000 and above) are the deliberate
     * behavioural fix: the old library corrupted them, we emit the correct
     * UTF-16 surrogate pair.
     */
    #[DataProvider('supplementaryPlaneProvider')]
    public function testGetUnicodeEntitiesEncodesSupplementaryPlaneAsSurrogatePairs(
        string $input,
        string $expected,
    ): void {
        $this->assertSame($expected, RtfEncoder::getUnicodeEntities($input));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function supplementaryPlaneProvider(): array
    {
        return [
            // U+10000 – first supplementary code point -> surrogates D800 DC00
            'first supplementary U+10000' => ["\u{10000}", self::ent(55296, 56320)],
            // U+1F600 GRINNING FACE -> surrogates D83D DE00
            'grinning face emoji U+1F600' => ["\u{1F600}", self::ent(55357, 56832)],
            // U+10FFFF – last valid code point -> surrogates DBFF DFFF
            'last code point U+10FFFF' => ["\u{10FFFF}", self::ent(56319, 57343)],
            'emoji between ascii' => ["a\u{1F600}b", 'a' . self::ent(55357, 56832) . 'b'],
        ];
    }

    /**
     * An invalid 4-byte UTF-8 lead (0xF5-0xF7) decodes to a code point above the
     * Unicode maximum (U+10FFFF); it must be dropped, never emitted as a
     * malformed surrogate half.
     */
    public function testInvalidFourByteSequenceProducesNoSurrogate(): void
    {
        // 0xF7 0xBF 0xBF 0xBF decodes to 0x1FFFFF, which is > U+10FFFF.
        $this->assertSame('', RtfEncoder::getUnicodeEntities("\xF7\xBF\xBF\xBF"));
    }
}
