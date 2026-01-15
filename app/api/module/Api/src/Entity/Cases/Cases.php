<?php

namespace Dvsa\Olcs\Api\Entity\Cases;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Note\Note;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\CloseableInterface;
use Dvsa\Olcs\Api\Entity\ReopenableInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Service\Document\ContextProviderInterface;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SeriousInfringementEntity;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * Cases Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="cases",
 *    indexes={
 *        @ORM\Index(name="ix_cases_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_cases_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_cases_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_cases_transport_manager_id", columns={"transport_manager_id"}),
 *        @ORM\Index(name="ix_cases_case_type", columns={"case_type"}),
 *        @ORM\Index(name="ix_cases_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_cases_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
class Cases extends AbstractCases implements
    CloseableInterface,
    ReopenableInterface,
    ContextProviderInterface,
    OrganisationProviderInterface
{
    public const LICENCE_CASE_TYPE = 'case_t_lic';
    public const IMPOUNDING_CASE_TYPE = 'case_t_imp';
    public const APP_CASE_TYPE = 'case_t_app';
    public const TM_CASE_TYPE = 'case_t_tm';
    public const ERRU_DEFAULT_CASE_CATEGORY = 'case_cat_compl_erru_msi';

    /**
     * Creates a new case entity and sets the open date
     *
     * @param \DateTime $openDate
     * @param RefData $caseType
     * @param ArrayCollection $categorys
     * @param ArrayCollection $outcomes
     * @param Application|null $application
     * @param Licence|null $licence
     * @param TransportManager|null $transportManager
     * @param string $ecmsNo
     * @param string $description
     */
    public function __construct(
        \DateTime $openDate,
        RefData $caseType,
        ArrayCollection $categorys,
        ArrayCollection $outcomes,
        $application,
        $licence,
        $transportManager,
        $ecmsNo,
        $description
    ) {
        parent::__construct();

        $this->create(
            $openDate,
            $caseType,
            $categorys,
            $outcomes,
            $application,
            $licence,
            $transportManager,
            $ecmsNo,
            $description
        );
    }

    /**
     * Creates a new case entity and sets the open date
     *
     * @param Application|null $application
     * @param Licence|null $licence
     * @param TransportManager|null $transportManager
     * @param string $ecmsNo
     * @param string $description
     *
     * @throws ForbiddenException
     */
    public function create(
        \DateTime $openDate,
        RefData $caseType,
        ArrayCollection $categorys,
        ArrayCollection $outcomes,
        $application,
        $licence,
        $transportManager,
        $ecmsNo,
        $description
    ) {
        //if we have an application, make sure a case is allowed to be created, and also override the passed licence
        //variable to ensure we always have the correct licence for the application
        if ($application instanceof Application) {
            if (!$application->canCreateCase()) {
                throw new ForbiddenException('Cases can\'t be created for this application');
            }

            $licence = $application->getLicence();
        }

        $this->setOpenDate($openDate);
        $this->setCaseType($caseType);
        $this->setCategorys($categorys);
        $this->setOutcomes($outcomes);
        $this->setApplication($application);
        $this->setLicence($licence);
        $this->setTransportManager($transportManager);
        $this->setEcmsNo($ecmsNo);
        $this->setDescription($description);
    }

    /**
     * @param RefData $caseType
     * @param ArrayCollection $categorys
     * @param ArrayCollection $outcomes
     * @param string $ecmsNo
     * @param string $description
     * @return bool
     */
    public function update(
        $caseType,
        $categorys,
        $outcomes,
        $ecmsNo,
        $description
    ) {
        $caseTypeModifyAllowed = [self::IMPOUNDING_CASE_TYPE, self::LICENCE_CASE_TYPE];

        //if case type is allowed to be modified, make sure it's modified to something allowable
        if (in_array($this->caseType, $caseTypeModifyAllowed) && in_array($caseType, $caseTypeModifyAllowed)) {
            $this->setCaseType($caseType);
        }

        $this->setCaseType($caseType);
        $this->setCategorys($categorys);
        $this->setOutcomes($outcomes);
        $this->setEcmsNo($ecmsNo);
        $this->setDescription($description);

        return true;
    }

    /**
     * Updates annual test history
     *
     * @param string $annualTestHistory
     * @return bool
     */
    public function updateAnnualTestHistory($annualTestHistory)
    {
        $this->setAnnualTestHistory($annualTestHistory);

        return true;
    }

    /**
     * Updates conviction note
     *
     * @param string $convictionNote
     * @return bool
     */
    public function updateConvictionNote($convictionNote)
    {
        $this->setConvictionNote($convictionNote);

        return true;
    }

    /**
     * Updates prohibition note
     *
     * @param string $prohibitionNote
     * @return bool
     */
    public function updateProhibitionNote($prohibitionNote)
    {
        $this->setProhibitionNote($prohibitionNote);

        return true;
    }

    /**
     * Checks a stay type exists
     * @return bool
     */
    public function hasStayType(RefData $stayType)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("stayType", $stayType))
            ->setFirstResult(0)
            ->setMaxResults(1);

        $stays = $this->getStays()->matching($criteria);

        return !($stays->isEmpty());
    }

    /**
     * Checks whether an appeal exists
     * @return bool
     */
    public function hasAppeal()
    {
        return !(empty($this->getAppeal()));
    }

    /**
     * @return boolean
     */
    public function isOpen()
    {
        return (
            is_null($this->getClosedDate())
            && is_null($this->getDeletedDate())
        );
    }

    /**
     * @return boolean
     */
    public function hasComplaints()
    {
        return !$this->getComplaints()->isEmpty();
    }

    /**
     * @return boolean
     */
    public function isTm()
    {
        return $this->getTransportManager() !== null;
    }

    public function getComplianceComplaints()
    {
        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('isCompliance', 1));

        return $this->getComplaints()->matching($criteria);
    }

    public function getEnvironmentalComplaints()
    {
        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('isCompliance', 0));

        return $this->getComplaints()->matching($criteria);
    }

    /**
     * Close the case
     */
    public function close()
    {
        $errors = $this->generateCloseableValidationErrors();

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $this->closedDate = new \DateTime();
    }

    /**
     * Generate backend validation errors. Checks the following:
     * 1 Can close (is not already closed)
     * 2 Case has no outcome
     * 3 Has no outstanding appeals or stays
     *
     * @return array
     */
    private function generateCloseableValidationErrors()
    {
        $errors = [];
        if (!$this->canClose()) {
            $errors[] = 'close-case.validation.error.cannot-be-closed';
        }
        if ($this->getOutcomes()->isEmpty()) {
            $errors[] = 'close-case.validation.error.requires-outcome';
        }
        if (!empty($this->getAppeal()) && !$this->getAppeal()->isOutstanding()) {
            $errors[] = 'close-case.validation.error.outstanding-appeal';
        }
        $stays = $this->getStays();
        if (!empty($stays)) {
            /** @var Stay $stay */
            foreach ($stays as $stay) {
                if (!$stay->isOutstanding()) {
                    $errors[] = 'close-case.validation.error.outstanding-stay';
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * Reopen the case
     */
    public function reopen()
    {
        if (!$this->canReopen()) {
            throw new ForbiddenException('Case is not allowed to be reopened');
        }

        $this->closedDate = null;
    }

    /**
     * Can the case be closed?
     *
     * @return bool
     */
    public function canClose()
    {
        if ($this->getOutcomes()->isEmpty()) {
            return false;
        }

        return !$this->isClosed();
    }

    /**
     * Is the case closed?
     *
     * return bool
     */
    public function isClosed()
    {
        return (bool) $this->closedDate != null;
    }

    /**
     * Can the Case be reopened?
     *
     * @return bool
     */
    public function canReopen()
    {
        return $this->isClosed();
    }

    /**
     * Returns whether the case is an Erru case
     *
     * @return bool
     */
    public function isErru()
    {
        return (bool) $this->erruRequest != null;
    }

    /**
     * Returns whether an Erru Msi response can be sent
     * NOTE: only cases with requested penalties are responded to
     */
    public function canSendMsiResponse(): bool
    {
        //check this is an erru case, and if so that the response isn't already sent
        if (!$this->isOpenErruCase()) {
            return false;
        }

        /** @var SeriousInfringementEntity $si */
        foreach ($this->seriousInfringements as $si) {
            //each serious infringement must have a response to all requested penalties
            if (!$si->responseSet()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether the case has at least one serious infringement with requested penalties
     * This determines whether any response is set when the caseworker clicks the send button
     */
    public function hasErruRequestedPenalties(): bool
    {
        /** @var SeriousInfringementEntity $si */
        foreach ($this->seriousInfringements as $si) {
            if ($si->hasRequestedPenalties()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether a serious infringement can be added to the case
     *
     * @return bool
     */
    public function canAddSi()
    {
        return $this->isOpenErruCase();
    }

    /**
     * Returns whether the case is both open and erru with no response set
     *
     * @return bool
     */
    public function isOpenErruCase()
    {
        return !$this->isClosed() && $this->isErru() && $this->erruRequest->canModify();
    }

    /**
     * Calculated values to be added to a bundle
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        return [
            'isClosed' => $this->isClosed(),
            'canReopen' => $this->canReopen(),
            'canClose' => $this->canClose(),
            'canSendMsiResponse' => $this->canSendMsiResponse(),
            'canAddSi' => $this->canAddSi(),
            'isErru' => $this->isErru(),
            'hasErruRequestedPenalties' => $this->hasErruRequestedPenalties(),
        ];
    }

    /**
     * Return Conditions and Undertakings that are added via Case. Used in submissions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConditionUndertakingsAddedViaCase()
    {
        return $this->getConditionUndertakings()->filter(
            fn($element) => ($element->getDeletedDate() === null)
                && in_array(
                    $element->getAddedVia(),
                    [
                        ConditionUndertaking::ADDED_VIA_CASE,
                    ]
                )
        );
    }

    public function getContextValue()
    {
        return $this->getId();
    }

    /**
     * Return the Note type for this case, based on case type
     *
     * @return string
     */
    public function getNoteType()
    {
        switch ($this->getCaseType()) {
            case self::LICENCE_CASE_TYPE:
                return Note::NOTE_TYPE_LICENCE;
            case self::APP_CASE_TYPE:
                return Note::NOTE_TYPE_APPLICATION;
            case self::TM_CASE_TYPE:
                return Note::NOTE_TYPE_TRANSPORT_MANAGER;
            default:
                return Note::NOTE_TYPE_CASE;
        }
    }

    /**
     * Get organisations this entity is linked to
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation|\Dvsa\Olcs\Api\Entity\Organisation\Organisation[]|null
     */
    public function getRelatedOrganisation()
    {
        if ($this->getApplication()) {
            return $this->getApplication()->getRelatedOrganisation();
        }

        if ($this->getLicence()) {
            return $this->getLicence()->getRelatedOrganisation();
        }

        if ($this->getTransportManager()) {
            return $this->getTransportManager()->getRelatedOrganisation();
        }

        return null;
    }
}
