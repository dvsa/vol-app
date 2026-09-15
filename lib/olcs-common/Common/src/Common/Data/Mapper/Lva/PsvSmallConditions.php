<?php

declare(strict_types=1);

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;

class PsvSmallConditions implements MapperInterface
{
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        return [
            'version' => $data['version'],
            'psvSmallVhlConfirmation' => $data['psvSmallVhlConfirmation'],
            'isOperatingSmallPsvAsPartOfLarge' => $data['isOperatingSmallPsvAsPartOfLarge'],
        ];
    }

    public static function mapFromForm(array $data): array
    {
        return [
            'version' => $data['version'],
            'psvSmallVhlConfirmation' => $data['psvSmallVhlConfirmation'],
            'isOperatingSmallPsvAsPartOfLarge' => $data['isOperatingSmallPsvAsPartOfLarge'],
        ];
    }
}
