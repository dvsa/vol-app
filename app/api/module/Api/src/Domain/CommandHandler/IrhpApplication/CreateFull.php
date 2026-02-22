<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as IrhpPermitTypeEntity;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\ApplicationUpdater as BilateralApplicationUpdater;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\CreateFull as Cmd;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCountries;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateMultipleNoOfPermits;
use Psr\Container\ContainerInterface;

/**
 * Create Irhp Permit Application
 */
class CreateFull extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'IrhpApplication';
    protected $extraRepos = ['IrhpPermitWindow', 'IrhpPermitApplication'];

    /** @var EventHistoryCreator */
    private $eventHistoryCreator;

    /** @var BilateralApplicationUpdater */
    private $bilateralApplicationUpdater;

    /**
     * Handle command
     *
     * @param Cmd $command command
     *
     * @return Result
     * @throws NotFoundException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var IrhpApplicationRepo $irhpApplicationRepo */
        $irhpApplicationRepo = $this->getRepo();
        /** @var IrhpPermitTypeEntity $permitType */
        $permitType = $irhpApplicationRepo->getReference(IrhpPermitTypeEntity::class, $command->getIrhpPermitType());

        if (!($permitType instanceof IrhpPermitTypeEntity)) {
            throw new NotFoundException('Permit type not found');
        }

        $irhpApplication = $this->createNewIrhpApplication(
            $permitType,
            $irhpApplicationRepo->getReference(LicenceEntity::class, $command->getLicence()),
            $command->getDateReceived()
        );
        $irhpApplicationRepo->save($irhpApplication);

        // create Event History record
        $this->eventHistoryCreator->create($irhpApplication, EventHistoryTypeEntity::IRHP_APPLICATION_CREATED);

        $this->createIrhpPermitApplications($irhpApplication, $command->getIrhpPermitType());
        $this->updateCountries($irhpApplication, $permitType->getId(), $command);

        $irhpApplicationRepo->refresh($irhpApplication);
        $irhpApplication->resetSectionCompletion();

        if ($permitType->getId() == IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_MULTILATERAL) {
            $this->result->merge(
                $this->handleSideEffect(
                    UpdateMultipleNoOfPermits::create([
                        'id' => $irhpApplication->getId(),
                        'permitsRequired' => $command->getPermitsRequired()
                    ])
                )
            );
        } elseif ($permitType->getId() == IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_BILATERAL) {
            $this->bilateralApplicationUpdater->update(
                $irhpApplication,
                $command->getPermitsRequired()
            );
        }

        $irhpApplicationRepo->refresh($irhpApplication);
        $irhpApplication->resetSectionCompletion();

        if ($command->getDeclaration()) {
            $irhpApplication->updateCheckAnswers();
            $irhpApplicationRepo->save($irhpApplication);
            $irhpApplicationRepo->refresh($irhpApplication);
            $irhpApplication->resetSectionCompletion();

            $irhpApplication->makeDeclaration();
            $irhpApplicationRepo->save($irhpApplication);
        }

        $this->result->addId('irhpApplication', $irhpApplication->getId());
        $this->result->addMessage('IRHP Application created successfully');

        return $this->result;
    }

    /**
     * Creates new instance of IrhpApplication
     *
     * @param string $dateReceived
     */
    protected function createNewIrhpApplication(IrhpPermitTypeEntity $permitType, LicenceEntity $licence, $dateReceived)
    {
        return IrhpApplicationEntity::createNew(
            $this->refData(IrhpInterface::SOURCE_INTERNAL),
            $this->refData(IrhpInterface::STATUS_NOT_YET_SUBMITTED),
            $permitType,
            $licence,
            $dateReceived
        );
    }

    /**
     * Creates and saves instances of IrhpPermitApplication as required to accompany the IrhpApplication
     *
     * @param int $permitTypeId
     */
    private function updateCountries(IrhpApplicationEntity $irhpApplication, $permitTypeId, $command)
    {
        if ((int)$permitTypeId !== IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_BILATERAL) {
            return;
        }

        $this->result->merge(
            $this->handleSideEffect(
                UpdateCountries::create([
                    'id' => $irhpApplication->getId(),
                    'countries' => array_keys($command->getPermitsRequired())
                ])
            )
        );
    }

    /**
     * Creates and saves instances of IrhpPermitApplication as required to accompany the IrhpApplication
     *
     * @param int $permitTypeId
     */
    private function createIrhpPermitApplications(IrhpApplicationEntity $irhpApplication, $permitTypeId)
    {
        if ($permitTypeId != IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_MULTILATERAL) {
            return;
        }

        $irhpPermitWindows = $this->getRepo('IrhpPermitWindow')->fetchOpenWindowsByType(
            $permitTypeId,
            new DateTime()
        );

        $irhpPermitApplicationRepo = $this->getRepo('IrhpPermitApplication');

        foreach ($irhpPermitWindows as $irhpPermitWindow) {
            $irhpPermitApplication = IrhpPermitApplicationEntity::createNewForIrhpApplication(
                $irhpApplication,
                $irhpPermitWindow
            );

            $irhpPermitApplicationRepo->save($irhpPermitApplication);
        }
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->bilateralApplicationUpdater = $container->get('PermitsBilateralInternalApplicationUpdater');
        $this->eventHistoryCreator = $container->get('EventHistoryCreator');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
