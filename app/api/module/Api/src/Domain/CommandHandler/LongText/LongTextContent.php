<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\LongText;

use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/** Content arrives as a JSON string from the editor. */
final class LongTextContent
{
    public static function decode(?string $json): array
    {
        if ($json === null || $json === '') {
            throw new ValidationException(['content' => 'Long Text content is required']);
        }

        try {
            $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new ValidationException(['content' => 'Long Text content must be valid JSON']);
        }

        if (!is_array($decoded) || !isset($decoded['blocks']) || !is_array($decoded['blocks'])) {
            throw new ValidationException(['content' => 'Long Text content must be an EditorJS document']);
        }

        return $decoded;
    }
}
