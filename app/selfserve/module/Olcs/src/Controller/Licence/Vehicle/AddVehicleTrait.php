<?php

namespace Olcs\Controller\Licence\Vehicle;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Command\Licence\CreateGoodsVehicle;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePsvVehicle;
use Permits\Data\Mapper\MapperManager;

trait AddVehicleTrait
{
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager,
        FlashMessengerHelperService $flashMessenger
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager, $flashMessenger);
    }

    /**
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
            $commandData['platedWeight'] = $platedWeight;
            return CreateGoodsVehicle::create($commandData);
        }

        return CreatePsvVehicle::create($commandData);
    }
}
