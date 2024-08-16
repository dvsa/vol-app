<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\ContinuationDetail\GenerateChecklistDocument as GenerateChecklistDocumentCommand;

/**
 * Process ContinuationDetail
 */
final class Process extends AbstractCommandHandler implements TransactionedInterface, EmailAwareInterface
{
    use EmailAwareTrait;

    protected $repoServiceName = 'ContinuationDetail';

    protected $extraRepos = ['Document', 'FeeType', 'Fee', 'SystemParameter'];

    /**
     * Handle command
     *
     * @param CommandInterface $command Command DTO
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $continuationDetail = $this->getRepo()->fetchUsingId($command);

        if ($continuationDetail->getStatus()->getId() !== ContinuationDetailEntity::STATUS_PRINTING) {
            $this->result
                ->addId('continuationDetail', $continuationDetail->getId())
                ->addMessage('Continuation detail no longer pending');
            return $this->result;
        }

        if ($this->useDigitalContinuation($continuationDetail)) {
            $this->processDigital($continuationDetail);
        } else {
            $this->processNonDigital($continuationDetail, $command->getUser());
        }

        $this->result
            ->addId('continuationDetail', $continuationDetail->getId())
            ->addMessage('ContinuationDetail updated');

        return $this->result;
    }

    /**
     * Decide whether to use the Digital continuation process
     *
     * @param ContinuationDetailEntity $continuationDetail Continuation detail
     *
     * @return bool
     */
    private function useDigitalContinuation(ContinuationDetailEntity $continuationDetail)
    {
        /** @var SystemParameter $systemParameterRepo */
        $systemParameterRepo = $this->getRepo('SystemParameter');
        // if Digital continuations are disabled, return false
        if ($systemParameterRepo->getDisabledDigitalContinuations()) {
            return false;
        }

        // if sending preference is not Email, return false
        if ($continuationDetail->getLicence()->getOrganisation()->getAllowEmail() !== 'Y') {
            return false;
        }

        // if there are no admin email addresses, return false
        if (empty($continuationDetail->getLicence()->getOrganisation()->getAdminEmailAddresses())) {
            return false;
        }

        return true;
    }

    /**
     * Process a non digital continuation (the paper based method)
     *
     * @param ContinuationDetailEntity $continuationDetail Continuation detail
     * @param int                      $userId             User who initiated the process
     *
     * @return void
     */
    private function processNonDigital(ContinuationDetailEntity $continuationDetail, $userId)
    {
        // 1. Generate the checklist document
        $this->result->merge(
            $this->handleSideEffect(
                GenerateChecklistDocumentCommand::create(
                    [
                        'id' => $continuationDetail->getId(),
                        'user' => $userId
                    ]
                )
            )
        );

        // 2. Update continuation detail record with the checklist document
        // reference and 'printed' status
        $document = $this->getRepo('Document')->fetchById($this->result->getId('document'));
        $status = $this->getRepo()->getRefdataReference(ContinuationDetailEntity::STATUS_PRINTED);
        $continuationDetail
            ->setChecklistDocument($document)
            ->setStatus($status);
        $this->getRepo()->save($continuationDetail);

        // 3. Create the continuation fee, if applicable
        $this->createFee($continuationDetail);
    }

    /**
     * Process a digital continuation (email based method)
     *
     * @param ContinuationDetailEntity $continuationDetail Continuation detail
     *
     * @return void
     */
    private function processDigital(ContinuationDetailEntity $continuationDetail)
    {
        // 1. Create the continuation fee, if applicable
        $feeAmount = $this->createFee($continuationDetail);

        // 2. Generate and send an email
        $this->sendDigitalContinuationEmail($continuationDetail, $feeAmount);
        $continuationDetail->setDigitalNotificationSent(true);

        // 3. Update continuation detail record with 'printed' status
        $status = $this->getRepo()->getRefdataReference(ContinuationDetailEntity::STATUS_PRINTED);
        $continuationDetail->setStatus($status);

        $this->getRepo()->save($continuationDetail);
    }

    /**
     * Send an email to operator admins, with link to the digital continuation
     *
     * @param ContinuationDetailEntity $continuationDetail Continuation detail
     * @param float|int                $feeAmount          The amount of the continuation fee
     *
     * @return void
     */
    private function sendDigitalContinuationEmail(ContinuationDetailEntity $continuationDetail, $feeAmount)
    {
        $emailAddresses = $continuationDetail->getLicence()->getOrganisation()->getAdminEmailAddresses();

        foreach ($emailAddresses as $emailAddress) {
            $expiryDate = $continuationDetail->getLicence()->getExpiryDate(true)->format('j F Y');

            $message = new \Dvsa\Olcs\Email\Data\Message($emailAddress, 'email.digital-continuation.subject');
            $message->setSubjectVariables([$continuationDetail->getLicence()->getLicNo(), $expiryDate]);
            $message->setTranslateToWelsh($continuationDetail->getLicence()->getTranslateToWelsh());

            $this->sendEmailTemplate(
                $message,
                'digital-continuation',
                [
                    'licNo' => $continuationDetail->getLicence()->getLicNo(),
                    'continuationDate' => $expiryDate,
                    'isGoods' => $continuationDetail->getLicence()->isGoods(),
                    'isPsv' => $continuationDetail->getLicence()->isPsv(),
                    'isSpecialRestricted' => $continuationDetail->getLicence()->isSpecialRestricted(),
                    'feeAmount' => $feeAmount,
                    'continueLicenceUrl' => sprintf('http://selfserve/continuation/%d', $continuationDetail->getId()),
                ]
            );
        }
    }

    /**
     * Create the fee for the continuation, if there isn't one already
     *
     * @param ContinuationDetailEntity $continuationDetail Continuation Detail
     *
     * @return int|float The amount on the fee
     */
    protected function createFee(ContinuationDetailEntity $continuationDetail)
    {
        $licence = $continuationDetail->getLicence();
        $amount = 0;

        if ($this->shouldCreateFee($licence)) {
            $now = new DateTime();

            $feeType = $this->getRepo('FeeType')->fetchLatest(
                $this->getRepo()->getRefdataReference(FeeTypeEntity::FEE_TYPE_CONT),
                $licence->getGoodsOrPsv(),
                $licence->getLicenceType(),
                $now,
                $licence->getTrafficArea()
            );

            $amount = ($feeType->getFixedValue() != 0 ? $feeType->getFixedValue() : $feeType->getFiveYearValue());

            $data = [
                'feeType' => $feeType->getId(),
                'amount' => $amount,
                'invoicedDate' => $now->format('Y-m-d'),
                'licence' => $licence->getId(),
                'description' => $feeType->getDescription() . ' for licence ' . $licence->getLicNo(),
            ];

            $result = $this->handleSideEffect(CreateFee::create($data));

            $this->result->merge($result);
        }

        return $amount;
    }

    /**
     * We want to create a fee if the licence type is goods, or psv special restricted
     * and there is no existing CONT fee
     *
     * @param LicenceEntity $licence Licence
     *
     * @return boolean
     */
    protected function shouldCreateFee(LicenceEntity $licence)
    {
        // If PSV and not SR then we don't need to create a fee
        if ($licence->isPsv() && !$licence->isSpecialRestricted()) {
            return false;
        }

        // check for fees less than three months old
        $after = (new DateTime('now'))->sub(new \DateInterval('P3M'));
        $results = $this->getRepo('Fee')->fetchOutstandingContinuationFeesByLicenceId(
            $licence->getId(),
            $after,
            true
        );

        return empty($results);
    }
}
