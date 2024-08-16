<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SiEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategory as SiCategoryEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategoryType as SiCategoryTypeEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * CreateSi
 */
final class CreateSi extends AbstractCommandHandler
{
    protected $repoServiceName = 'SeriousInfringement';

    /**
     * Create Si
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $case = $this->getRepo()->getReference(CaseEntity::class, $command->getCase());

        if ($case->isErru()) {
            throw new Exception\ValidationException(['This is an ERRU case']);
        }

        $si = new SiEntity(
            $case,
            new \DateTime($command->getCheckDate()),
            new \DateTime($command->getInfringementDate()),
            $this->getRepo()->getReference(SiCategoryEntity::class, SiCategoryEntity::ERRU_DEFAULT_CATEGORY),
            $this->getRepo()->getReference(SiCategoryTypeEntity::class, $command->getSiCategoryType())
        );

        $si->setMemberStateCode(
            $this->getRepo()->getReference(CountryEntity::class, $command->getMemberStateCode())
        );

        if ($command->getNotificationNumber() !== null) {
            $si->setNotificationNumber($command->getNotificationNumber());
        }

        if ($command->getReason() !== null) {
            $si->setReason($command->getReason());
        }

        $this->getRepo()->save($si);

        $result = new Result();
        $result->addId('si', $si->getId());
        $result->addMessage('Serious Infringement created');

        return $result;
    }
}
