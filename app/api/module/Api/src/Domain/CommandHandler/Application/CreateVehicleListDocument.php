<?php

/**
 * Create Vehicle List Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVehicleListDocument as LicenceCmd;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Create Vehicle List Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateVehicleListDocument extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['Document'];

    /**
     * Create a vehicle list document
     *
     * @param CommandInterface $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchUsingId($command);

        $dtoData = $command->getArrayCopy();
        $dtoData['id'] = $application->getLicence()->getId();

        $result = $this->handleSideEffect(LicenceCmd::create($dtoData));

        $docId = $result->getId('document');

        /** @var Document $document */
        $document = $this->getRepo('Document')->fetchById($docId);
        $document->setApplication($application);
        $this->getRepo('Document')->save($document);

        return $result;
    }
}
