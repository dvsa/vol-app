<?php

/**
 * Create a Transport Manager Delete Delta
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Variation;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Psr\Container\ContainerInterface;

/**
 * Create a Transport Manager Delete Delta
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class TransportManagerDeleteDelta extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';
    /**
     * @var \Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication
     */
    protected $tmaRepo;
    /**
     * @var \Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence
     */
    protected $tmlRepo;

    public function handleCommand(CommandInterface $command)
    {
        /* @var $application Application */
        $application = $this->getRepo()->fetchUsingId($command);

        $result = new Result();

        $applicationIds = [];
        foreach ($command->getTransportManagerLicenceIds() as $tmlId) {
            /* @var $tml \Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence */
            $tml = $this->tmlRepo->fetchById($tmlId);

            // If the TML licence is different to the application licence something has gone wrong.
            if ($application->getLicence() !== $tml->getLicence()) {
                throw new \Dvsa\Olcs\Api\Domain\Exception\NotFoundException(
                    "Transport Manager Licence ID $tmlId is not connected to this Application"
                );
            }

            // Create a new TMA record with action D(Delete)
            $tma = new TransportManagerApplication();
            $tma->setApplication($application);
            $tma->setTransportManager($tml->getTransportManager());
            $tma->setAction(TransportManagerApplication::ACTION_DELETE);

            $this->tmaRepo->save($tma);

            $result->addMessage('Transport manager application ID ' . $tma->getId() . ' delete Delata created');

            $applicationIds[$tma->getApplication()->getId()] = $tma->getApplication()->getId();
        }

        foreach ($applicationIds as $applicationId) {
            $result->merge(
                $this->handleSideEffect(
                    \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::create(
                        ['id' => $applicationId, 'section' => 'transportManagers']
                    )
                )
            );
        }

        return $result;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->tmaRepo = $container->get('RepositoryServiceManager')->get('TransportManagerApplication');
        $this->tmlRepo = $container->get('RepositoryServiceManager')->get('TransportManagerLicence');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
