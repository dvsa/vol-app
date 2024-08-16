<?php

/**
 * Create PSV Vehicle List Document for discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Discs;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create PSV Vehicle List Document for discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreatePsvVehicleListForDiscs extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';

    public function handleCommand(CommandInterface $command)
    {
        $dtoData = [
            'template' => 'PSVVehiclesList',
            'query' => [
                'licence' => $command->getId(),
                'user' => $command->getUser()
            ],
            'knownValues' => $command->getKnownValues(),
            'description'   => 'New disc notification',
            'licence'       => $command->getId(),
            'category'      => Category::CATEGORY_LICENSING,
            'subCategory'   => Category::DOC_SUB_CATEGORY_LICENCE_VEHICLE_LIST,
            'isExternal'    => false,
        ];

        $result = $this->handleSideEffect(GenerateAndStore::create($dtoData));

        $printData = [
            'documentId' => $result->getId('document'),
            'jobName' => 'New disc notification',
            'user' => $command->getUser(),
            'isDiscPrinting' => true,
        ];

        $this->handleSideEffect(Enqueue::create($printData));

        return $result;
    }
}
