<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si\Applied;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SiEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenalty as SiPenaltyEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyType as SiPenaltyTypeEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyErruRequested as SiPenaltyErruRequestedEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Si\Applied\Create as CreatePenaltyCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create Erru applied penalty
 */
final class Create extends AbstractCommandHandler
{
    public const string DATE_FORMAT = 'Y-m-d';

    protected $repoServiceName = 'SiPenalty';
    protected $extraRepos = ['SeriousInfringement'];

    /**
     * Create Erru applied penalty
     *
     * @param CommandInterface $command
     * @return Result
     * @throws Exception\ValidationException
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var SiEntity $si
         * @var CaseEntity $case
         * @var SiPenaltyTypeEntity $siPenaltyType
         * @var CreatePenaltyCmd $command
         */
        $si = $this->getRepo('SeriousInfringement')->fetchById($command->getSi());
        $case = $si->getCase();

        if (!$case->canAddSi()) {
            throw new Exception\ValidationException(['Invalid action for the case']);
        }

        $siPenaltyType = $this->getRepo()->getReference(SiPenaltyTypeEntity::class, $command->getSiPenaltyType());
        $siRequestedErru = $this->getRepo()->getReference(SiPenaltyErruRequestedEntity::class, $command->getErruPenaltyRequested());
        $startDate
            = ($command->getStartDate() !== null)
                ? \DateTime::createFromFormat(self::DATE_FORMAT, $command->getStartDate()) : null;
        $endDate
            = ($command->getEndDate() !== null)
                ? \DateTime::createFromFormat(self::DATE_FORMAT, $command->getEndDate()) : null;

        $penalty = new SiPenaltyEntity(
            $si,
            $siPenaltyType,
            $siRequestedErru,
            $command->getImposed(),
            $startDate,
            $endDate,
            $command->getReasonNotImposed()
        );

        $this->getRepo()->save($penalty);

        $result = new Result();
        $result->addMessage('Applied penalty created');
        $result->addId('si', $si->getId());
        $result->addId('penalty', $penalty->getId());

        return $result;
    }
}
