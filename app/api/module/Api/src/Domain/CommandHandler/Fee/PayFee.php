<?php

/**
 * Pay Fee (handles fee side effects, doesn't actually change fee status)
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Fee;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Application\CloseFeeDueTask as CloseFeeDueTaskCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask as CloseTexTaskCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\EndInterim as EndInterimCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\ValidateApplication;
use Dvsa\Olcs\Api\Domain\Command\Application\InForceInterim;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplication as SubmitIrhpApplication;
use Dvsa\Olcs\Transfer\Command\Permits\AcceptIrhpPermits;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Licence\ContinueLicence as ContinueLicenceCmd;
use Dvsa\Olcs\Transfer\Command\Task\CloseTasks as CloseTasksCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees;

/**
 * Pay Fee (handles fee side effects, doesn't actually change fee status)
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class PayFee extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Fee';
    protected $extraRepos = ['ContinuationDetail'];

    /**
     * Handle command
     *
     * @param PayOutstandingFees $command command
     *
     * @return Result
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $fee = $this->getRepo()->fetchUsingId($command);

        $this->maybeProcessApplicationFee($fee);
        $this->maybeProcessGrantingFee($fee);
        $this->maybeProcessGrantInterimFee($fee);
        $this->maybeContinueLicence($fee);
        $this->maybeCancelApplicationTasks($fee);
        $this->maybeProcessIrhpApplicationFee($fee);
        $this->maybeProcessIrhpIssueFee($fee);
        $this->maybeCloseFeeTask($fee);

        return $this->result;
    }

    /**
     * Close Fee and TEX tasks, when pays the grant fee on a new goods application;
     *
     * @param Fee $fee fee
     *
     * @return void
     */
    protected function maybeCancelApplicationTasks(Fee $fee)
    {
        $application = $fee->getApplication();

        // if New Application and Grant Fee
        if (
            $application &&
            $application->isGoods() &&
            $application->isNew() &&
            $this->isInternalUser() &&
            $fee->getFeeType()->getFeeType()->getId() === FeeType::FEE_TYPE_GRANT
        ) {
            $this->result->merge($this->handleSideEffect(CloseTexTaskCmd::create(['id' => $application->getId()])));
            $this->result->merge($this->handleSideEffect(CloseFeeDueTaskCmd::create(['id' => $application->getId()])));
        }
    }

    /**
     * Process application fee
     *
     * @param Fee $fee fee
     *
     * @throws RuntimeException
     * @return void
     */
    protected function maybeProcessApplicationFee(Fee $fee)
    {
        $application = $fee->getApplication();

        if (
            $application === null
            || $application->isVariation()
            || $application->getStatus()->getId() !== ApplicationEntity::APPLICATION_STATUS_GRANTED
        ) {
            return;
        }

        $outstandingGrantFees = $this->getRepo()->fetchOutstandingGrantFeesByApplicationId($application->getId());

        // if there are any outstanding GRANT fees then don't continue
        if (!empty($outstandingGrantFees)) {
            return;
        }
        $this->result->merge(
            $this->handleSideEffectAsSystemUser(ValidateApplication::create(['id' => $application->getId()]))
        );
    }

    /**
     * Process irhp application fees
     *
     * @param Fee $fee fee
     *
     * @return void
     */
    protected function maybeProcessIrhpApplicationFee(Fee $fee)
    {
        $irhpApplication = $fee->getIrhpApplication();

        if (
            $irhpApplication === null
            || !$fee->getFeeType()->isIrhpApplication()
            || !$irhpApplication->canBeSubmitted()
        ) {
            return;
        }

        // IrhpApplications have 2 fees, and could paid by ICW in any order. Only attempt following if its the last outstanding Fee
        if (!$irhpApplication->hasOutstandingFees()) {
            $this->result->merge(
                $this->handleSideEffectAsSystemUser(SubmitIrhpApplication::create(['id' => $irhpApplication->getId()]))
            );
        }
    }

    /**
     * Process irhp issue fees
     *
     * @param Fee $fee fee
     *
     * @return void
     */
    protected function maybeProcessIrhpIssueFee(Fee $fee)
    {
        $irhpApplication = $fee->getIrhpApplication();

        if ($irhpApplication === null || !$fee->getFeeType()->isIrhpApplicationIssue() || !$irhpApplication->isAwaitingFee()) {
            return;
        }

        $this->result->merge(
            $this->handleSideEffectAsSystemUser(
                AcceptIrhpPermits::create(
                    ['id' => $irhpApplication->getId()]
                )
            )
        );
    }

    /**
     * If all criteria are true then continue the Licence
     *
     * @param Fee $fee fee
     *
     * @return void
     */
    protected function maybeContinueLicence(Fee $fee)
    {
        // Fee type is continuation fee
        if ($fee->getFeeType()->getFeeType()->getId() !== FeeType::FEE_TYPE_CONT) {
            return;
        }

        $licenceId = $fee->getLicence()->getId();

        // there is an ongoing continuation associated to a particular licence and the status is 'Checklist accepted'
        // or the status is not Complete and it has been completed online
        try {
            $this->getRepo('ContinuationDetail')->fetchOngoingForLicence($licenceId);
        } catch (\Doctrine\ORM\UnexpectedResultException) {
            return;
        }

        // the licence status is Valid, Curtailed or Suspended
        $validLicenceStatuses = [
            LicenceEntity::LICENCE_STATUS_VALID,
            LicenceEntity::LICENCE_STATUS_CURTAILED,
            LicenceEntity::LICENCE_STATUS_SUSPENDED,
        ];
        if (!in_array($fee->getLicence()->getStatus()->getId(), $validLicenceStatuses)) {
            return;
        }

        // there are no other outstanding or (waive recommended) continuation fees associated to the licence
        $outstandingFees = $this->getRepo()->fetchOutstandingContinuationFeesByLicenceId($licenceId);
        if (count($outstandingFees) !== 0) {
            return;
        }

        $this->result->merge($this->handleSideEffect(ContinueLicenceCmd::create(['id' => $licenceId])));
        $this->result->setFlag(ContinuationDetailEntity::RESULT_LICENCE_CONTINUED, true);
    }

    /**
     * If the fee type is a GRANT, then check if we do need in-force processing
     *
     * @param Fee $fee fee
     *
     * @return void
     */
    protected function maybeProcessGrantingFee(Fee $fee)
    {
        if ($fee->getFeeType()->getFeeType()->getId() !== FeeType::FEE_TYPE_GRANT) {
            return;
        }

        $application = $fee->getApplication();
        if (
            $application->isGoods() && !$application->isVariation() &&
            $application->getCurrentInterimStatus() === ApplicationEntity::INTERIM_STATUS_INFORCE
        ) {
            $this->result->merge($this->handleSideEffect(EndInterimCmd::create(['id' => $application->getId()])));
        }
    }

    /**
     * If the fee type is a GRANTINT, then check if we do need in-force processing
     *
     * @param Fee $fee fee
     *
     * @return void
     */
    protected function maybeProcessGrantInterimFee(Fee $fee)
    {
        $application = $fee->getApplication();

        if (
            $fee->getFeeType()->getFeeType()->getId() !== FeeType::FEE_TYPE_GRANTINT
            || $application->getCurrentInterimStatus() !== ApplicationEntity::INTERIM_STATUS_GRANTED
        ) {
            return;
        }

        $this->result->merge($this->handleSideEffect(InForceInterim::create(['id' => $application->getId()])));
    }

    /**
     * If the fee has an associated task, close it
     *
     * @param Fee $fee fee
     *
     * @return void
     */
    protected function maybeCloseFeeTask(Fee $fee)
    {
        if ($fee->getTask()) {
            $taskIdsToClose = [$fee->getTask()->getId()];
            $this->result->merge(
                $this->handleSideEffect(CloseTasksCmd::create(['ids' => $taskIdsToClose]))
            );
        }
    }
}
