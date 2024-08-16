<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth\Service;

class PasswordService
{
    public const REALM_INTERNAL = 'internal';
    public const REALM_SELFSERVE = 'selfserve';
    public const MINIMUM_LENGTH = 12;

    public const ERR_MESSAGE_TOO_SHORT = 'Password length cannot be less than 12';

    /**
     * Generates a password
     *
     * @param int $length Defaults to PasswordService::MINIMUM_LENGTH
     * @return string
     * @throws \Exception
     */
    public function generatePassword(int $length = self::MINIMUM_LENGTH): string
    {
        if ($length < static::MINIMUM_LENGTH) {
            throw new \InvalidArgumentException(static::ERR_MESSAGE_TOO_SHORT);
        }

        $digits    = range('0', '9');
        $lowercase = range('a', 'z');
        $uppercase = range('A', 'Z');
        $special   = str_split('-=~!@#$%^&*()_+,./?;:');
        $combined  = array_merge($digits, $lowercase, $uppercase, $special);

        // Ensure our resulting password has at least ONE of each requirement
        // Uses random_int instead of array_rand for cryptographically secure random number generation.
        $passwordCharacters = [
            $digits[random_int(0, count($digits) - 1)],
            $lowercase[random_int(0, count($lowercase) - 1)],
            $uppercase[random_int(0, count($uppercase) - 1)],
            $special[random_int(0, count($special) - 1)]
        ];

        for ($i = count($passwordCharacters); $i < $length; $i++) {
            $passwordCharacters[] = $combined[random_int(0, count($combined) - 1)];
        }

        shuffle($passwordCharacters);

        return implode('', $passwordCharacters);
    }
}
