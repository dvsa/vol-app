<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Document\Rtf;

/**
 * Minimal RTF text-encoding helpers.
 *
 * Reproduces the only two functions the document/bookmark pipeline ever used
 * from the (unmaintained) phprtflite/phprtflite library:
 *   - PHPRtfLite::quoteRtfCode()
 *   - PHPRtfLite_Utf8::getUnicodeEntities()
 *
 * For every input the old library handled correctly the output is byte-for-byte
 * identical; this was verified against the real library across the whole Basic
 * Multilingual Plane before it was removed. The one deliberate divergence is
 * that supplementary-plane characters (U+10000 and above, e.g. emoji) are now
 * decoded and emitted correctly as UTF-16 surrogate pairs; the old library
 * silently corrupted their bytes because its decoder only understood 1-3 byte
 * UTF-8 sequences.
 */
final class RtfEncoder
{
    /**
     * Escapes RTF control characters and, optionally, converts newlines to
     * RTF line breaks.
     */
    public static function quoteRtfCode(string $text, bool $convertNewlines = true): string
    {
        // escape backslashes and curly brackets
        $text = str_replace(['\\', '{', '}'], ['\\\\', '\\{', '\\}'], $text);

        if ($convertNewlines) {
            // convert breaks into an rtf break
            $text = str_replace(["\r\n", "\n", "\r"], '\line ', $text);
        }

        return $text;
    }

    /**
     * Converts a UTF-8 string into RTF, replacing every non-ASCII character
     * with its RTF unicode entity and passing ASCII through untouched.
     */
    public static function getUnicodeEntities(string $text): string
    {
        $entities = '';

        foreach (self::utf8ToCodePoints($text) as $codePoint) {
            if ($codePoint === 0xFEFF) {
                // strip byte-order marks / zero-width no-break spaces
                continue;
            }

            if ($codePoint <= 0x7F) {
                $entities .= chr($codePoint);
            } elseif ($codePoint <= 0xFFFF) {
                $entities .= '\uc0{\u' . $codePoint . '}';
            } elseif ($codePoint <= 0x10FFFF) {
                // RTF's \u control word only carries a 16-bit code unit, so a
                // supplementary-plane character must be written as the two
                // halves of its UTF-16 surrogate pair.
                $offset = $codePoint - 0x10000;
                $high = 0xD800 + ($offset >> 10);
                $low = 0xDC00 + ($offset & 0x3FF);
                $entities .= '\uc0{\u' . $high . '}\uc0{\u' . $low . '}';
            }
            // Code points above U+10FFFF are only reachable from invalid UTF-8
            // lead bytes (0xF5-0xF7); drop them rather than emit a malformed
            // surrogate half.
        }

        return $entities;
    }

    /**
     * Decodes a UTF-8 string into an array of Unicode code points.
     *
     * Handles 1-4 byte sequences. Behaviour for 1-3 byte sequences is a
     * verbatim reproduction of the old library's arithmetic (so output stays
     * byte-identical); the 4-byte branch is the new, correct handling.
     *
     * @return int[]
     */
    private static function utf8ToCodePoints(string $str): array
    {
        $codePoints = [];
        $values = [];
        $lookingFor = 1;

        for ($i = 0, $len = strlen($str); $i < $len; $i++) {
            $thisValue = ord($str[$i]);

            if ($thisValue < 128) {
                $codePoints[] = $thisValue;
                continue;
            }

            if ($values === []) {
                $lookingFor = match (true) {
                    $thisValue < 224 => 2,
                    $thisValue < 240 => 3,
                    default => 4,
                };
            }

            $values[] = $thisValue;

            if (count($values) === $lookingFor) {
                $codePoints[] = match ($lookingFor) {
                    2 => (($values[0] % 32) * 64) + ($values[1] % 64),
                    3 => (($values[0] % 16) * 4096) + (($values[1] % 64) * 64) + ($values[2] % 64),
                    default => (($values[0] % 8) * 262144) + (($values[1] % 64) * 4096)
                        + (($values[2] % 64) * 64) + ($values[3] % 64),
                };
                $values = [];
                $lookingFor = 1;
            }
        }

        return $codePoints;
    }
}
