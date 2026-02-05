<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si\Applied;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest as ErruRequestEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenalty as SiPenaltyEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyErruRequested as SiPenaltyErruRequestedEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyType as SiPenaltyTypeEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Si\Applied\Update as UpdatePenaltyCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update Erru applied penalty
 */
final class Update extends AbstractCommandHandler
{
    public const string DATE_FORMAT = 'Y-m-d';

    protected $repoServiceName = 'SiPenalty';

    /**
     * Update Erru applied penalty
     *
     * @param CommandInterface $command
     * @return Result
     * @throws Exception\ValidationException
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var SiPenaltyEntity $penalty
         * @var CaseEntity $case
         * @var ErruRequestEntity $erruRequest
         * @var SiPenaltyTypeEntity $siPenaltyType
         * @var UpdatePenaltyCmd $command
         */
        $penalty = $this->getRepo()->fetchUsingId($command);

        if (!$penalty->getSeriousInfringement()->getCase()->isOpenErruCase()) {
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

        $penalty->update(
            $siPenaltyType,
            $siRequestedErru,
            $command->getImposed(),
            $startDate,
            $endDate,
            $command->getReasonNotImposed()
        );

        $this->getRepo()->save($penalty);

        $result = new Result();
        $result->addMessage('Applied penalty updated');
        $result->addId('penalty', $penalty->getId());

        return $result;
    }
}
