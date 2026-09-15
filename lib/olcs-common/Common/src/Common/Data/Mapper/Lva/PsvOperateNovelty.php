<?php

declare(strict_types=1);

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;

class PsvOperateNovelty implements MapperInterface
{
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        return [
            'version' => $data['version'],
            'limousinesNoveltyVehicles' => [
                'psvLimousines' => $data['psvLimousines'],
                'psvNoLimousineConfirmation' => $data['psvNoLimousineConfirmation'],
                'psvOnlyLimousinesConfirmation' => $data['psvOnlyLimousinesConfirmation'],
                'size' => $data['psvWhichVehicleSizes']['id'],
            ],
        ];
    }

    public static function mapFromForm(array $data): array
    {
        $mappedData = [
            'version' => $data['version'],
            'psvLimousines' => $data['limousinesNoveltyVehicles']['psvLimousines'],
            'psvNoLimousineConfirmation' => $data['limousinesNoveltyVehicles']['psvNoLimousineConfirmation'],
            'psvOnlyLimousinesConfirmation' => $data['limousinesNoveltyVehicles']['psvOnlyLimousinesConfirmation'],
            'size' => $data['limousinesNoveltyVehicles']['size'],
        ];

        //in cases where operator has changed their mind, both confirmation boxes may have been ticked
        if ($mappedData['psvLimousines'] === 'Y') {
            $mappedData['psvNoLimousineConfirmation'] = 'N';
        }

        if ($mappedData['psvLimousines'] === 'N') {
            $mappedData['psvOnlyLimousinesConfirmation'] = 'N';
        }

        return $mappedData;
    }
}
