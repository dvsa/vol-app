<?php

declare(strict_types=1);

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;

class PsvDocumentaryEvidenceLarge implements MapperInterface
{
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        return [
            'data' => [
                'version' => $data['version'],
            ],
        ];
    }

    public static function mapFromForm(array $data): array
    {
        return [
            'data' => [
                'version' => $data['version'],
            ],
        ];
    }
}
