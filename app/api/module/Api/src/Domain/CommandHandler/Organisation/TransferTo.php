<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Organisation;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Transfer an Organisation eg Licences/Application to another organisation
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class TransferTo extends AbstractCommandHandler implements TransactionedInterface
{
    public const string ERR_INVALID_ID = 'ERR_INVALID_ID';
    public const string ERR_NO_LICENCES = 'ERR_NO_LICENCES';

    protected $repoServiceName = 'Organisation';
    protected $extraRepos = [
        'Licence',
        'IrfoGvPermit',
        'IrfoPsvAuth',
        'Task',
        'Disqualification',
        'EbsrSubmission',
        'TxcInbox',
        'EventHistory',
        'OrganisationUser',
        'OrganisationPerson',
        'Note'
    ];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $licenceIds = $command->getLicenceIds();

        /* @var Organisation $organisationFrom */
        $organisationFrom = $this->getRepo()->fetchUsingId($command);

        try {
            /* @var Organisation $organisationTo */
            $organisationTo = $this->getRepo()->fetchById($command->getReceivingOrganisation());
        } catch (\Exception) {
            throw new ValidationException(['Target organisation is not found']);
        }

        $existingLicenceCount = $organisationFrom->getLicences()->count();

        $this->validateTransfer($organisationFrom, $organisationTo, $existingLicenceCount, $licenceIds);

        //clear the user caches for both organisations
        $result->merge(
            $this->clearOrganisationCacheSideEffect($organisationFrom->getId())
        );

        $result->merge(
            $this->clearOrganisationCacheSideEffect($organisationTo->getId())
        );

        if (count($licenceIds) === $existingLicenceCount) {
            $result->merge($this->transferLicences($organisationFrom, $organisationTo));
            $result->merge($this->transferIrfo($organisationFrom, $organisationTo));
            $result->merge($this->transferDisqualification($organisationFrom, $organisationTo));
            $result->merge($this->transferEbsr($organisationFrom, $organisationTo));
            $result->merge($this->transferEventHistory($organisationFrom, $organisationTo));
            $result->merge($this->transferUsers($organisationFrom, $organisationTo));
            $result->merge($this->transferPersons($organisationFrom, $organisationTo));

            // delete the from organisation
            $this->getRepo()->delete($organisationFrom);
            $successMessage = 'form.operator-merge.success';
            $result->merge($this->setUnlicencedFlag($organisationTo));
        } else {
            $result->merge($this->transferLicencesByIds($organisationFrom, $organisationTo, $licenceIds));
            $successMessage = 'form.operator-merge.success-alternative';
            $result->merge($this->setUnlicencedFlag($organisationFrom));
            $result->merge($this->setUnlicencedFlag($organisationTo));
        }
        $result->addMessage($successMessage);

        return $result;
    }

    /**
     * Validate transfer
     *
     * @param Organisation $organisationFrom     organisationFrom
     * @param Organisation $organisationTo       organisationTo
     * @param int          $existingLicenceCount existing licence count
     * @param array        $licenceIds           licence ids
     *
     */
    protected function validateTransfer($organisationFrom, $organisationTo, $existingLicenceCount, $licenceIds = [])
    {
        if ($organisationFrom === $organisationTo) {
            throw new ValidationException([self::ERR_INVALID_ID => 'Invalid Operator ID']);
        }
        if ($existingLicenceCount > 0 && count($licenceIds) === 0) {
            throw new ValidationException([self::ERR_NO_LICENCES => 'You must select at least one licence']);
        }
    }

    /**
     * Transfer Licence
     *
     *
     * @return Result
     */
    protected function transferLicences(Organisation $organisationFrom, Organisation $organisationTo)
    {
        $result = new Result();
        $result->addMessage($organisationFrom->getLicences()->count() . ' Licence(s) transferred');
        // get licences for organisationFrom
        /* @var $licence \Dvsa\Olcs\Api\Entity\Licence\Licence */
        foreach ($organisationFrom->getLicences() as $licence) {
            $licence->setOrganisation($organisationTo);
            $this->getRepo('Licence')->save($licence);
        }

        return $result;
    }

    /**
     * Transfer Licence
     *
     * @param Organisation $organisationFrom organisationFrom
     * @param Organisation $organisationTo   organisationTo
     * @param array        $licenceIds       licence ids
     *
     * @return Result
     */
    protected function transferLicencesByIds(Organisation $organisationFrom, Organisation $organisationTo, $licenceIds)
    {
        $result = new Result();
        $existingLicences = $organisationFrom->getLicences();
        $result->addMessage(count($licenceIds) . ' Licence(s) transferred');
        foreach ($existingLicences as $licence) {
            if (in_array($licence->getId(), $licenceIds)) {
                $licence->setOrganisation($organisationTo);
                $this->getRepo('Licence')->save($licence);
                $organisationFrom->getLicences()->removeElement($licence);
                $organisationTo->getLicences()->add($licence);
            }
        }

        return $result;
    }

    /**
     * Set unlicenced flag
     *
     * @param Organisation $organisation organisation
     *
     * @return Result
     */
    protected function setUnlicencedFlag(Organisation $organisation)
    {
        $result = new Result();
        if ($organisation->hasUnlicencedLicences()) {
            $organisation->setIsUnlicensed(true);
        } else {
            $organisation->setIsUnlicensed(false);
        }
        $this->getRepo()->save($organisation);
        $result->addMessage('Unlicenced flags set');

        return $result;
    }

    /**
     * Transfer Note, IrfoGvPermit, IrfoPsvAuth and Task
     *
     *
     * @return Result
     */
    protected function transferIrfo(Organisation $organisationFrom, Organisation $organisationTo)
    {
        $result = new Result();

        //set note.irfo_organisation_id = <winning operator id>
        $notes = $this->getRepo('Note')->fetchByOrganisation($organisationFrom);
        $result->addMessage(count($notes) . ' Note(s) transferred');
        /* @var $note \Dvsa\Olcs\Api\Entity\Note\Note */
        foreach ($notes as $note) {
            $note->setOrganisation($organisationTo);
            $this->getRepo('Note')->save($note);
        }

        //set irfo_gv_permit.organisation_id = <winning operator id>
        $irfoGvPermits = $this->getRepo('IrfoGvPermit')->fetchByOrganisation($organisationFrom);
        $result->addMessage(count($irfoGvPermits) . ' IrfoGvPermit(s) transferred');
        /* @var $irfoGvPermit \Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit */
        foreach ($irfoGvPermits as $irfoGvPermit) {
            $irfoGvPermit->setOrganisation($organisationTo);
            $this->getRepo('IrfoGvPermit')->save($irfoGvPermit);
        }

        //set irfo_psv_auth.organisation_id = <winning operator id>
        $irfoPsvAuths = $this->getRepo('IrfoPsvAuth')->fetchByOrganisation($organisationFrom);
        $result->addMessage(count($irfoPsvAuths) . ' IrfoPsvAuth(s) transferred');
        /* @var $irfoPsvAuth \Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth */
        foreach ($irfoPsvAuths as $irfoPsvAuth) {
            $irfoPsvAuth->setOrganisation($organisationTo);
            $this->getRepo('IrfoPsvAuth')->save($irfoPsvAuth);
        }

        //set task.ifo_organisation_id = <winning operator id>
        $tasks = $this->getRepo('Task')->fetchByIrfoOrganisation($organisationFrom);
        $result->addMessage(count($tasks) . ' Task(s) transferred');
        /* @var $task \Dvsa\Olcs\Api\Entity\Task\Task */
        foreach ($tasks as $task) {
            $task->setIrfoOrganisation($organisationTo);
            $this->getRepo('Task')->save($task);
        }

        $organisationTo->setIsIrfo($organisationFrom->getIsIrfo());

        return $result;
    }

    /**
     * Transfer Disqualification
     *
     *
     * @return Result
     */
    protected function transferDisqualification(Organisation $organisationFrom, Organisation $organisationTo)
    {
        $result = new Result();

        $result->addMessage($organisationFrom->getDisqualifications()->count() . ' Disqualifications(s) transferred');
        /* @var $disqualification \Dvsa\Olcs\Api\Entity\Organisation\Disqualification */
        foreach ($organisationFrom->getDisqualifications() as $disqualification) {
            $disqualification->setOrganisation($organisationTo);
            $this->getRepo('Disqualification')->save($disqualification);
        }

        return $result;
    }

    /**
     * Transfer EbsrSubmission and TxcInbox
     *
     *
     * @return Result
     */
    protected function transferEbsr(Organisation $organisationFrom, Organisation $organisationTo)
    {
        $result = new Result();

        $ebsrSubmissions = $this->getRepo('EbsrSubmission')->fetchByOrganisation($organisationFrom);
        $result->addMessage(count($ebsrSubmissions) . ' EbsrSubmission(s) transferred');
        /* @var $ebsrSubmission \Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission */
        foreach ($ebsrSubmissions as $ebsrSubmission) {
            $ebsrSubmission->setOrganisation($organisationTo);
            $this->getRepo('EbsrSubmission')->save($ebsrSubmission);
        }

        $txcInboxs = $this->getRepo('TxcInbox')->fetchByOrganisation($organisationFrom);
        $result->addMessage(count($txcInboxs) . ' TxcInbox(s) transferred');
        /* @var $txcInbox \Dvsa\Olcs\Api\Entity\Ebsr\TxcInbox */
        foreach ($txcInboxs as $txcInbox) {
            $txcInbox->setOrganisation($organisationTo);
            $this->getRepo('TxcInbox')->save($txcInbox);
        }

        return $result;
    }

    /**
     * Transfer EventHistory
     *
     *
     * @return Result
     */
    protected function transferEventHistory(Organisation $organisationFrom, Organisation $organisationTo)
    {
        $result = new Result();

        $eventHistorys = $this->getRepo('EventHistory')->fetchByOrganisation($organisationFrom);
        $result->addMessage(count($eventHistorys) . ' EventHistory(s) transferred');
        /* @var $eventHistory \Dvsa\Olcs\Api\Entity\EventHistory\EventHistory */
        foreach ($eventHistorys as $eventHistory) {
            $eventHistory->setOrganisation($organisationTo);
            $this->getRepo('EventHistory')->save($eventHistory);
        }

        return $result;
    }

    /**
     * Transfer OrganisationUser
     *
     *
     * @return Result
     */
    protected function transferUsers(Organisation $organisationFrom, Organisation $organisationTo)
    {
        $result = new Result();

        $result->addMessage($organisationFrom->getOrganisationUsers()->count() . ' OrganisationUser(s) transferred');
        /* @var $organisationUser \Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser */
        foreach ($organisationFrom->getOrganisationUsers() as $organisationUser) {
            $organisationUser->setOrganisation($organisationTo);
            $this->getRepo('OrganisationUser')->save($organisationUser);
        }

        return $result;
    }

    /**
     * Transfer OrganisationPersons
     *
     *
     * @return Result
     */
    protected function transferPersons(Organisation $organisationFrom, Organisation $organisationTo)
    {
        $result = new Result();

        $organisationPersons = $organisationFrom->getOrganisationPersons();
        $result->addMessage($organisationPersons->count() . ' OrganisationPersons(s) transferred');
        /* @var $organisationPerson \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson */
        foreach ($organisationPersons as $organisationPerson) {
            $organisationPerson->setOrganisation($organisationTo);
            $this->getRepo('OrganisationPerson')->save($organisationPerson);
        }

        return $result;
    }
}
