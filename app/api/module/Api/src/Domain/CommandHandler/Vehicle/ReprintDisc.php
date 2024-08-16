<?php

/**
 * Reprint Disc
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle;

use Dvsa\Olcs\Api\Domain\Command\Vehicle\CreateGoodsDiscs as CreateGoodsDiscsCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\CeaseActiveDiscs as CeaseCmd;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;

/**
 * Reprint Disc
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ReprintDisc extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'LicenceVehicle';

    public function handleCommand(CommandInterface $command)
    {
        $ids = [];

        $licenceVehicles = $this->getRepo()->fetchByIds($command->getIds());

        /** @var LicenceVehicle $licenceVehicle */
        foreach ($licenceVehicles as $licenceVehicle) {
            $activeDisc = $licenceVehicle->getActiveDisc();

            if ($activeDisc !== null && $activeDisc->getDiscNo() !== null) {
                $ids[] = $licenceVehicle->getId();
            }
        }

        if (!empty($ids)) {
            $dtoData = $command->getArrayCopy();
            $dtoData['ids'] = $ids;

            $this->result->merge($this->handleSideEffect(CeaseCmd::create($dtoData)));

            $dtoData['isCopy'] = 'Y';

            $this->result->merge($this->handleSideEffect(CreateGoodsDiscsCmd::create($dtoData)));
        }

        return $this->result;
    }
}
