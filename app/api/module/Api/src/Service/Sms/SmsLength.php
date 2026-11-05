<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

/**
 * Estimates how many characters and texts an SMS body will use.
 *
 * GSM-7 gives 160 characters in a single text and 153 per part when split. Any character
 * outside GSM-7 switches the whole message to UCS-2: 70 single, 67 per part.
 * GSM-7 extension characters take two characters each.
 */
final class SmsLength
{
    private const string GSM_BASIC = "@£\$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?"
        . "¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";

    private const string GSM_EXTENSION = "\f^{}\\[~]|€";

    /**
     * @return array{characters:int, fragments:int}
     */
    public static function estimate(string $text): array
    {
        if ($text === '') {
            return ['characters' => 0, 'fragments' => 0];
        }

        $gsm = true;
        $count = 0;
        foreach (mb_str_split($text) as $char) {
            if (mb_strpos(self::GSM_BASIC, $char) !== false) {
                $count++;
            } elseif (mb_strpos(self::GSM_EXTENSION, $char) !== false) {
                $count += 2;
            } else {
                $gsm = false;
                break;
            }
        }

        if (!$gsm) {
            // UCS-2 counts UTF-16 code units, so an emoji outside the BMP is two.
            $count = intdiv(strlen((string) mb_convert_encoding($text, 'UTF-16BE', 'UTF-8')), 2);
            [$single, $part] = [70, 67];
        } else {
            [$single, $part] = [160, 153];
        }

        $fragments = $count <= $single ? 1 : (int) ceil($count / $part);

        return ['characters' => $count, 'fragments' => $fragments];
    }
}
