<?php

declare(strict_types=1);

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;

class PsvWrittenExplanation implements MapperInterface
{
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        return [
            'version' => $data['version'],
            'psvSmallVhlNotes' => $data['psvSmallVhlNotes'],
            'psvTotalVehicleSmall' => $data['psvTotalVehicleSmall'],
            'psvTotalVehicleLarge' => $data['psvTotalVehicleLarge'],
        ];
    }

    public static function mapFromForm(array $data): array
    {
        return [
            'version' => $data['version'],
            'psvSmallVhlNotes' => $data['psvSmallVhlNotes'],
            'psvTotalVehicleSmall' => $data['psvTotalVehicleSmall'],
            'psvTotalVehicleLarge' => $data['psvTotalVehicleLarge'],
        ];
    }
}
