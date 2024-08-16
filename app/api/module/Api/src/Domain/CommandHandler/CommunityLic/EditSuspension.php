<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspension as CommunityLicSuspensionEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspensionReason as CommunityLicSuspensionReasonEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Transfer\Command\CommunityLic\EditSuspension as Cmd;
use Doctrine\ORM\Query;

/**
 * Edit suspension
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class EditSuspension extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'CommunityLic';

    protected $extraRepos = [
        'CommunityLicSuspension',
        'CommunityLicSuspensionReason'
    ];

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommunityLic\EditSuspension $command command
     *
     * @return Result
     * @throws ValidationException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $status = $command->getStatus();
        $startDate = new \DateTime($command->getStartDate());
        $endDate = $command->getEndDate() ? new \DateTime($command->getEndDate()) : null;
        $today = (new \DateTime('now', new \DateTimeZone('UTC')))->setTime(0, 0, 0);
        $communityLicence = $this->getRepo()->fetchById($command->getCommunityLicenceId());

        if ($status === CommunityLicEntity::STATUS_ACTIVE && $startDate == $today) {
            $communityLicence->setStatus(
                $this->getRepo()->getRefdataReference(CommunityLicEntity::STATUS_SUSPENDED)
            );
            $this->updateSuspensionAndReasons($command, $communityLicence);
            $result->addMessage('The community licence has been suspended');
        }

        if ($status === CommunityLicEntity::STATUS_ACTIVE && $startDate > $today) {
            $this->updateSuspensionAndReasons($command, $communityLicence);
            $result->addMessage('The community licence suspension details have been updated');
        }

        if ($status === CommunityLicEntity::STATUS_SUSPENDED && $endDate == $today) {
            $communityLicence->setStatus(
                $this->getRepo()->getRefdataReference(CommunityLicEntity::STATUS_ACTIVE)
            );
            $this->removeSuspensionAndReasons($command->getId());
            $result->addMessage('The community licence has been restored to active');
        }

        if ($status === CommunityLicEntity::STATUS_SUSPENDED && ($endDate > $today || $endDate === null)) {
            $this->updateSuspensionAndReasons($command, $communityLicence);
            $result->addMessage('The community licence suspension details have been updated');
        }

        $this->getRepo()->save($communityLicence);

        return $result;
    }

    /**
     * Update suspension and reasons
     *
     * @param Cmd $command      command
     * @param CommunityLicEntity $communityLic community licence
     *
     * @return void
     */
    protected function updateSuspensionAndReasons($command, $communityLic)
    {
        $suspension = $this->getRepo('CommunityLicSuspension')
            ->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $suspension->updateCommunityLicSuspension(
            $communityLic,
            $command->getStartDate(),
            $command->getEndDate()
        );
        $this->getRepo('CommunityLicSuspension')->save($suspension);

        $suspensionReasons = $suspension->getCommunityLicSuspensionReasons();
        foreach ($suspensionReasons as $reason) {
            $this->getRepo('CommunityLicSuspensionReason')->delete($reason);
        }

        $newReasons = $command->getReasons();
        foreach ($newReasons as $newReasonType) {
            $newReason = new CommunityLicSuspensionReasonEntity(
                $this->getRepo()->getReference(CommunityLicSuspensionEntity::class, $suspension->getId()),
                $this->getRepo()->getRefdataReference($newReasonType)
            );
            $this->getRepo('CommunityLicSuspensionReason')->save($newReason);
        }
    }

    /**
     * Remove suspension and reasons
     *
     * @param int $id suspension id
     *
     * @return void
     */
    protected function removeSuspensionAndReasons($id)
    {
        $suspension = $this->getRepo('CommunityLicSuspension')->fetchById($id);
        $suspensionReasons = $suspension->getCommunityLicSuspensionReasons();

        foreach ($suspensionReasons as $reason) {
            $this->getRepo('CommunityLicSuspensionReason')->delete($reason);
        }
        $this->getRepo('CommunityLicSuspension')->delete($suspension);
    }
}
