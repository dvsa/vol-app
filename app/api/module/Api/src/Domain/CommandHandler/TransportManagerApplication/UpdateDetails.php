<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication;

use Doctrine\ORM\Query;
use Dvsa\Olcs\AcquiredRights\Model\ApplicationReference;
use Dvsa\Olcs\AcquiredRights\Service\AcquiredRightsService;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\OtherLicence as OtherLicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\PreviousConviction as PreviousConvictionRepo;
use Dvsa\Olcs\Api\Domain\Repository\TmEmployment as TmEmploymentRepo;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\UpdateDetails as UpdateDetailsCommand;
use Olcs\Logging\Log\Logger;

/**
 * UpdateDetails
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class UpdateDetails extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TransportManagerApplication';

    protected $extraRepos = [
        'ContactDetails',
        'Address',
        'TmEmployment',
        'TmQualification',
        'OtherLicence',
        'PreviousConviction',
    ];

    public function __construct(protected AcquiredRightsService $acquiredRightsService)
    {
    }

    /**
     * Handle query
     *
     * @param UpdateDetailsCommand $command command
     *
     * @return Result
     * @throws ValidationException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $tma TransportManagerApplication */
        $tma = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $this->updateTma($tma, $command);
        $this->updateHomeAddress($tma, $command->getHomeAddress());
        $this->updateWorkAddress($tma, $command->getWorkAddress());
        $tma->getTransportManager()->getHomeCd()->setEmailAddress($command->getEmail());
        $tma->getTransportManager()->getHomeCd()->getPerson()->setBirthPlace($command->getPlaceOfBirth());

        if ($command->getSubmit() === 'Y') {
            $this->validateDob($command->getDob());
            $this->maybeDeleteAssociatedData($tma, $command);
            // could validate the TMA here?
            $tma->setDeclarationConfirmation('Y');
        }
        $tma->getTransportManager()->getHomeCd()->getPerson()->setBirthDate(
            $command->getDob() ? new DateTime($command->getDob()) : null
        );

        $this->maybeAddLgvAcquiredRightsQualification($tma, $command);

        $this->getRepo()->save($tma);

        return $this->result->addMessage("Transport Manager Application ID {$tma->getId()} updated");
    }

    /**
     * Add LGV Acquired Rights qualification if reference number is provided and this TM doesn't have one already
     *
     *
     * @return void
     */
    private function maybeAddLgvAcquiredRightsQualification(TransportManagerApplication $tma, UpdateDetailsCommand $command): void
    {
        if ($command->getLgvAcquiredRightsReferenceNumber() && !$tma->getTransportManager()->hasLgvAcquiredRightsQualification()) {
            $qualificationType = $tma->getApplication()->isNi()
                ? TmQualification::QUALIFICATION_TYPE_NILGVAR : TmQualification::QUALIFICATION_TYPE_LGVAR;

            $acquiredRightsRecord = $this->validateAcquiredRightsReferenceNumber(
                $command->getLgvAcquiredRightsReferenceNumber(),
                $command->getDob(),
            );

            $tmQualification = TmQualification::create(
                $tma->getTransportManager(),
                $this->getRepo()->getReference(Country::class, Country::ID_UNITED_KINGDOM),
                $this->getRepo()->getRefdataReference($qualificationType),
                $command->getLgvAcquiredRightsReferenceNumber()
            );

            if (!is_null($acquiredRightsRecord) && !is_null($acquiredRightsRecord->getStatusUpdateAt())) {
                $tmQualification->setIssuedDate(\DateTime::createFromImmutable($acquiredRightsRecord->getStatusUpdateAt()));
            } elseif ($this->acquiredRightsService->isCheckEnabled() && !$this->acquiredRightsService->isAcquiredRightsExpired()) {
                Logger::warn(sprintf(
                    'Unable to set Issued Date for TM Qualification for TM (%d) from Acquired Rights record (Ref: %s): record does not contain statusUpdateAt.',
                    $tma->getTransportManager()->getId(),
                    $command->getLgvAcquiredRightsReferenceNumber()
                ));
            }

            $this->getRepo('TmQualification')->save($tmQualification);
        }
    }

    private function maybeDeleteAssociatedData(TransportManagerApplication $tma, UpdateDetailsCommand $command): void
    {
        $this->maybeDeleteOtherLicences($tma, $command);
        $this->maybeDeletePreviousLicences($tma, $command);
        $this->maybeDeleteOtherEmployment($tma, $command);
        $this->maybeDeletePreviousConvictions($tma, $command);
    }

    private function maybeDeleteOtherLicences(TransportManagerApplication $tma, UpdateDetailsCommand $command): void
    {
        if ($command->getHasOtherLicences() === 'N' && $command->getSubmit() === 'Y') {
            /** @var OtherLicenceRepo $repo */
            $repo = $this->getRepo('OtherLicence');
            $otherLicences = $repo->fetchForTransportManagerApplication($tma->getId());
            foreach ($otherLicences as $otherLicence) {
                $repo->delete($otherLicence);
            }
        }
    }

    private function maybeDeletePreviousLicences(TransportManagerApplication $tma, UpdateDetailsCommand $command): void
    {
        if ($command->getHasPreviousLicences() === 'N' && $command->getSubmit() === 'Y') {
            /** @var OtherLicenceRepo $repo */
            $repo = $this->getRepo('OtherLicence');
            $otherLicences = $repo->fetchByTransportManager($tma->getTransportManager()->getId());
            foreach ($otherLicences as $otherLicence) {
                $repo->delete($otherLicence);
            }
        }
    }

    private function maybeDeleteOtherEmployment(TransportManagerApplication $tma, UpdateDetailsCommand $command): void
    {
        if ($command->getHasOtherEmployment() === 'N' && $command->getSubmit() === 'Y') {
            /** @var TmEmploymentRepo $repo */
            $repo = $this->getRepo('TmEmployment');
            $otherEmployments = $repo->fetchByTransportManager($tma->getTransportManager()->getId());
            foreach ($otherEmployments as $otherEmployment) {
                $repo->delete($otherEmployment);
            }
        }
    }

    private function maybeDeletePreviousConvictions(TransportManagerApplication $tma, UpdateDetailsCommand $command): void
    {
        if ($command->getHasConvictions() === 'N' && $command->getSubmit() === 'Y') {
            /** @var PreviousConvictionRepo $repo */
            $repo = $this->getRepo('PreviousConviction');
            $otherEmployments = $repo->fetchByTransportManager($tma->getTransportManager()->getId());
            foreach ($otherEmployments as $otherEmployment) {
                $repo->delete($otherEmployment);
            }
        }
    }

    /**
     * Update TMA properties
     *
     * @param TransportManagerApplication $tma     tma
     * @param UpdateDetailsCommand        $command command
     *
     * @return void
     */
    protected function updateTma(TransportManagerApplication $tma, UpdateDetailsCommand $command)
    {
        if ($command->getTmType()) {
            $tma->setTmType($this->getRepo()->getRefdataReference($command->getTmType()));
        }
        $tma->setAdditionalInformation($command->getAdditionalInfo());
        $tma->setHoursMon((float) $command->getHoursMon());
        $tma->setHoursTue((float) $command->getHoursTue());
        $tma->setHoursWed((float) $command->getHoursWed());
        $tma->setHoursThu((float) $command->getHoursThu());
        $tma->setHoursFri((float) $command->getHoursFri());
        $tma->setHoursSat((float) $command->getHoursSat());
        $tma->setHoursSun((float) $command->getHoursSun());
        if ($command->getIsOwner()) {
            $tma->setIsOwner($command->getIsOwner());
        }
        $tma->setHasUndertakenTraining($command->getHasUndertakenTraining());
        $tma->setHasOtherLicences($this->yesNoToBoolOrNull($command->getHasOtherLicences()));
        $tma->setHasOtherEmployment($this->yesNoToBoolOrNull($command->getHasOtherEmployment()));
        $tma->setHasConvictions($this->yesNoToBoolOrNull($command->getHasConvictions()));
        $tma->setHasPreviousLicences($this->yesNoToBoolOrNull($command->getHasPreviousLicences()));
    }

    /**
     * Update home address
     *
     * @param TransportManagerApplication $tma         tma
     * @param array                       $addressData addressData
     *
     * @return void
     */
    protected function updateHomeAddress(TransportManagerApplication $tma, array $addressData)
    {
        $address = $tma->getTransportManager()->getHomeCd()->getAddress();
        if (!$address instanceof Address) {
            $address = new Address();
        }
        $this->populateAddress($address, $addressData);
        $this->getRepo('Address')->save($address);

        $tma->getTransportManager()->getHomeCd()->setAddress($address);
    }

    /**
     * Update work address
     *
     * @param TransportManagerApplication $tma     tma
     * @param array                       $address address
     *
     * @return void
     */
    protected function updateWorkAddress(TransportManagerApplication $tma, array $address)
    {
        if (!$tma->getTransportManager()->getWorkCd()) {
            $tma->getTransportManager()->setWorkCd(
                new ContactDetails(
                    $this->getRepo()->getRefdataReference(ContactDetails::CONTACT_TYPE_TRANSPORT_MANAGER)
                )
            );
        }
        if (!$tma->getTransportManager()->getWorkCd()->getAddress()) {
            $tma->getTransportManager()->getWorkCd()->setAddress(new Address());
        }

        $this->populateAddress($tma->getTransportManager()->getWorkCd()->getAddress(), $address);

        $this->getRepo('ContactDetails')->save($tma->getTransportManager()->getWorkCd());
    }

    /**
     * Populate an Address entity
     *
     * @param Address $address     address
     * @param array   $addressData addressData
     *
     * @return void
     */
    protected function populateAddress(Address $address, array $addressData)
    {
        $countryCode = null;
        if (!empty($addressData['countryCode'])) {
            $countryCode = $this->getRepo()->getReference(Country::class, $addressData['countryCode']);
        }

        $address->updateAddress(
            $addressData['addressLine1'],
            $addressData['addressLine2'],
            $addressData['addressLine3'],
            $addressData['addressLine4'],
            $addressData['town'],
            $addressData['postcode'],
            $countryCode
        );
    }

    /**
     * Validate dob field
     *
     * @param array $dob date of birth
     *
     * @throws ValidationException
     * @return void
     */
    protected function validateDob($dob)
    {
        if (!$dob) {
            throw new ValidationException(
                [
                    'dob' => [
                        TransportManagerApplication::ERROR_DOB_REQUIRED => 'Date of birth is required'
                    ]
                ]
            );
        }
    }

    /**
     * Ensures the Acquired Rights Reference Number meets the following conditions:
     *  - Matches a valid pattern.
     *  - The reference number is valid and refers to a real acquired rights application.
     *  - The acquired rights application has been approved.
     *
     * @return void
     * @throws ValidationException
     */
    protected function validateAcquiredRightsReferenceNumber(string $acquiredRightsReferenceNumber, string $dateOfBirth): ?ApplicationReference
    {
        if (!$this->acquiredRightsService->isCheckEnabled()) {
            Logger::debug(
                sprintf(
                    'Acquired rights check is disabled; skipping verification of acquired rights reference: %s',
                    $acquiredRightsReferenceNumber
                )
            );
            return null;
        }
        return $this->acquiredRightsService->verifyAcquiredRightsByReference(
            $acquiredRightsReferenceNumber,
            new \DateTimeImmutable($dateOfBirth),
            'lgvAcquiredRightsReferenceNumber'
        );
    }
}
