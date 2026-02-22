<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Command\IrhpApplication\CreateDefaultIrhpPermitApplications;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitType as IrhpPermitTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as IrhpPermitStockEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as IrhpPermitTypeEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow as IrhpPermitWindowEntity;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Create as CreateIrhpApplicationCmd;
use Psr\Container\ContainerInterface;

/**
 * Create Irhp Permit Application
 */
final class Create extends AbstractCommandHandler
{
    public const string LICENCE_INVALID_MSG = 'Licence ID %d with number %s is unable to make an application for %s stock ID %d';

    protected $repoServiceName = 'IrhpApplication';
    protected $extraRepos = ['Licence', 'IrhpPermitStock', 'IrhpPermitType', 'IrhpPermitWindow'];

    /** @var EventHistoryCreator */
    private $eventHistoryCreator;

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     * @throws ForbiddenException
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var CreateIrhpApplicationCmd $command
         * @var IrhpPermitTypeRepo       $irhpPermitTypeRepo
         * @var IrhpPermitTypeEntity     $permitType
         * @var LicenceRepo              $licenceRepo
         * @var LicenceEntity            $licence
         */
        $permitTypeId = $command->getIrhpPermitType();
        $irhpPermitTypeRepo = $this->getRepo('IrhpPermitType');
        $permitType = $irhpPermitTypeRepo->fetchById($permitTypeId);
        $licenceRepo = $this->getRepo('Licence');
        $licence = $licenceRepo->fetchById($command->getLicence());

        $permitStockId = $command->getIrhpPermitStock();

        /** behaviour differs depending on whether we have a stock id */
        if ($permitStockId === null) {
            /**
             * @var IrhpPermitWindowRepo   $permitWindowRepo
             * @var IrhpPermitWindowEntity $window
             */
            $permitWindowRepo = $this->getRepo('IrhpPermitWindow');
            $window = $permitWindowRepo->fetchLastOpenWindowByIrhpPermitType(
                $permitTypeId,
                new \DateTime()
            );

            $stock = $window->getIrhpPermitStock();
            $permitStockId = $stock->getId();
        } else {
            /**
             * @var IrhpPermitStockRepo   $permitStockRepo
             * @var IrhpPermitStockEntity $stock
             */
            $permitStockRepo = $this->getRepo('IrhpPermitStock');
            $stock = $permitStockRepo->fetchById($permitStockId);
        }

        if (!$licence->canMakeIrhpApplication($stock)) {
            $message = sprintf(
                self::LICENCE_INVALID_MSG,
                $licence->getId(),
                $licence->getLicNo(),
                $permitType->getName()->getDescription(),
                $permitStockId
            );

            throw new ForbiddenException($message);
        }

        $source = $command->getFromInternal() ? IrhpInterface::SOURCE_INTERNAL : IrhpInterface::SOURCE_SELFSERVE;
        $irhpApplication = IrhpApplicationEntity::createNew(
            $this->refData($source),
            $this->refData(IrhpInterface::STATUS_NOT_YET_SUBMITTED),
            $permitType,
            $licence,
            date('Y-m-d')
        );

        /** @var IrhpApplicationRepo $irhpApplicationRepo */
        $irhpApplicationRepo = $this->getRepo();
        $irhpApplicationRepo->save($irhpApplication);

        // create Event History record
        $this->eventHistoryCreator->create($irhpApplication, EventHistoryTypeEntity::IRHP_APPLICATION_CREATED);

        $this->result->merge(
            $this->handleSideEffect(
                CreateDefaultIrhpPermitApplications::create(
                    [
                        'id' => $irhpApplication->getId(),
                        'irhpPermitStock' => $permitStockId
                    ]
                )
            )
        );

        $this->result->addId('irhpApplication', $irhpApplication->getId());
        $this->result->addMessage('IRHP Application created successfully');

        return $this->result;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->eventHistoryCreator = $container->get('EventHistoryCreator');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
