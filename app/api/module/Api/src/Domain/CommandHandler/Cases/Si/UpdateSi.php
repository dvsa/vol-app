<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategory as SiCategoryEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategoryType as SiCategoryTypeEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement;
use Dvsa\Olcs\Transfer\Command\Cases\Si\UpdateSi as UpdateSiCmd;

/**
 * UpdateSi
 */
final class UpdateSi extends AbstractCommandHandler
{
    protected $repoServiceName = 'SeriousInfringement';

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
         * @var SeriousInfringement $si
         * @var UpdateSiCmd $command
         */
        $si = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        if ($si->getCase()->isErru()) {
            throw new Exception\ValidationException(['This is an ERRU case']);
        }

        $si->update(
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
        $result->addMessage('Serious Infringement updated');

        return $result;
    }
}
