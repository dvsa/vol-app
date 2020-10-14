<?php

namespace Olcs\Controller\Licence\Vehicle;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Command\Licence\CreateGoodsVehicle;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePsvVehicle;

trait AddVehicleTrait
{
    /**
     * @param string $vrm
     * @param string $make
     * @param bool $confirmDuplicateVehicle
     * @param int $platedWeight
     * @return CreateGoodsVehicle|CreatePsvVehicle|AbstractCommand
     */
    protected function generateCreateVehicleCommand(
        string $vrm,
        string $make,
        bool $confirmDuplicateVehicle = false,
        int $platedWeight = 0
    ): AbstractCommand {
        $commandData = [
            'id' => $this->licenceId,
            'vrm' => $vrm,
            'makeModel' => $make,
        ];

        if ($confirmDuplicateVehicle) {
            $commandData['confirm'] = true;
        }

        if ($this->isGoods()) {
            // TODO: What if we get a goods vehicle with no plated weight?
            $commandData['platedWeight'] = $platedWeight;
            return CreateGoodsVehicle::create($commandData);
        }

        return CreatePsvVehicle::create($commandData);
    }
}
