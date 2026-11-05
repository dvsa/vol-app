<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Validators;

use Laminas\Validator\AbstractValidator;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

/**
 * Mirrors GOV.UK Notify's UK phone number validation, but also allows landlines.
 *
 * @see notifications_utils/recipient_validation/phone_number.py
 */
final class SmsPhoneNumber extends AbstractValidator
{
    public const INVALID = 'smsPhoneInvalid';

    private const UK_COUNTRY_CODE = 44;

    private const ALLOWED_TYPES = [
        PhoneNumberType::FIXED_LINE,
        PhoneNumberType::MOBILE,
        PhoneNumberType::FIXED_LINE_OR_MOBILE,
        PhoneNumberType::UAN,
        PhoneNumberType::PERSONAL_NUMBER,
    ];

    protected $messageTemplates = [
        self::INVALID => 'contact-number-sms-invalid',
    ];

    #[\Override]
    public function isValid($value): bool
    {
        if (!is_string($value) || self::normalise($value) === null) {
            $this->error(self::INVALID);
            return false;
        }

        return true;
    }

    /**
     * Returns the number in E.164 format, or null if it is not an acceptable UK number
     */
    public static function normalise(string $value): ?string
    {
        // only digits, whitespace and ()-+ are allowed
        if (preg_match('/^[\d\s()\-+]+$/', $value) !== 1) {
            return null;
        }

        $digits = preg_replace('/[\s()\-]/', '', $value);
        if ($digits === null || preg_match('/\d/', $digits) !== 1) {
            return null;
        }

        $util = PhoneNumberUtil::getInstance();

        foreach (self::candidates($digits) as $candidate) {
            try {
                $parsed = $util->parse($candidate, 'GB');
            } catch (NumberParseException) {
                continue;
            }

            if (self::isAcceptable($util, $parsed)) {
                return $util->format($parsed, PhoneNumberFormat::E164);
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    private static function candidates(string $digits): array
    {
        $candidates = [$digits];

        // 0044... is the same as +44...
        if (str_starts_with($digits, '00')) {
            $candidates[] = '+' . substr($digits, 2);
        }

        // Notify's retry: drop the plus and leading zeros, then treat as international
        $stripped = ltrim($digits, '+0');
        if ($stripped !== '') {
            $candidates[] = '+' . $stripped;
        }

        return array_values(array_unique($candidates));
    }

    private static function isAcceptable(PhoneNumberUtil $util, PhoneNumber $number): bool
    {
        if ($number->getCountryCode() !== self::UK_COUNTRY_CODE) {
            return false;
        }

        // Ofcom reserved TV and drama range, libphonenumber may call it invalid
        if (preg_match('/^7700900\d{3}$/', $util->getNationalSignificantNumber($number)) === 1) {
            return true;
        }

        return $util->isPossibleNumber($number)
            && $util->isValidNumber($number)
            && in_array($util->getNumberType($number), self::ALLOWED_TYPES, true);
    }
}
