<?php

declare(strict_types=1);

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;

class VehicleSize implements MapperInterface
{
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        return [
            'version' => $data['version'],
            'psvVehicleSize' => [
                'size' => $data['psvWhichVehicleSizes']['id'] ?? null,
            ],
        ];
    }

    public static function mapFromForm(array $data): array
    {
        return [
            'version' => $data['version'],
            'psvVehicleSize' => $data['psvVehicleSize']['size'],
        ];
    }
}
