<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApggAppGranted;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermSuccessful;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueue;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Service\Permits\GrantabilityChecker;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Grant as GrantCmd;
use Psr\Container\ContainerInterface;

/**
 * Command Handler to action the granting of an IrhpApplication
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class Grant extends AbstractCommandHandler implements TransactionedInterface
{
    use QueueAwareTrait;

    protected $repoServiceName = 'IrhpApplication';

    protected $extraRepos = ['FeeType'];

    public const string ERR_IRHP_GRANT_CANNOT_GRANT = 'ERR_IRHP_GRANT_CANNOT_GRANT';
    public const string ERR_IRHP_GRANT_TOO_MANY_PERMITS = 'ERR_IRHP_GRANT_TOO_MANY_PERMITS';

    /** @var GrantabilityChecker */
    private $grantabilityChecker;

    /** @var EventHistoryCreator */
    private $eventHistoryCreator;

    /**
     * Handle command
     *
     * @param GrantCmd|CommandInterface $command command
     *
     * @return Result
     * @throws ForbiddenException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var IrhpApplication $irhpApplication */
        $irhpApplicationId = $command->getId();
        $irhpApplication = $this->getRepo()->fetchById($irhpApplicationId);

        if (!$this->grantabilityChecker->isGrantable($irhpApplication)) {
            throw new ForbiddenException(self::ERR_IRHP_GRANT_TOO_MANY_PERMITS);
        }

        if (!$irhpApplication->canBeGranted()) {
            throw new ForbiddenException(self::ERR_IRHP_GRANT_CANNOT_GRANT);
        }

        $irhpApplication->grant($this->refData(IrhpInterface::STATUS_AWAITING_FEE));

        $this->getRepo()->save($irhpApplication);

        // create Event History record
        $this->eventHistoryCreator->create($irhpApplication, EventHistoryTypeEntity::IRHP_APPLICATION_GRANTED);

        $this->result->merge(
            $this->handleSideEffects(
                [
                    $this->getCreateFeeCommand($irhpApplication),
                    $this->getEmailCommand($irhpApplication),
                ]
            )
        );

        $this->result->addMessage('IRHP application granted');
        $this->result->addId('irhpApplication', $irhpApplicationId);

        return $this->result;
    }

    /**
     * @return CreateQueue
     */
    private function getEmailCommand(IrhpApplication $irhpApplication)
    {
        $cmdClass = $irhpApplication->getIrhpPermitType()->isEcmtShortTerm()
            ? SendEcmtShortTermSuccessful::class : SendEcmtApggAppGranted::class;

        return $this->emailQueue(
            $cmdClass,
            ['id' => $irhpApplication->getId()],
            $irhpApplication->getId()
        );
    }

    /**
     * @return CreateFee
     */
    private function getCreateFeeCommand(IrhpApplication $irhpApplication)
    {
        $totalRequired = $irhpApplication->getFirstIrhpPermitApplication()->countPermitsAwarded();

        $feeType = $this->getRepo('FeeType')->getLatestByProductReference(
            $irhpApplication->getIssueFeeProductReference()
        );

        return CreateFee::create(
            [
                'licence' => $irhpApplication->getLicence()->getId(),
                'irhpApplication' => $irhpApplication->getId(),
                'invoicedDate' => date('Y-m-d'),
                'description' => $feeType->getDescription(),
                'feeType' => $feeType->getId(),
                'feeStatus' => Fee::STATUS_OUTSTANDING,
                'quantity' => $totalRequired,
            ]
        );
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->grantabilityChecker = $container->get('PermitsGrantabilityChecker');
        $this->eventHistoryCreator = $container->get('EventHistoryCreator');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
