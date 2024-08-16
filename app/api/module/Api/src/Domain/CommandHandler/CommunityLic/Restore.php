<?php

/**
 * Restore community licences
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Licence\UpdateTotalCommunityLicences as UpdateTotalCommunityLicencesCommand;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Restore community licences
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Restore extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'CommunityLic';

    protected $extraRepos = [
        'CommunityLicSuspension',
        'CommunityLicSuspensionReason',
        'CommunityLicWithdrawal',
        'CommunityLicWithdrawalReason',
        'Licence'
    ];

    public function handleCommand(CommandInterface $command)
    {
        $ids = $command->getCommunityLicenceIds();
        $licenceId = $command->getLicence();

        $this->validateLicences($ids, $licenceId);
        $licences = $this->getRepo()->fetchLicencesByIds($ids);

        $result = new Result();
        foreach ($licences as $communityLicence) {
            $id = $communityLicence->getId();
            if ($communityLicence->getSpecifiedDate()) {
                $status = $this->getRepo()->getRefdataReference(CommunityLicEntity::STATUS_ACTIVE);
            } else {
                $status = $this->getRepo()->getRefdataReference(CommunityLicEntity::STATUS_PENDING);
            }
            $communityLicence->changeStatusAndExpiryDate($status, null);
            $this->getRepo()->save($communityLicence);
            $result->addMessage("Community Licence {$id} restored");
            $result->addId('communityLic' . $id, $id);
        }

        $this->deleteSuspensionsAndReasons($ids);
        $this->deleteWithdrawalsAndReasons($ids);

        $updateTotalCommunityLicences =  UpdateTotalCommunityLicencesCommand::create(['id' => $licenceId]);
        $updateResult = $this->handleSideEffect($updateTotalCommunityLicences);
        $result->merge($updateResult);

        return $result;
    }

    protected function validateLicences($ids, $licenceId)
    {
        $licence = $this->getRepo('Licence')->fetchById($licenceId);
        if (!$licence->hasCommunityLicenceOfficeCopy($ids)) {
            $officeCopy = $this->getRepo()->fetchOfficeCopy($licenceId);
            $status = $officeCopy->getStatus()->getId();
            if (
                $status === CommunityLicEntity::STATUS_WITHDRAWN ||
                $status === CommunityLicEntity::STATUS_SUSPENDED
            ) {
                throw new ValidationException(
                    [
                        'communityLicence' => [
                            CommunityLicEntity::ERROR_CANT_RESTORE =>
                                'You cannot restore these licences without restoring the office copy'
                        ]
                    ]
                );
            }
        }
    }

    protected function deleteSuspensionsAndReasons($ids)
    {
        $suspensions = $this->getRepo('CommunityLicSuspension')->fetchByCommunityLicIds($ids);
        $suspensionIds = [];
        foreach ($suspensions as $suspension) {
            $suspensionIds[] = $suspension->getId();
        }
        $suspensionReasons = $this->getRepo('CommunityLicSuspensionReason')->fetchBySuspensionIds($suspensionIds);
        foreach ($suspensionReasons as $suspensionReason) {
            $this->getRepo('CommunityLicSuspensionReason')->delete($suspensionReason);
        }
        foreach ($suspensions as $suspension) {
            $this->getRepo('CommunityLicSuspension')->delete($suspension);
        }
    }

    protected function deleteWithdrawalsAndReasons($ids)
    {
        $withdrawals = $this->getRepo('CommunityLicWithdrawal')->fetchByCommunityLicIds($ids);
        $withdrawalIds = [];
        foreach ($withdrawals as $withdrawal) {
            $withdrawalIds[] = $withdrawal->getId();
        }
        $withdrawalReasons = $this->getRepo('CommunityLicWithdrawalReason')->fetchByWithdrawalIds($withdrawalIds);
        foreach ($withdrawalReasons as $withdrawalReason) {
            $this->getRepo('CommunityLicWithdrawalReason')->delete($withdrawalReason);
        }
        foreach ($withdrawals as $withdrawal) {
            $this->getRepo('CommunityLicWithdrawal')->delete($withdrawal);
        }
    }
}
