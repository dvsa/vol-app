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
    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     * @param FlashMessengerHelperService $flashMessenger
     */
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
