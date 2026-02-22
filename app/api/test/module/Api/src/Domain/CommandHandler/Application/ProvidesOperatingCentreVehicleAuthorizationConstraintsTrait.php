<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

trait ProvidesOperatingCentreVehicleAuthorizationConstraintsTrait
{
    /**
     * @return array
     */
    public static function operatingCentreVehicleAuthorisationConstraintsDataProvider(): array
    {
        return [
            'no operating centres' => [
                /* Operating Centres ...[HGVs]: */ [],
                /* Constraints: */ [
                    'minHgvVehicleAuth' => 0,
                    'maxHgvVehicleAuth' => 0,
                ]
            ],
            'one operating centre with HGVs' => [
                /* Operating Centres ...[HGVs]: */ [[1]],
                /* Constraints: */ [
                    'minHgvVehicleAuth' => 1,
                    'maxHgvVehicleAuth' => 1,
                ]
            ],
            'more then one operating centre with HGVs' => [
                /* Operating Centres ...[HGVs]: */ [[1], [2]],
                /* Constraints: */ [
                    'minHgvVehicleAuth' => 2,
                    'maxHgvVehicleAuth' => 3,
                ]
            ],
        ];
    }
}
