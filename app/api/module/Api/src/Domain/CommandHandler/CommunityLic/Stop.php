<?php

/**
 * Stop community licences
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicWithdrawal as CommunityLicWithdrawalEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspension as CommunityLicSuspensionEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicWithdrawalReason as CommunityLicWithdrawalReasonEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspensionReason as CommunityLicSuspensionReasonEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Stop as Cmd;

/**
 * Stop community licences
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Stop extends AbstractCommandHandler implements TransactionedInterface
{
    public const string STOP_TYPE_WITHDRAWN = 'withdrawal';

    protected $repoServiceName = 'CommunityLic';

    protected $extraRepos = [
        'CommunityLicSuspension',
        'CommunityLicSuspensionReason',
        'CommunityLicWithdrawal',
        'CommunityLicWithdrawalReason',
        'Licence'
    ];

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommunityLic\Stop $command command
     *
     * @return Result
     * @throws ValidationException
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $ids = $command->getCommunityLicenceIds();
        $licenceId = $command->getLicence();
        $type = $command->getType();
        $startDate = $command->getStartDate();
        $endDate = $command->getEndDate();
        $reasons = $command->getReasons();

        $this->validateLicences($command, $licenceId);
        $licences = $this->getRepo()->fetchLicencesByIds($ids);

        $result = new Result();
        foreach ($licences as $communityLicence) {
            $result->merge($this->updateCommunityLicenceStatus($communityLicence, $type, $startDate));

            $id = $communityLicence->getId();
            $result->addId('communityLic' . $id, $id);

            $this->getRepo()->save($communityLicence);
        }
        if ($type == 'withdrawal') {
            $this->createWithrawalAndReasons($licences, $reasons);
        } else {
            $this->createSuspensionAndReasons($licences, $reasons, $startDate, $endDate);
        }

        if ($command->getApplication()) {
            $result->merge(
                $this->handleSideEffect(
                    UpdateApplicationCompletion::create(
                        [
                            'id' => $command->getApplication(),
                            'section' => 'communityLicences'
                        ]
                    )
                )
            );
        }

        return $result;
    }

    /**
     * Update community licence status
     *
     * @param CommunityLicEntity $communityLicence community licence
     * @param string       $type             type
     * @param string       $startDate        start date
     *
     * @return mixed
     * @throws RuntimeException
     */
    protected function updateCommunityLicenceStatus($communityLicence, $type, $startDate)
    {
        $result = new Result();
        $id = $communityLicence->getId();
        if ($type === self::STOP_TYPE_WITHDRAWN) {
            $communityLicence->setStatus(
                $this->getRepo()->getRefdataReference(CommunityLicEntity::STATUS_WITHDRAWN)
            );
            $result->addMessage("The licence {$id} have been withdrawn");
            return $result;
        }

        $today = (new DateTime())->setTime(0, 0, 0)->format('Y-m-d');
        $startDate = (new DateTime($startDate))->format('Y-m-d');

        if ($startDate === $today) {
            $communityLicence->setStatus(
                $this->getRepo()->getRefdataReference(CommunityLicEntity::STATUS_SUSPENDED)
            );
            $result->addMessage("The licence {$id} have been suspended");
        } else {
            $result->addMessage("The licence {$id} due to suspend");
        }
        return $result;
    }

    /**
     * Create withdrawal and reasons
     *
     * @param array $communityLics community licences
     * @param array $reasons       reasons
     *
     * @throws RuntimeException
     * @return void
     */
    protected function createWithrawalAndReasons($communityLics, $reasons)
    {
        foreach ($communityLics as $licence) {
            $withdrawal = new CommunityLicWithdrawalEntity();
            $withdrawal->updateCommunityLicWithdrawal($licence);
            $this->getRepo('CommunityLicWithdrawal')->save($withdrawal);
            foreach ($reasons as $reason) {
                $withdrawalReason = new CommunityLicWithdrawalReasonEntity(
                    $this->getRepo()->getReference(CommunityLicWithdrawalEntity::class, $withdrawal->getId()),
                    $this->getRepo()->getRefdataReference($reason)
                );
                $this->getRepo('CommunityLicWithdrawalReason')->save($withdrawalReason);
            }
        }
    }

    /**
     * Create suspension and reasons
     *
     * @param array  $communityLics community licences
     * @param array  $reasons       reasons
     * @param string $startDate     start date
     * @param string $endDate       end date
     *
     * @throws RuntimeException
     * @return void
     */
    protected function createSuspensionAndReasons($communityLics, $reasons, $startDate, $endDate)
    {
        foreach ($communityLics as $licence) {
            $suspension = new CommunityLicSuspensionEntity();
            $suspension->updateCommunityLicSuspension($licence, $startDate, $endDate);
            $this->getRepo('CommunityLicSuspension')->save($suspension);
            foreach ($reasons as $reason) {
                $suspensionReason = new CommunityLicSuspensionReasonEntity(
                    $this->getRepo()->getReference(CommunityLicSuspensionEntity::class, $suspension->getId()),
                    $this->getRepo()->getRefdataReference($reason)
                );
                $this->getRepo('CommunityLicSuspensionReason')->save($suspensionReason);
            }
        }
    }

    /**
     * Validate licences
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommunityLic\Stop $command   command
     * @param int                                           $licenceId licence id
     *
     * @throws ValidationException
     * @throws RuntimeException
     * @return void
     */
    protected function validateLicences($command, $licenceId)
    {
        $messages = [];
        $ids = $command->getCommunityLicenceIds();
        $licence = $this->getRepo('Licence')->fetchById($licenceId);
        if ($licence->hasCommunityLicenceOfficeCopy($ids)) {
            $validLicences = $this->getRepo()->fetchValidLicences($licenceId);
            foreach ($validLicences as $communityLicence) {
                $status = $communityLicence->getStatus()->getId();
                if (
                        ($status == CommunityLicEntity::STATUS_PENDING) ||
                        (
                           $status == CommunityLicEntity::STATUS_ACTIVE &&
                           !in_array($communityLicence->getId(), $ids)
                        )
                ) {
                        $messages['communityLicence'][CommunityLicEntity::ERROR_CANT_STOP] =
                            'Please annul, withdraw or suspend the other pending/active ' .
                            'licences before the office copy';
                }
            }
        }
        $startDate = $command->getStartDate();
        $endDate = $command->getEndDate();
        if (!$startDate && $command->getType() === 'suspension') {
            $messages['communityLicence'][CommunityLicEntity::ERROR_START_DATE_EMPTY] =
                'Start date can not be empty';
        }
        if ($endDate) {
            $startDate = new DateTime($startDate);
            $endDate = new DateTime($endDate);
            if ($endDate <= $startDate) {
                $messages['communityLicence'][CommunityLicEntity::ERROR_END_DATE_WRONG] =
                    'End date must be after start date';
            }
        }
        if (count($messages)) {
            throw new ValidationException($messages);
        }
    }
}
