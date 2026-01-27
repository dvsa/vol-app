<?php

/**
 * Update Application Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationOperatingCentre;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\ApplicationOperatingCentre\Update as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;

/**
 * Update Application Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class Update extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'ApplicationOperatingCentre';

    protected $extraRepos = ['Document', 'OperatingCentre'];

    /**
     * @var \Dvsa\Olcs\Api\Domain\Service\OperatingCentreHelper
     */
    protected $helper;

    /**
     * @param Cmd $command
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $aoc ApplicationOperatingCentre */
        $aoc = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $application = $aoc->getApplication();

        // if only one OC on licence then allow reseting of TA
        if ($application->isNew() && $application->getOperatingCentres()->count() === 1) {
            // if postcode has changed
            if ($command->getAddress()['postcode'] !== $aoc->getOperatingCentre()->getAddress()->getPostcode()) {
                $application->getLicence()->setTrafficArea(null);
            }
        }

        $this->helper->validate($application, $command, $this->isGranted(Permission::SELFSERVE_USER), $aoc);

        $operatingCentre = $aoc->getOperatingCentre();

        if ($command->getAddress() !== null) {
            $data = $command->getAddress();
            $this->result->merge($this->handleSideEffect(SaveAddress::create($data)));
            
            if ($data['version'] != $operatingCentre->getAddress()->getVersion()) {
                $this->eventHistoryCreator->create($operatingCentre->getAddress(), EventHistoryTypeEntity::EVENT_CODE_EDIT_OPERATING_CENTRE, null, $application->getLicence());
            }
        }

        // Link, unlinked documents to the OC
        $this->helper->saveDocuments($application, $operatingCentre, $this->getRepo('Document'));

        $this->helper->updateOperatingCentreLink(
            $aoc,
            $application,
            $command,
            $this->getRepo('ApplicationOperatingCentre')
        );

        if ($application->getTrafficArea() === null) {
            $data = ['id' => $application->getId(), 'operatingCentre' => $operatingCentre->getId()];
            $this->result->merge(
                $this->handleSideEffect(
                    \Dvsa\Olcs\Api\Domain\Command\Application\SetDefaultTrafficAreaAndEnforcementArea::create($data)
                )
            );
        }

        $completionData = ['id' => $application->getId(), 'section' => 'operatingCentres'];
        $this->result->merge($this->handleSideEffect(UpdateApplicationCompletionCmd::create($completionData)));

        return $this->result;
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->helper = $container->get('OperatingCentreHelper');
        $this->eventHistoryCreator = $container->get('EventHistoryCreator');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
