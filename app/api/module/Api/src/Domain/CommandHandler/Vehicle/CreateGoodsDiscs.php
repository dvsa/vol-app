<?php

/**
 * Create Goods Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\CeaseActiveDiscs as CeaseCmd;

/**
 * Create Goods Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateGoodsDiscs extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'GoodsDisc';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $count = 0;

        $isCopy = $command->getIsCopy();

        foreach ($command->getIds() as $id) {

            /** @var LicenceVehicle $licenceVehicle */
            $licenceVehicle = $this->getRepo()->getReference(LicenceVehicle::class, $id);

            $goodsDisc = new GoodsDisc($licenceVehicle);
            $goodsDisc->setIsCopy($isCopy);

            $this->getRepo()->save($goodsDisc);
            $count++;
        }

        $result->addMessage($count . ' Disc(s) created');

        return $result;
    }
}
