<?php

namespace Dvsa\Olcs\Api\Entity\Permits;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\CancelableInterface;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApggAppSubmitted;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgAppSubmitted;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtAutomaticallyWithdrawn;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApggIssued;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgIssued;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgSuccessful;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgUnsuccessful;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgPartSuccessful;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermApggIssued;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermApsgIssued;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermAutomaticallyWithdrawn;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermSuccessful;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermUnsuccessful;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermApsgPartSuccessful;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermAppSubmitted;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPath;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Entity\Generic\Question;
use Dvsa\Olcs\Api\Entity\Generic\QuestionText;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\Sectors;
use Dvsa\Olcs\Api\Entity\LicenceProviderInterface;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;
use Dvsa\Olcs\Api\Entity\SectionableInterface;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Entity\Traits\FetchPermitAppSubmissionTaskTrait;
use Dvsa\Olcs\Api\Entity\Traits\SectionTrait;
use Dvsa\Olcs\Api\Entity\Traits\PermitAppReviveFromUnsuccessfulTrait;
use Dvsa\Olcs\Api\Entity\Traits\PermitAppReviveFromWithdrawnTrait;
use Dvsa\Olcs\Api\Entity\Permits\Traits\ApplicationAcceptConsts;
use Dvsa\Olcs\Api\Entity\Permits\Traits\CandidatePermitCreationTrait;
use Dvsa\Olcs\Api\Entity\Traits\TieredProductReference;
use Dvsa\Olcs\Api\Entity\WithdrawableInterface;
use Dvsa\Olcs\Api\Service\Document\ContextProviderInterface;
use Dvsa\Olcs\Api\Service\Permits\Checkable\CheckableApplicationInterface;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use RuntimeException;

/**
 * IrhpApplication Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="irhp_application",
 *    indexes={
 *        @ORM\Index(name="ix_irhp_application_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_irhp_application_source", columns={"source"}),
 *        @ORM\Index(name="ix_irhp_application_status", columns={"status"}),
 *        @ORM\Index(name="ix_irhp_application_irhp_permit_type_id", columns={"irhp_permit_type_id"}),
 *        @ORM\Index(name="ix_irhp_application_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_irhp_application_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class IrhpApplication extends AbstractIrhpApplication implements
    IrhpInterface,
    OrganisationProviderInterface,
    LicenceProviderInterface,
    SectionableInterface,
    CancelableInterface,
    WithdrawableInterface,
    ContextProviderInterface,
    CheckableApplicationInterface,
    QaEntityInterface
{
    use SectionTrait;
    use CandidatePermitCreationTrait;
    use FetchPermitAppSubmissionTaskTrait;
    use TieredProductReference;

    use PermitAppReviveFromWithdrawnTrait {
        canBeRevivedFromWithdrawn as baseCanBeRevivedFromWithdrawn;
    }

    use PermitAppReviveFromUnsuccessfulTrait {
        canBeRevivedFromUnsuccessful as baseCanBeRevivedFromUnsuccessful;
    }

    public const NON_SCALAR_ANSWER_PRESENT = 'Answer is present but has non-scalar representation';

    public const ERR_CANT_CANCEL = 'Unable to cancel this application';
    public const ERR_CANT_TERMINATE = 'Unable to terminate this application';
    public const ERR_CANT_CHECK_ANSWERS = 'Unable to check answers: the sections of the application have not been completed.';
    public const ERR_CANT_MAKE_DECLARATION = 'Unable to make declaration: the sections of the application have not been completed.';
    public const ERR_CANT_SUBMIT = 'This application cannot be submitted';
    public const ERR_CANT_ISSUE = 'This application cannot be issued';
    public const ERR_CANT_GRANT = 'Unable to grant this application';

    public const ERR_ROADWORTHINESS_ONLY = 'This method is only for roadworthiness certificates';
    public const ERR_ROADWORTHINESS_MOT_EXPIRY = 'The MOT has not yet expired on this record';

    public const COUNTRY_PROPERTY_CODE = 'code';
    public const COUNTRY_PROPERTY_NAME = 'name';
    public const COUNTRY_PROPERTY_STATUS = 'status';
    public const COUNTRY_PROPERTY_IPA_ID = 'irhpPermitApplication';

    public const SECTIONS = [
        IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL => [
            'countries' => [
                'validator' => 'areBilateralCountriesCompleted',
            ],
            'declaration' => [
                'validator' => 'fieldIsAgreed',
                'validateIf' => [
                    'countries' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                ],
            ],
            'submitAndPay' => [
                'validator' => SectionableInterface::VALIDATOR_ALWAYS_TRUE,
                'validateIf' => [
                    'declaration' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                ],
            ],
        ],
        IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL => [
            'permitsRequired' => [
                'validator' => 'permitsRequiredPopulated',
            ],
            'checkedAnswers' => [
                'validator' => 'fieldIsAgreed',
                'validateIf' => [
                    'permitsRequired' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                ],
            ],
            'declaration' => [
                'validator' => 'fieldIsAgreed',
                'validateIf' => [
                    'checkedAnswers' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                ],
            ],
        ],
    ];

    public const ISSUE_FEE_PRODUCT_REFERENCE_MONTH_ARRAY = [
        'Jan' => FeeTypeEntity::FEE_TYPE_ECMT_ISSUE_100_PRODUCT_REF,
        'Feb' => FeeTypeEntity::FEE_TYPE_ECMT_ISSUE_100_PRODUCT_REF,
        'Mar' => FeeTypeEntity::FEE_TYPE_ECMT_ISSUE_100_PRODUCT_REF,
        'Apr' => FeeTypeEntity::FEE_TYPE_ECMT_ISSUE_75_PRODUCT_REF,
        'May' => FeeTypeEntity::FEE_TYPE_ECMT_ISSUE_75_PRODUCT_REF,
        'Jun' => FeeTypeEntity::FEE_TYPE_ECMT_ISSUE_75_PRODUCT_REF,
        'Jul' => FeeTypeEntity::FEE_TYPE_ECMT_ISSUE_50_PRODUCT_REF,
        'Aug' => FeeTypeEntity::FEE_TYPE_ECMT_ISSUE_50_PRODUCT_REF,
        'Sep' => FeeTypeEntity::FEE_TYPE_ECMT_ISSUE_50_PRODUCT_REF,
        'Oct' => FeeTypeEntity::FEE_TYPE_ECMT_ISSUE_25_PRODUCT_REF,
        'Nov' => FeeTypeEntity::FEE_TYPE_ECMT_ISSUE_25_PRODUCT_REF,
        'Dec' => FeeTypeEntity::FEE_TYPE_ECMT_ISSUE_25_PRODUCT_REF,
    ];

    /**
     * Prepares data and calls TieredProductReference Trait method genericGetProdRefForTier
     *
     * @return string
     */
    public function getProductReferenceForTier(DateTime $now = null)
    {
        $now = is_null($now) ? new DateTime() : $now;
        $irhpPermitApplication = $this->getFirstIrhpPermitApplication();
        $irhpPermitStock = $irhpPermitApplication->getIrhpPermitWindow()->getIrhpPermitStock();
        return $this->genericGetProdRefForTier(
            $irhpPermitStock->getValidFrom(true),
            $irhpPermitStock->getValidTo(true),
            $now,
            self::ISSUE_FEE_PRODUCT_REFERENCE_MONTH_ARRAY
        );
    }

    /** @var array */
    private $storedFeesRequired;

    /**
     * This is a custom validator for the permitsRequired field
     *
     * @param string $field field being checked
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function permitsRequiredPopulated($field)
    {
        if ($this->getIrhpPermitApplications()->isEmpty()) {
            return false;
        }

        /** @var IrhpPermitApplication $irhpPermitApplication */
        foreach ($this->getIrhpPermitApplications() as $irhpPermitApplication) {
            if (!$irhpPermitApplication->hasPermitsRequired()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets calculated values
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        return [
            'applicationRef' => $this->getApplicationRef(),
            'canBeCancelled' => $this->canBeCancelled(),
            'canBeTerminated' => $this->canBeTerminated(),
            'canBeWithdrawn' => $this->canBeWithdrawn(),
            'canBeGranted' => $this->canBeGranted(),
            'canBeDeclined' => $this->canBeDeclined(),
            'canBeSubmitted' => $this->canBeSubmitted(),
            'canBeResetToNotYetSubmittedFromValid' => $this->canBeResetToNotYetSubmittedFromValid(),
            'canBeResetToNotYetSubmittedFromCancelled' => $this->canBeResetToNotYetSubmittedFromCancelled(),
            'canBeRevivedFromWithdrawn' => $this->canBeRevivedFromWithdrawn(),
            'canBeRevivedFromUnsuccessful' => $this->canBeRevivedFromUnsuccessful(),
            'hasOutstandingFees' => $this->hasOutstandingFees(),
            'outstandingFeeAmount' => $this->getOutstandingFeeAmount(),
            'sectionCompletion' => $this->getSectionCompletion(),
            'hasCheckedAnswers' => $this->hasCheckedAnswers(),
            'hasMadeDeclaration' => $this->hasMadeDeclaration(),
            'isNotYetSubmitted' => $this->isNotYetSubmitted(),
            'isOverviewAccessible' => $this->isOverviewAccessible(),
            'isSubmittedForConsideration' => $this->isSubmittedForConsideration(),
            'isValid' => $this->isValid(),
            'isFeePaid' => $this->isFeePaid(),
            'isIssueInProgress' => $this->isIssueInProgress(),
            'isAwaitingFee' => $this->isAwaitingFee(),
            'isUnderConsideration' => $this->isUnderConsideration(),
            'isReadyForNoOfPermits' => $this->isReadyForNoOfPermits(),
            'isCancelled' => $this->isCancelled(),
            'isWithdrawn' => $this->isWithdrawn(),
            'isDeclined' => $this->isDeclined(),
            'isBilateral' => $this->isBilateral(),
            'isMultilateral' => $this->isMultilateral(),
            'canCheckAnswers' => $this->canCheckAnswers(),
            'canMakeDeclaration' => $this->canMakeDeclaration(),
            'permitsRequired' => $this->getPermitsRequired(),
            'canUpdateCountries' => $this->canUpdateCountries(),
            'questionAnswerData' => $this->getQuestionAnswerData(),
            'businessProcess' => $this->getBusinessProcess(),
            'requiresPreAllocationCheck' => $this->requiresPreAllocationCheck(),
        ];
    }

    /**
     * Get question and answer data
     *
     * @return array
     */
    public function getQuestionAnswerData(): array
    {
        if ($this->isBilateral()) {
            return $this->getBilateralQuestionAnswerData();
        }

        if ($this->isMultilateral()) {
            return [];
        }

        $previousQuestionStatus = SectionableInterface::SECTION_COMPLETION_COMPLETED;

        // the Q&A solution
        $activeApplicationPath = $this->getActiveApplicationPath();

        if (isset($activeApplicationPath)) {
            /**
             * list of defined steps
             *
             * @var ApplicationStep $applicationStep
             */
            foreach ($activeApplicationPath->getApplicationSteps() as $applicationStep) {
                $question = $applicationStep->getQuestion();
                $answer = null;
                $status = SectionableInterface::SECTION_COMPLETION_CANNOT_START;

                if ($previousQuestionStatus === SectionableInterface::SECTION_COMPLETION_COMPLETED) {
                    $answer = $this->getAnswer($applicationStep);

                    $status = isset($answer)
                        ? SectionableInterface::SECTION_COMPLETION_COMPLETED
                        : SectionableInterface::SECTION_COMPLETION_NOT_STARTED;
                }

                $activeQuestionText = $question->getActiveQuestionText($this->getApplicationPathLockedOn());

                $questionKey = $activeQuestionText->getTranslationKeyFromQuestionKey();
                $slug = $question->getSlug();

                $data[$slug] = [
                    'section' => $slug,
                    'slug' => $slug,
                    'questionShort' => $activeQuestionText->getQuestionShortKey(),
                    'question' => $questionKey,
                    'questionType' => $activeQuestionText->getQuestion()->getQuestionType()->getId(),
                    'answer' => $answer,
                    'status' => $status,
                ];
                $previousQuestionStatus = $status;
            }
        }

        // checked answers
        $answer = null;
        $status = SectionableInterface::SECTION_COMPLETION_CANNOT_START;

        if ($previousQuestionStatus === SectionableInterface::SECTION_COMPLETION_COMPLETED) {
            $answer = $this->getCheckedAnswers();
            $status = !empty($answer)
                ? SectionableInterface::SECTION_COMPLETION_COMPLETED
                : SectionableInterface::SECTION_COMPLETION_NOT_STARTED;
        }

        $data['custom-check-answers'] = [
            'section' => 'checkedAnswers',
            'slug' => 'custom-check-answers',
            'questionShort' => 'section.name.application-check-answers',
            'question' => 'section.name.application-check-answers',
            'answer' => $answer,
            'status' => $status,
        ];
        $previousQuestionStatus = $status;

        // declaration
        $answer = null;
        $status = SectionableInterface::SECTION_COMPLETION_CANNOT_START;

        if ($previousQuestionStatus === SectionableInterface::SECTION_COMPLETION_COMPLETED) {
            $answer = $this->getDeclaration();
            $status = !empty($answer)
                ? SectionableInterface::SECTION_COMPLETION_COMPLETED
                : SectionableInterface::SECTION_COMPLETION_NOT_STARTED;
        }

        $data['custom-declaration'] = [
            'section' => 'declaration',
            'slug' => 'custom-declaration',
            'questionShort' => 'section.name.application-declaration',
            'question' => 'section.name.application-declaration',
            'answer' => $answer,
            'status' => $status,
        ];

        return $data;
    }

    /**
     * Get question and answer data for bilateral
     *
     * @return array
     */
    private function getBilateralQuestionAnswerData()
    {
        return [
            'countries' => $this->getBilateralCountriesAndStatuses(),
            'reviewAndSubmit' => $this->getSectionCompletion(),
        ];
    }

    /**
     * Get an answer to the given application step
     *
     * @return mixed|null
     */
    public function getAnswer(ApplicationStep $applicationStep)
    {
        $question = $applicationStep->getQuestion();
        $applicationPathLockedOn = $this->getApplicationPathLockedOn();

        if ($question->isCustom()) {
            $formControlType = $question->getFormControlType();
            switch ($formControlType) {
                case Question::FORM_CONTROL_ECMT_REMOVAL_NO_OF_PERMITS:
                    return $this->getEcmtRemovalNoOfPermitsAnswer();
                case Question::FORM_CONTROL_ECMT_NO_OF_PERMITS_EITHER:
                case Question::FORM_CONTROL_ECMT_NO_OF_PERMITS_BOTH:
                    return $this->getEcmtNoOfPermitsAnswer();
                case Question::FORM_CONTROL_ECMT_INTERNATIONAL_JOURNEYS:
                    return $this->getInternationalJourneysAnswer();
                case Question::FORM_CONTROL_ECMT_SECTORS:
                    return $this->getEcmtSectorsAnswer();
                case Question::FORM_CONTROL_ECMT_ANNUAL_2018_NO_OF_PERMITS:
                    return $this->getEcmtAnnual2018NoOfPermitsAnswer();
                case Question::FORM_CONTROL_ECMT_RESTRICTED_COUNTRIES:
                case Question::FORM_CONTROL_ECMT_REMOVAL_PERMIT_START_DATE:
                case Question::FORM_CONTROL_ECMT_ANNUAL_TRIPS_ABROAD:
                case Question::FORM_CONTROL_ECMT_SHORT_TERM_EARLIEST_PERMIT_DATE:
                case Question::FORM_CONTROL_CERT_ROADWORTHINESS_MOT_EXPIRY_DATE:
                case Question::FORM_CONTROL_COMMON_CERTIFICATES:
                    return $question->getStandardAnswer($this, $applicationPathLockedOn);
            }

            throw new RuntimeException(
                sprintf(
                    'Unable to retrieve answer status for form control type %s',
                    $formControlType
                )
            );
        }

        return $question->getStandardAnswer($this, $applicationPathLockedOn);
    }

    /**
     * Get the number of permits answer value for a custom element of type ecmt removal
     *
     * @return int|null
     */
    private function getEcmtRemovalNoOfPermitsAnswer()
    {
        return $this->getFirstIrhpPermitApplication()->countPermitsRequired();
    }

    /**
     * Get the number of permits answer values for a custom element of type ecmt short term
     *
     * @return string|null
     */
    private function getEcmtNoOfPermitsAnswer()
    {
        $irhpPermitApplication = $this->getFirstIrhpPermitApplication();

        $requiredEuro5 = $irhpPermitApplication->getRequiredEuro5();
        $requiredEuro6 = $irhpPermitApplication->getRequiredEuro6();

        if (is_null($requiredEuro5) || is_null($requiredEuro6)) {
            return null;
        }

        return self::NON_SCALAR_ANSWER_PRESENT;
    }

    /**
     * Get the international journeys answer value
     *
     * @return int|null
     */
    private function getInternationalJourneysAnswer()
    {
        if (!is_null($this->internationalJourneys)) {
            return $this->internationalJourneys->getId();
        }

        return null;
    }

    /**
     * Get the sectors answer value
     *
     * @return int|null
     */
    private function getEcmtSectorsAnswer()
    {
        if (!is_null($this->sectors)) {
            return $this->sectors->getId();
        }

        return null;
    }

    /**
     * Get the number of permits answer value for a custom element of type ecmt annual 2018
     *
     * @return int|null
     */
    private function getEcmtAnnual2018NoOfPermitsAnswer()
    {
        $irhpPermitApplication = $this->getFirstIrhpPermitApplication();

        $requiredEuro5 = $irhpPermitApplication->getRequiredEuro5();
        if ($requiredEuro5) {
            return $requiredEuro5;
        }

        $requiredEuro6 = $irhpPermitApplication->getRequiredEuro6();
        if ($requiredEuro6) {
            return $requiredEuro6;
        }

        return null;
    }

    /**
     * Get the application reference
     *
     * @return string
     */
    public function getApplicationRef()
    {
        return sprintf('%s / %d', $this->licence->getLicNo(), $this->id);
    }

    /**
     * Get the organisation
     *
     * @return Organisation
     */
    public function getRelatedOrganisation()
    {
        return $this->getLicence()->getOrganisation();
    }

    /**
     * Get the related licence
     *
     * @return Licence
     */
    public function getRelatedLicence(): Licence
    {
        return $this->licence;
    }

    /**
     * Whether the application is for a bilateral permit type
     *
     * @return bool
     */
    public function isBilateral(): bool
    {
        return $this->irhpPermitType->isBilateral();
    }

    /**
     * Whether the application is for a multilateral permit type
     *
     * @return bool
     */
    public function isMultilateral(): bool
    {
        return $this->irhpPermitType->isMultilateral();
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->status->getId() === IrhpInterface::STATUS_VALID;
    }

    /**
     * @return bool
     */
    public function isNotYetSubmitted()
    {
        return $this->status->getId() === IrhpInterface::STATUS_NOT_YET_SUBMITTED;
    }

    /**
     * @return bool
     */
    public function isOverviewAccessible()
    {
        if (!$this->isNotYetSubmitted()) {
            return false;
        }

        if ($this->isBilateral()) {
            return count($this->countrys) > 0;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isUnderConsideration()
    {
        return $this->status->getId() === IrhpInterface::STATUS_UNDER_CONSIDERATION;
    }

    /**
     * @return bool
     */
    public function isAwaitingFee()
    {
        return $this->status->getId() === IrhpInterface::STATUS_AWAITING_FEE;
    }

    /**
     * @return bool
     */
    public function isFeePaid()
    {
        return $this->status->getId() === IrhpInterface::STATUS_FEE_PAID;
    }

    /**
     * Gets fees over a certain number of days old
     *
     * @param int $days fees invoiced over a certain number of days ago
     *
     * @return ArrayCollection
     */
    public function getFeesByAge(int $days): ArrayCollection
    {
        $cutoff = new \DateTime('-' . $days . ' weekdays');

        $criteria = Criteria::create();
        $criteria->andWhere(Criteria::expr()->lte('invoicedDate', $cutoff));
        $criteria->orderBy(['invoicedDate' => Criteria::DESC]);

        return $this->getFees()->matching($criteria);
    }

    /**
     * Is there an overdue issue fee for this application?
     *
     * @param int $days
     *
     * @return bool
     */
    public function issueFeeOverdue($days)
    {
        $matchedFees = $this->getFeesByAge($days);

        /**
         * @var Fee $fee
         */
        foreach ($matchedFees as $fee) {
            if ($fee->isOutstanding() && $fee->getFeeType()->isIrhpApplicationIssue()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets IrhpPermitApplications ordered by country name
     *
     * @return ArrayCollection
     * @throws ForbiddenException
     */
    public function getIrhpPermitApplicationsByCountryName(): ArrayCollection
    {
        if (!$this->isBilateral()) {
            throw new ForbiddenException(
                'Cannot get IrhpPermitApplications by country name for irhpPermitType ' . $this->getIrhpPermitType()->getId()
            );
        }

        $iterator = $this->getIrhpPermitApplications()->getIterator();

        $iterator->uasort(function ($a, $b) {
            $countryNameA = $a->getIrhpPermitWindow()
                ->getIrhpPermitStock()
                ->getCountry()
                ->getCountryDesc();

            $countryNameB = $b->getIrhpPermitWindow()
                ->getIrhpPermitStock()
                ->getCountry()
                ->getCountryDesc();
            return $countryNameA <=> $countryNameB;
        });

        return new ArrayCollection(iterator_to_array($iterator));
    }

    /**
     * Return the associated irhp permit application associated with a stock having the specified country id, or null
     * if no such irhp permit application exists
     *
     * @param string $countryId
     *
     * @return IrhpPermitApplication|null
     */
    public function getIrhpPermitApplicationByCountryId($countryId)
    {
        foreach ($this->irhpPermitApplications as $irhpPermitApplication) {
            $irhpPermitApplicationCountryId = $irhpPermitApplication->getIrhpPermitWindow()
                ->getIrhpPermitStock()
                ->getCountry()
                ->getId();

            if ($countryId == $irhpPermitApplicationCountryId) {
                return $irhpPermitApplication;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isIssueInProgress()
    {
        return $this->status->getId() === IrhpInterface::STATUS_ISSUING;
    }

    /**
     * @return bool
     */
    public function isSubmittedForConsideration()
    {
        $irhpPermitType = $this->getIrhpPermitType();

        return ($irhpPermitType->isEcmtShortTerm() || $irhpPermitType->isEcmtAnnual())
            && $this->isUnderConsideration();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return in_array($this->status->getId(), IrhpInterface::ACTIVE_STATUSES);
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return $this->status->getId() === IrhpInterface::STATUS_CANCELLED;
    }

    /**
     * @return bool
     */
    public function isWithdrawn(): bool
    {
        return $this->status->getId() === IrhpInterface::STATUS_WITHDRAWN;
    }

    /**
     * @return bool
     */
    public function isDeclined(): bool
    {
        return $this->isWithdrawn()
            && $this->withdrawReason->getId() === WithdrawableInterface::WITHDRAWN_REASON_DECLINED;
    }

    /**
     * Cancel an application
     *
     * @param RefData $refData cancellation status
     *
     * @return void
     */
    public function cancel(RefData $cancelStatus)
    {
        if (!$this->canBeCancelled()) {
            throw new ForbiddenException(self::ERR_CANT_CANCEL);
        }

        $this->status = $cancelStatus;
        $this->cancellationDate = new \DateTime();
    }

    /**
     * Whether the permit application can be cancelled
     *
     * @return bool
     */
    public function canBeCancelled()
    {
        return $this->isNotYetSubmitted();
    }

    /**
     * Terminate an application
     *
     *
     * @return void
     */
    public function terminate(RefData $terminateStatus)
    {
        if (!$this->canBeTerminated()) {
            throw new ForbiddenException(self::ERR_CANT_TERMINATE);
        }

        $this->status = $terminateStatus;
        $this->expiryDate = new \DateTime();
    }

    /**
     * Whether the permit application can be terminated
     *
     * @return bool
     */
    public function canBeTerminated()
    {
        return $this->isValidCertificateOfRoadworthiness();
    }

    /**
     * Whether this is a certificate of roadworthiness application in valid status
     *
     * @return bool
     */
    public function isValidCertificateOfRoadworthiness()
    {
        return $this->isValid() && $this->isCertificateOfRoadworthiness();
    }

    /**
     * Whether the permit application can be withdrawn optional withdraw reason
     *
     * @param RefData $reason reason application is being withdrawn
     *
     * @return bool
     */
    public function canBeWithdrawn(?RefData $reason = null): bool
    {
        if ($reason instanceof RefData && $reason->getId() === WithdrawableInterface::WITHDRAWN_REASON_DECLINED) {
            return $this->canBeDeclined();
        }

        return $this->isUnderConsideration() || $this->isAwaitingFee();
    }

    /**
     * Whether the permit application can be granted
     *
     * @return bool
     */
    public function canBeGranted(): bool
    {
        return
            $this->isUnderConsideration()
            && $this->licence->isValid()
            && (string)$this->getBusinessProcess() === RefData::BUSINESS_PROCESS_APGG;
    }

    /**
     * Whether the permit application can be declined
     *
     * @return bool
     */
    public function canBeDeclined(): bool
    {
        return $this->isAwaitingFee();
    }

    /**
     * Have the answers been checked
     *
     * @return bool
     */
    public function hasCheckedAnswers()
    {
        if ($this->isBilateral()) {
            return false;
        }

        return $this->fieldIsAgreed('checkedAnswers');
    }

    /**
     * Whether countries can be updated
     *
     * @return bool
     */
    public function canUpdateCountries()
    {
        return $this->canBeUpdated()
            && $this->getIrhpPermitType()->getId() === IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL
            && $this->isFieldReadyToComplete('countries');
    }

    /**
     * Update checkedAnswers to true
     *
     * @throws ForbiddenException
     */
    public function updateCheckAnswers()
    {
        if ($this->isBilateral()) {
            return;
        }

        if (!$this->canCheckAnswers()) {
            throw new ForbiddenException(self::ERR_CANT_CHECK_ANSWERS);
        }
        return $this->checkedAnswers = true;
    }

    /**
     * Whether checkedAnswers can be updated
     *
     * @return bool
     */
    public function canCheckAnswers()
    {
        if ($this->isBilateral()) {
            return false;
        }

        return $this->canBeUpdated() && $this->isFieldReadyToComplete('checkedAnswers');
    }

    /**
     * Have the answers been checked
     *
     * @return bool
     */
    public function hasMadeDeclaration()
    {
        return $this->fieldIsAgreed('declaration');
    }

    /**
     * Whether the application can be submitted
     *
     * @return bool
     */
    public function canBeSubmitted()
    {
        if (!$this->isNotYetSubmitted() || !$this->licence->isEligibleForPermits()) {
            return false;
        }

        $sections = $this->getSectionCompletion();

        return $sections['allCompleted'];
    }

    /**
     * Whether the application has any outstanding fees
     *
     * @return bool
     */
    public function hasOutstandingFees()
    {
        $fee = $this->getLatestOutstandingFeeByTypes(
            [
                FeeTypeEntity::FEE_TYPE_IRHP_APP,
                FeeTypeEntity::FEE_TYPE_IRHP_ISSUE,
                FeeTypeEntity::FEE_TYPE_IRFOGVPERMIT,
            ]
        );

        return $fee !== null;
    }

    /**
     * Return the latest application fee, or none if no application fee is present
     *
     * @return Fee|null
     */
    public function getLatestOutstandingApplicationFee()
    {
        return $this->getLatestOutstandingFeeByTypes([FeeTypeEntity::FEE_TYPE_IRHP_APP]);
    }

    /**
     * Get a list of outstanding application fees relating to this application
     *
     * @return array
     */
    public function getOutstandingApplicationFees()
    {
        return $this->getOutstandingFeesByType(FeeTypeEntity::FEE_TYPE_IRHP_APP);
    }

    /**
     * Get a list of outstanding issue fees relating to this application
     *
     * @return array
     */
    public function getOutstandingIssueFees()
    {
        return $this->getOutstandingFeesByType(FeeTypeEntity::FEE_TYPE_IRHP_ISSUE);
    }

    /**
     * Get a list of outstanding irfo permit fees relating to this application
     *
     * @return array
     */
    public function getOutstandingIrfoPermitFees()
    {
        return $this->getOutstandingFeesByType(FeeTypeEntity::FEE_TYPE_IRFOGVPERMIT);
    }

    /**
     * Get outstanding fees by types
     *
     * @param int $feeTypeId
     *
     * @return array
     */
    private function getOutstandingFeesByType($feeTypeId)
    {
        $fees = [];
        foreach ($this->getFees() as $fee) {
            if ($fee->isOutstanding() && $fee->getFeeType()->getFeeType()->getId() == $feeTypeId) {
                $fees[] = $fee;
            }
        }

        return $fees;
    }

    /**
     * Return the latest issue fee, or none if no issue fee is present
     *
     * @return FeeEntity|null
     */
    public function getLatestIssueFee()
    {
        $criteria = Criteria::create()
            ->orderBy(['invoicedDate' => Criteria::DESC]);

        /** @var FeeEntity $fee */
        foreach ($this->getFees()->matching($criteria) as $fee) {
            if ($fee->getFeeType()->getFeeType()->getId() == FeeTypeEntity::FEE_TYPE_IRHP_ISSUE) {
                return $fee;
            }
        }

        return null;
    }

    /**
     * Return the latest outstanding issue fee, or none if no issue fee is present
     *
     * @return FeeEntity|null
     */
    public function getLatestOutstandingIssueFee()
    {
        return $this->getLatestOutstandingFeeByTypes([FeeTypeEntity::FEE_TYPE_IRHP_ISSUE]);
    }

    /**
     * Get latest outstanding fee by types
     *
     * @param array $feeTypeIds
     *
     * @return FeeEntity|null
     */
    private function getLatestOutstandingFeeByTypes($feeTypeIds)
    {
        $criteria = Criteria::create()
            ->orderBy(['invoicedDate' => Criteria::DESC]);

        /** @var FeeEntity $fee */
        foreach ($this->getFees()->matching($criteria) as $fee) {
            if ($fee->isOutstanding() && in_array($fee->getFeeType()->getFeeType()->getId(), $feeTypeIds)) {
                return $fee;
            }
        }
        return null;
    }

    /**
     * Return the amount of the outstanding fees (application + issue)
     *
     * @return float|int
     */
    public function getOutstandingFeeAmount()
    {
        $feeAmount = 0;
        $outstandingFees = $this->getOutstandingFees();

        /** @var FeeEntity $fee */
        foreach ($outstandingFees as $fee) {
            $feeAmount += $fee->getGrossAmount();
        }

        return $feeAmount;
    }

    /**
     * Get All Outstanding IRHP Application Fees
     *
     * @return array
     */
    public function getOutstandingFees(): array
    {
        $feeTypeIds = [
            FeeTypeEntity::FEE_TYPE_IRHP_APP,
            FeeTypeEntity::FEE_TYPE_IRHP_ISSUE,
            FeeTypeEntity::FEE_TYPE_IRFOGVPERMIT,
        ];
        $fees = [];

        /** @var FeeEntity $fee */
        foreach ($this->getFees() as $fee) {
            if ($fee->isOutstanding() && in_array($fee->getFeeType()->getFeeType()->getId(), $feeTypeIds)) {
                $fees[] = $fee;
            }
        }
        return $fees;
    }

    /**
     * Returns true if the application is in a state where the number of permits can be specified against each
     * relevant stock (i.e. one or more instances of IrhpPermitApplication have already been created against this
     * IrhpApplication)
     *
     * @return bool
     */
    public function isReadyForNoOfPermits()
    {
        $canBeUpdated = $this->canBeUpdated();
        $hasIrhpPermitApplications = count($this->irhpPermitApplications) > 0;

        return $canBeUpdated && $hasIrhpPermitApplications;
    }

    /**
     * Whether the application can be updated
     *
     * @return bool
     */
    public function canBeUpdated()
    {
        return $this->isNotYetSubmitted()
            || $this->isUnderConsideration()
            || ($this->isValid() && $this->isCertificateOfRoadworthiness());
    }

    /**
     * Reset the checked answers and declaration sections to a value representing 'not completed'
     */
    public function resetCheckAnswersAndDeclaration()
    {
        if ($this->canBeUpdated()) {
            $this->declaration = false;
            $this->checkedAnswers = false;
        }
    }

    /**
     * @param RefData $irhpPermitType
     * @param RefData $licence
     * @param string|null $dateReceived
     * @return IrhpApplication
     */
    public static function createNew(
        RefData $source,
        RefData $status,
        IrhpPermitType $irhpPermitType,
        Licence $licence,
        string $dateReceived = null
    ) {
        $irhpApplication = new self();
        $irhpApplication->source = $source;
        $irhpApplication->status = $status;
        $irhpApplication->irhpPermitType = $irhpPermitType;
        $irhpApplication->licence = $licence;
        $irhpApplication->dateReceived = static::processDate($dateReceived);
        return $irhpApplication;
    }

    /**
     * Mark Declaration field as true
     *
     * @return bool
     * @throws ForbiddenException
     */
    public function makeDeclaration()
    {
        if (!$this->canMakeDeclaration()) {
            throw new ForbiddenException(self::ERR_CANT_MAKE_DECLARATION);
        }
        return $this->declaration = true;
    }

    /**
     * Whether declaration can be be updated
     *
     * @return bool
     */
    public function canMakeDeclaration()
    {
        return $this->canBeUpdated() && $this->isFieldReadyToComplete('declaration');
    }

    /**
     * Submit Application - Placeholder method to allow Declaration Page redirects to work for testing.
     *
     * @throws ForbiddenException
     */
    public function submit(RefData $submitStatus)
    {
        if (!$this->canBeSubmitted()) {
            throw new ForbiddenException(self::ERR_CANT_SUBMIT);
        }

        return match ($this->getIrhpPermitType()->getId()) {
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT, IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM => $this->proceedToUnderConsideration($submitStatus),
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL, IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL => $this->proceedToIssuing($submitStatus),
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER => $this->proceedToValid($submitStatus),
            default => throw new ForbiddenException(self::ERR_CANT_SUBMIT),
        };
    }

    /**
     * Grant Application -
     *
     * @throws ForbiddenException
     */
    public function grant(RefData $grantStatus)
    {
        if (!$this->canBeGranted()) {
            throw new ForbiddenException(self::ERR_CANT_GRANT);
        }

        $this->status = $grantStatus;
    }

    /**
     * Withdraw an application
     *
     * @param RefData $withdrawStatus
     * @param RefData $withdrawReason
     * @param bool $checkReasonAgainstStatus
     *
     * @throws ValidationException
     *
     * @return void
     */
    public function withdraw(RefData $withdrawStatus, RefData $withdrawReason, $checkReasonAgainstStatus): void
    {
        // when withdrawing an application from internal, we need to allow any withdrawal reason to be specified
        // regardless of the application status. in this situation, we pass a false value for
        // checkReasonAgainstStatus, which in turn passes a null reason into canBeWithdrawn and should therefore
        // run the most generalised form of the validation check applicable to all withdrawal reasons

        $validationWithdrawReason = null;
        if ($checkReasonAgainstStatus) {
            $validationWithdrawReason = $withdrawReason;
        }

        if (!$this->canBeWithdrawn($validationWithdrawReason)) {
            $error = ($withdrawReason->getId() === WithdrawableInterface::WITHDRAWN_REASON_DECLINED)
                ? WithdrawableInterface::ERR_CANT_DECLINE : WithdrawableInterface::ERR_CANT_WITHDRAW;

            throw new ValidationException([$error]);
        }

        $this->status = $withdrawStatus;
        $this->withdrawReason = $withdrawReason;
        $this->withdrawnDate = new DateTime();
    }

    /**
     * Gets the total number of Permits Required for the IRHP Permit Applications
     *
     * @return integer
     */
    public function getPermitsRequired()
    {
        $applications = $this->irhpPermitApplications;
        $total = 0;

        /** @var IrhpPermitApplication $app */
        foreach ($applications as $app) {
            $permitsRequired = $app->countPermitsRequired();
            $total += is_null($permitsRequired) ? 0 : $permitsRequired;
        }

        return $total;
    }

    /**
     * Calculates and stores the total number of permits required by this application. Intended to be used in
     * conjunction with hasPermitsRequiredChanged
     */
    public function storeFeesRequired()
    {
        $this->storedFeesRequired = $this->getSerialisedFeeProductRefsAndQuantities();
    }

    /**
     * Whether the total permits required has changed since the last call to storePermitsRequired. Can be used
     * to determine whether the issue fee needs to be regenerated
     *
     * @return bool
     */
    public function haveFeesRequiredChanged()
    {
        if (is_null($this->storedFeesRequired)) {
            throw new RuntimeException('storeFeesRequired must be called before haveFeesRequiredChanged');
        }

        return $this->getSerialisedFeeProductRefsAndQuantities() != $this->storedFeesRequired;
    }

    /**
     * Return a json encoded representation of all applicable fee product references and associated quantities, sorted
     * by product reference
     *
     * @return string
     */
    private function getSerialisedFeeProductRefsAndQuantities()
    {
        $productRefsAndQuantities = array_merge(
            $this->getApplicationFeeProductRefsAndQuantities(),
            $this->getIssueFeeProductRefsAndQuantities()
        );

        ksort($productRefsAndQuantities);
        return json_encode($productRefsAndQuantities);
    }

    /**
     * Gets the application fee product reference for this application
     * Applicable only to ecmt annual, bilateral, multilateral and ecmt short term and ecmt removal
     *
     * @return array
     *
     * @throws ForbiddenException if the permit type is unsupported
     */
    public function getApplicationFeeProductReference()
    {
        $mappings = [
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT
                => FeeTypeEntity::FEE_TYPE_ECMT_APP_PRODUCT_REF,
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL
                => FeeTypeEntity::FEE_TYPE_IRHP_APP_BILATERAL_PRODUCT_REF,
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL
                => FeeTypeEntity::FEE_TYPE_IRHP_APP_MULTILATERAL_PRODUCT_REF,
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM
                => FeeTypeEntity::FEE_TYPE_ECMT_APP_PRODUCT_REF,
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL
                => FeeTypeEntity::FEE_TYPE_ECMT_REMOVAL_ISSUE_PRODUCT_REF,
        ];

        $irhpPermitTypeId = $this->getIrhpPermitType()->getId();
        if (!isset($mappings[$irhpPermitTypeId])) {
            throw new ForbiddenException(
                'No application fee product reference available for permit type ' . $irhpPermitTypeId
            );
        }

        return $mappings[$irhpPermitTypeId];
    }

    /**
     * Gets a key/value pair array representing application fee product references and quantities for this application
     *
     * @return array
     */
    public function getApplicationFeeProductRefsAndQuantities()
    {
        return [
            $this->getApplicationFeeProductReference() => $this->getPermitsRequired()
        ];
    }

    /**
     * Gets the issue fee product reference for this application
     *
     * @return string
     *
     * @throws ForbiddenException if the permit type is unsupported
     */
    public function getIssueFeeProductReference()
    {
        $irhpPermitType = $this->irhpPermitType;

        if ($irhpPermitType->isEcmtShortTerm()) {
            return FeeTypeEntity::FEE_TYPE_ECMT_SHORT_TERM_ISSUE_PRODUCT_REF;
        } elseif ($irhpPermitType->isEcmtAnnual()) {
            return $this->getProductReferenceForTier();
        }

        throw new ForbiddenException(
            'No issue fee product reference available for permit type ' . $irhpPermitType->getId()
        );
    }

    /**
     * Gets the total fee per permit including application fee and issue fee
     * Applicable only to bilateral, multilateral, removals and ecmt short term
     *
     * @param FeeTypeEntity $applicationFeeType (optional)
     * @param FeeTypeEntity $issueFeeType (optional)
     *
     * @return int
     *
     * @throws ForbiddenException if the permit type is unsupported
     */
    public function getFeePerPermit(?FeeTypeEntity $applicationFeeType = null, ?FeeTypeEntity $issueFeeType = null)
    {
        $permittedPermitTypeIds = [
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL,
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
        ];

        $irhpPermitTypeId = $this->getIrhpPermitType()->getId();

        if (!in_array($irhpPermitTypeId, $permittedPermitTypeIds)) {
            throw new ForbiddenException(
                'Cannot get fee per permit for irhp permit type ' . $irhpPermitTypeId
            );
        }

        if ($irhpPermitTypeId == IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL) {
            return $issueFeeType->getFixedValue();
        }

        return $applicationFeeType->getFixedValue() + $issueFeeType->getFixedValue();
    }

    /**
     * Gets a key/value pair array representing issue fee product references and quantities for this application
     *
     * @return array
     *
     * @throws ForbiddenException if the permit type is unsupported
     */
    public function getIssueFeeProductRefsAndQuantities()
    {
        $cumulativeProductRefsAndQuantities = [];

        $irhpPermitApplications = $this->getIrhpPermitApplications();
        foreach ($irhpPermitApplications as $irhpPermitApplication) {
            $productRef = $irhpPermitApplication->getIssueFeeProductReference();
            $quantity = $irhpPermitApplication->countPermitsRequired();

            if ($quantity > 0) {
                if (!isset($cumulativeProductRefsAndQuantities[$productRef])) {
                    $cumulativeProductRefsAndQuantities[$productRef] = 0;
                }
                $cumulativeProductRefsAndQuantities[$productRef] += $quantity;
            }
        }

        return $cumulativeProductRefsAndQuantities;
    }

    /**
     * Whether the application fee can be created or replaced
     *
     * @return bool
     */
    public function canCreateOrReplaceApplicationFee()
    {
        return $this->isNotYetSubmitted();
    }

    /**
     * Whether the issue fees can be created or replaced
     *
     * @return bool
     */
    public function canCreateOrReplaceIssueFee()
    {
        return $this->isNotYetSubmitted();
    }

    /**
     * Updates date receive for application
     * @param string $dateReceived application received date
     */
    public function updateDateReceived($dateReceived)
    {
        $this->dateReceived = $this->processDate($dateReceived);
    }

    /**
     * Whether we're able to issue permits for this application
     *
     * @return bool
     */
    public function isReadyForIssuing()
    {
        return !$this->hasOutstandingFees();
    }

    /**
     * Proceeds the application from not yet submitted status to issuing status
     *
     *
     * @throws ForbiddenException
     */
    public function proceedToIssuing(RefData $issuingStatus)
    {
        if (!$this->isReadyForIssuing()) {
            throw new ForbiddenException(self::ERR_CANT_ISSUE);
        }

        $this->status = $issuingStatus;
    }

    /**
     * Proceeds the application from not yet submitted status to under consideration status
     *
     *
     * @throws ForbiddenException
     */
    public function proceedToUnderConsideration(RefData $uc_status)
    {
        if ($this->hasOutstandingFees()) {
            throw new ForbiddenException(self::ERR_CANT_SUBMIT);
        }

        $this->status = $uc_status;
    }

    /**
     * Proceeds the application from issuing status to valid status
     *
     *
     * @throws ForbiddenException
     */
    public function proceedToValid(RefData $validStatus)
    {
        if (!$this->isIssueInProgress() && !$this->isCertificateOfRoadworthiness()) {
            throw new ForbiddenException(
                sprintf(
                    'This application is not in the correct state to proceed to valid (status: %s, irhpPermitType: %d)',
                    $this->status->getId(),
                    $this->irhpPermitType->getId()
                )
            );
        }

        $this->status = $validStatus;
    }

    /**
     * Get the application path locked on datetime
     *
     * @return \DateTime
     */
    public function getApplicationPathLockedOn()
    {
        // the application path locked at the time when the application was created
        return $this->getCreatedOn(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveApplicationPath()
    {
        // get application path active at the time when the application path was locked
        return $this->getFirstIrhpPermitApplication()->getActiveApplicationPath();
    }

    /**
     * {@inheritdoc}
     */
    public function getContextValue()
    {
        return $this->id;
    }

    /**
     * Retrieves the first linked irhp permit application instance
     *
     * @return IrhpPermitApplication
     *
     * @throws RuntimeException
     */
    public function getFirstIrhpPermitApplication()
    {
        if ($this->irhpPermitApplications->count() == 0) {
            throw new RuntimeException(
                'IrhpApplication has zero linked IrhpPermitApplication instances'
            );
        }

        return $this->irhpPermitApplications->first();
    }

    /**
     * Is this a Certificate of Roadworthiness application?
     *
     * @return bool
     */
    public function isCertificateOfRoadworthiness(): bool
    {
        return $this->irhpPermitType->isCertificateOfRoadworthiness();
    }

    /**
     * Is this a Certificate of Roadworthiness vehicle application?
     *
     * @return bool
     */
    public function isCertificateOfRoadworthinessVehicle(): bool
    {
        return $this->irhpPermitType->isCertificateOfRoadworthinessVehicle();
    }

    /**
     * Is this a Certificate of Roadworthiness trailer application?
     *
     * @return bool
     */
    public function isCertificateOfRoadworthinessTrailer(): bool
    {
        return $this->irhpPermitType->isCertificateOfRoadworthinessTrailer();
    }

    /**
     * Is this application a multi stock application?
     *
     * @return bool
     */
    public function isMultiStock(): bool
    {
        return $this->irhpPermitType->isMultiStock();
    }

    /**
     * Get the associated stock for this application
     *
     * @return IrhpPermitStock
     * @throws RuntimeException
     */
    public function getAssociatedStock(): IrhpPermitStock
    {
        if ($this->isMultiStock()) {
            throw new RuntimeException('Multi stock permit types can\'t use this method');
        }

        return $this->getFirstIrhpPermitApplication()->getIrhpPermitWindow()->getIrhpPermitStock();
    }

    /**
     * Changes the status to expired
     *
     * @param DateTime|null $expiryDate
     * @throws ForbiddenException
     */
    public function expire(RefData $expireStatus, DateTime $expiryDate = null)
    {
        if (!$this->canBeExpired()) {
            throw new ForbiddenException('This application cannot be expired.');
        }

        if ($expiryDate === null) {
            $expiryDate = new DateTime();
        }

        $this->status = $expireStatus;
        $this->expiryDate = $expiryDate;
    }

    /**
     * Whether the application can be expired
     *
     * @return bool
     */
    public function canBeExpired()
    {
        if (!$this->isValid()) {
            // only valid application can be expired
            return false;
        }

        foreach ($this->getIrhpPermitApplications() as $irhpPermitApplication) {
            if ($irhpPermitApplication->hasValidPermits()) {
                // only application without any valid permits can be expired
                return false;
            }
        }

        return true;
    }

    /**
     * Whether can view candidate permits
     *
     * @return bool
     */
    public function canViewCandidatePermits(): bool
    {
        return $this->isAwaitingFee()
            && $this->isCandidatePermitsAllocationMode()
            && $this->isApgg();
    }

    /**
     * Whether can select candidate permits
     *
     * @return bool
     */
    public function canSelectCandidatePermits(): bool
    {
        return $this->isAwaitingFee()
            && $this->isCandidatePermitsAllocationMode()
            && $this->isApsg();
    }

    /**
     * Whether this application is associated with the candidate permits based allocation mode
     *
     * @return bool
     */
    public function isCandidatePermitsAllocationMode()
    {
        return $this->getAllocationMode() == IrhpPermitStock::ALLOCATION_MODE_CANDIDATE_PERMITS;
    }

    /**
     * Update the international journeys answer value
     */
    public function updateInternationalJourneys(RefData $internationalJourneys)
    {
        $this->internationalJourneys = $internationalJourneys;
    }

    /**
     * Clear the international journeys answer value
     */
    public function clearInternationalJourneys()
    {
        $this->internationalJourneys = null;
    }

    /**
     * Update the sectors answer value
     */
    public function updateSectors(Sectors $sectors)
    {
        $this->sectors = $sectors;
    }

    /**
     * Clear the sectors answer value
     */
    public function clearSectors()
    {
        $this->sectors = null;
    }

    /**
     * Get the business process
     *
     * @return RefData|null
     */
    public function getBusinessProcess()
    {
        // get the business process related to the application
        try {
            return $this->getFirstIrhpPermitApplication()
                ->getIrhpPermitWindow()
                ->getIrhpPermitStock()
                ->getBusinessProcess();
        } catch (RuntimeException) {
            // do nothing if getFirstIrhpPermitApplication() throws an exception
        }
    }

    /**
     * Get the answer value corresponding to the specified question id
     *
     * @param int $id
     *
     * @return mixed|null
     */
    public function getAnswerValueByQuestionId($id)
    {
        return $this->getActiveApplicationPath()
            ->getAnswerValueByQuestionId($id, $this);
    }

    /**
     * Whether the application has an association with the specified country id
     *
     * @param string $id
     *
     * @return bool
     */
    public function hasCountryId($id)
    {
        foreach ($this->countrys as $country) {
            if ($country->getId() == $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the ids of the countries associated with this application
     *
     * @return array
     */
    public function getCountryIds()
    {
        $countryIds = [];

        foreach ($this->countrys as $country) {
            $countryIds[] = $country->getId();
        }

        return $countryIds;
    }

    /**
     * Update the list of countries associated with this application
     */
    public function updateCountries(ArrayCollection $countries)
    {
        $this->countrys = $countries;
    }

    /**
     * Get the total number of permits required by this application
     *
     * @return int
     *
     * @throws RuntimeException
     */
    public function calculateTotalPermitsRequired()
    {
        $irhpPermitType = $this->getIrhpPermitType();

        if (!$irhpPermitType->isEcmtShortTerm() && !$irhpPermitType->isEcmtAnnual()) {
            throw new RuntimeException(
                'calculateTotalPermitsRequired is only applicable to ECMT short term and ECMT Annual'
            );
        }

        $irhpPermitApplication = $this->getFirstIrhpPermitApplication();
        $requiredEuro5 = $irhpPermitApplication->getRequiredEuro5();
        $requiredEuro6 = $irhpPermitApplication->getRequiredEuro6();

        if (is_null($requiredEuro5) || is_null($requiredEuro6)) {
            throw new RuntimeException('This IRHP Application has not had number of required permits set yet.');
        }

        return $requiredEuro5 + $requiredEuro6;
    }

    /**
     * Calculates the intensity_of_use value for permits requested by an irhpApplication
     *
     * @param string $emissionsCategoryId|null
     *
     * return float
     *
     * @throws RuntimeException
     */
    public function getPermitIntensityOfUse($emissionsCategoryId = null)
    {
        $firstIrhpPermitApplication = $this->getFirstIrhpPermitApplication();

        if ($emissionsCategoryId == RefData::EMISSIONS_CATEGORY_EURO5_REF) {
            $numberOfPermits = $firstIrhpPermitApplication->getRequiredEuro5();
        } elseif ($emissionsCategoryId == RefData::EMISSIONS_CATEGORY_EURO6_REF) {
            $numberOfPermits = $firstIrhpPermitApplication->getRequiredEuro6();
        } elseif (is_null($emissionsCategoryId)) {
            $numberOfPermits = $this->calculateTotalPermitsRequired();
        } else {
            throw new RuntimeException(
                'Unexpected emissionsCategoryId parameter for getPermitIntensityOfUse: ' . $emissionsCategoryId
            );
        }

        return $this->calculatePermitIntensityOfUse(
            $this->getAnswerValueByQuestionId(Question::QUESTION_ID_ECMT_ANNUAL_TRIPS_ABROAD),
            $numberOfPermits
        );
    }

    /**
     * Calculates the application_score value for permits requested by an irhpApplication
     *
     * @param string $emissionsCategoryId|null
     *
     * @return float
     */
    public function getPermitApplicationScore($emissionsCategoryId = null)
    {
        return $this->calculatePermitApplicationScore(
            $this->getPermitIntensityOfUse($emissionsCategoryId),
            $this->internationalJourneys->getId()
        );
    }

    /**
     * Return the entity name in camel case
     *
     * @return string
     */
    public function getCamelCaseEntityName()
    {
        return 'irhpApplication';
    }

    /**
     * Return an array of mappings between success levels and email commands
     *
     * @return array
     */
    public function getEmailCommandLookup()
    {
        if ($this->irhpPermitType->isEcmtAnnual()) {
            return [
                ApplicationAcceptConsts::SUCCESS_LEVEL_NONE => SendEcmtApsgUnsuccessful::class,
                ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL => SendEcmtApsgPartSuccessful::class,
                ApplicationAcceptConsts::SUCCESS_LEVEL_FULL => SendEcmtApsgSuccessful::class
            ];
        } elseif ($this->irhpPermitType->isEcmtShortTerm()) {
            return [
                ApplicationAcceptConsts::SUCCESS_LEVEL_NONE => SendEcmtShortTermUnsuccessful::class,
                ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL => SendEcmtShortTermApsgPartSuccessful::class,
                ApplicationAcceptConsts::SUCCESS_LEVEL_FULL => SendEcmtShortTermSuccessful::class
            ];
        }

        throw new RuntimeException('getEmailCommandLookup is only applicable to ECMT short term and ECMT Annual');
    }

    /**
     * Return the number of trips above which the intensity of use should be classed as high intensity
     *
     * @return int
     */
    public function getIntensityOfUseWarningThreshold()
    {
        if (!$this->irhpPermitType->isEcmtShortTerm() && !$this->irhpPermitType->isEcmtAnnual()) {
            throw new RuntimeException('getIntensityOfUseWarningThreshold is only applicable to ECMT short term and ECMT Annual');
        }

        $irhpPermitApplication = $this->getFirstIrhpPermitApplication();

        $highestRequiredPermits = max(
            $irhpPermitApplication->getRequiredEuro5(),
            $irhpPermitApplication->getRequiredEuro6()
        );

        return $highestRequiredPermits * 100;
    }

    /**
     * Return the command class name that represents the application submitted email for this application, or null if
     * no email command is applicable
     *
     * @return string|null
     */
    public function getAppSubmittedEmailCommand()
    {
        $businessProcessId = $this->getBusinessProcess()->getId();
        $isApsg = $businessProcessId == RefData::BUSINESS_PROCESS_APSG;
        $isApgg = $businessProcessId == RefData::BUSINESS_PROCESS_APGG;

        if ($this->irhpPermitType->isEcmtAnnual()) {
            if ($isApsg) {
                return SendEcmtApsgAppSubmitted::class;
            } elseif ($isApgg) {
                return SendEcmtApggAppSubmitted::class;
            }
        } elseif ($this->irhpPermitType->isEcmtShortTerm() && $isApsg) {
            return SendEcmtShortTermAppSubmitted::class;
        }

        return null;
    }

    /**
     * Return the command class name that represents the application withdrawn email for this application, or null if
     * no email command is applicable
     *
     * @return string|null
     */
    public function getAppWithdrawnEmailCommand($withdrawReason)
    {
        $irhpPermitTypeId = $this->irhpPermitType->getId();

        $commandLookup = [
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT => [
                WithdrawableInterface::WITHDRAWN_REASON_UNPAID => SendEcmtAutomaticallyWithdrawn::class
            ],
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM => [
                WithdrawableInterface::WITHDRAWN_REASON_NOTSUCCESS => SendEcmtShortTermUnsuccessful::class,
                WithdrawableInterface::WITHDRAWN_REASON_UNPAID => SendEcmtShortTermAutomaticallyWithdrawn::class,
            ]
        ];

        return $commandLookup[$irhpPermitTypeId][$withdrawReason] ?? null;
    }

    /**
     * Return the command class name that represents the issued email for this application, or null if no email command
     * is applicable
     *
     * @return string|null
     */
    public function getIssuedEmailCommand()
    {
        if ($this->irhpPermitType->isEcmtAnnual()) {
            if ($this->isApsg()) {
                return SendEcmtApsgIssued::class;
            } elseif ($this->isApgg()) {
                return SendEcmtApggIssued::class;
            }
        } elseif ($this->irhpPermitType->isEcmtShortTerm()) {
            if ($this->isApsg()) {
                return SendEcmtShortTermApsgIssued::class;
            } elseif ($this->isApgg()) {
                return SendEcmtShortTermApggIssued::class;
            }
        }

        return null;
    }

    /**
     * Get the permit allocation mode used by the stock associated with this application
     *
     * @return string
     */
    public function getAllocationMode()
    {
        return $this->getAssociatedStock()->getAllocationMode();
    }

    /**
     * Whether permits should be immediately allocated on submission for this application
     *
     * @return bool
     */
    public function shouldAllocatePermitsOnSubmission()
    {
        return $this->getBusinessProcess()->getId() == RefData::BUSINESS_PROCESS_APG;
    }

    /**
     * Update the checked value for this application
     *
     * @param bool $checked
     */
    public function updateChecked($checked)
    {
        $this->checked = $checked;
    }

    /**
     * Get the description associated with the task to be created on application submission
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function getSubmissionTaskDescription()
    {
        $mappings = [
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT
                => Task::TASK_DESCRIPTION_ANNUAL_ECMT_RECEIVED,
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM
                => Task::TASK_DESCRIPTION_SHORT_TERM_ECMT_RECEIVED,
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL
                => Task::TASK_DESCRIPTION_ECMT_INTERNATIONAL_REMOVALS_RECEIVED,
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL
                => Task::TASK_DESCRIPTION_BILATERAL_RECEIVED,
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL
                => Task::TASK_DESCRIPTION_MULTILATERAL_RECEIVED,
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE
                => Task::TASK_DESCRIPTION_CERT_ROADWORTHINESS_RECEIVED,
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER
                => Task::TASK_DESCRIPTION_CERT_ROADWORTHINESS_RECEIVED,
        ];

        $irhpPermitTypeId = $this->irhpPermitType->getId();

        if (!isset($mappings[$irhpPermitTypeId])) {
            throw new RuntimeException('No submission task description defined for permit type ' . $irhpPermitTypeId);
        }

        return $mappings[$irhpPermitTypeId];
    }

    /**
     * Get the MOT expiry date for a certificate of roadworthiness
     *
     * @return string|null
     */
    public function getMotExpiryDate(): ?string
    {
        if (!$this->isCertificateOfRoadworthiness()) {
            return null;
        }

        $questionId = Question::QUESTION_ID_ROADWORTHINESS_VEHICLE_MOT_EXPIRY;

        if ($this->isCertificateOfRoadworthinessTrailer()) {
            $questionId = Question::QUESTION_ID_ROADWORTHINESS_TRAILER_MOT_EXPIRY;
        }

        return $this->getAnswerValueByQuestionId($questionId);
    }

    /**
     * Return whether the MOT has expired
     *
     * @return bool
     */
    private function motHasExpired(): bool
    {
        $motExpiryDate = $this->getMotExpiryDate();

        //cover instances where expiry date not yet completed
        if ($motExpiryDate === null) {
            return false;
        }

        $currentDate = new \DateTime();

        //if MOT expiry date (returned by Q&A as a Y-m-d string) is after the current date then it is still valid.
        if ($motExpiryDate >= $currentDate->format('Y-m-d')) {
            return false;
        }

        return true;
    }

    /**
     * Expire certificate of roadworthiness
     *
     *
     * @throws \Exception
     * @return void
     */
    public function expireCertificate(RefData $expireStatus): void
    {
        if (!$this->isCertificateOfRoadworthiness()) {
            throw new \Exception(self::ERR_ROADWORTHINESS_ONLY);
        }

        if (!$this->motHasExpired()) {
            throw new \Exception(self::ERR_ROADWORTHINESS_MOT_EXPIRY);
        }

        $this->expire(
            $expireStatus,
            DateTime::createFromFormat('Y-m-d', $this->getMotExpiryDate())
        );
    }

    /**
     * Get the status that this application should be set to upon submission
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function getSubmissionStatus()
    {
        $mappings = [
            RefData::BUSINESS_PROCESS_AG => IrhpInterface::STATUS_VALID,
            RefData::BUSINESS_PROCESS_APG => IrhpInterface::STATUS_ISSUING,
            RefData::BUSINESS_PROCESS_APGG => IrhpInterface::STATUS_UNDER_CONSIDERATION,
            RefData::BUSINESS_PROCESS_APSG => IrhpInterface::STATUS_UNDER_CONSIDERATION,
        ];

        $businessProcessId = $this->getBusinessProcess()->getId();

        if (!isset($mappings[$businessProcessId])) {
            throw new RuntimeException('No submission status defined for business process ' . $businessProcessId);
        }

        return $mappings[$businessProcessId];
    }

    /**
     * Get the candidate permit creation mode associated with this application
     *
     * @return string
     */
    public function getCandidatePermitCreationMode()
    {
        if ($this->isMultiStock()) {
            return IrhpPermitStock::CANDIDATE_MODE_NONE;
        }

        return $this->getAssociatedStock()->getCandidatePermitCreationMode();
    }

    /**
     * Whether this application needs to be manually checked by a case worker before permits are allocated
     *
     * @return bool
     */
    public function requiresPreAllocationCheck()
    {
        return $this->irhpPermitType->isEcmtShortTerm() || $this->irhpPermitType->isEcmtAnnual();
    }

    /**
     * Whether the permit application can be revived from a withdrawn state
     *
     * @return bool
     */
    public function canBeRevivedFromWithdrawn()
    {
        $businessProcess = $this->getBusinessProcess();

        if ($businessProcess === null) {
            return false;
        }

        $canBeRevivedFromWithdrawn = $this->baseCanBeRevivedFromWithdrawn();
        $isApsg = $businessProcess->getId() == RefData::BUSINESS_PROCESS_APSG;

        return $canBeRevivedFromWithdrawn && $isApsg;
    }

    /**
     * Whether the permit application is APSG
     *
     * @return bool
     */
    public function isApsg()
    {
        return $this->isBusinessProcess(RefData::BUSINESS_PROCESS_APSG);
    }

    /**
     * Whether the permit application is APGG
     *
     * @return bool
     */
    public function isApgg()
    {
        return $this->isBusinessProcess(RefData::BUSINESS_PROCESS_APGG);
    }

    private function isBusinessProcess($businessProcessId)
    {
        $businessProcess = $this->getBusinessProcess();

        if ($businessProcess === null) {
            return false;
        }

        return $businessProcess->getId() == $businessProcessId;
    }

    /**
     * Whether the permit application can be revived from an unsuccessful state
     *
     * @return bool
     */
    public function canBeRevivedFromUnsuccessful()
    {
        $businessProcess = $this->getBusinessProcess();

        if ($businessProcess === null) {
            return false;
        }

        $canBeRevivedFromUnsuccessful = $this->baseCanBeRevivedFromUnsuccessful();
        $isApsg = $businessProcess->getId() == RefData::BUSINESS_PROCESS_APSG;

        return $canBeRevivedFromUnsuccessful && $isApsg;
    }

    /**
     * Indicate the type of notification required upon completion of scoring acceptance, returning one of the
     * NOTIFICATION_TYPE_* constants
     *
     * @return string
     */
    public function getOutcomeNotificationType()
    {
        $mappings = [
            IrhpInterface::SOURCE_SELFSERVE => ApplicationAcceptConsts::NOTIFICATION_TYPE_EMAIL,
            IrhpInterface::SOURCE_INTERNAL => ApplicationAcceptConsts::NOTIFICATION_TYPE_MANUAL
        ];

        return $mappings[$this->source->getId()];
    }

    /**
     * Indicate whether this application is either unsuccessful, partially successful or fully successful, returning
     * one of the class constants SUCCESS_LEVEL_NONE, SUCCESS_LEVEL_PARTIAL or SUCCESS_LEVEL_FULL accordingly
     *
     * @return string
     */
    public function getSuccessLevel()
    {
        $permitsAwarded = $this->getPermitsAwarded();

        $successLevel = ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL;
        if ($permitsAwarded == 0) {
            $successLevel = ApplicationAcceptConsts::SUCCESS_LEVEL_NONE;
        } elseif ($this->calculateTotalPermitsRequired() == $permitsAwarded) {
            $successLevel = ApplicationAcceptConsts::SUCCESS_LEVEL_FULL;
        }

        return $successLevel;
    }

    /**
     * Indicate whether this application is in the correct state for post scoring email report generation and upload
     *
     * @return bool
     */
    public function hasStateRequiredForPostScoringEmail()
    {
        if (!$this->isUnderConsideration() || !$this->getInScope()) {
            return false;
        }

        $permittedSuccessLevels = [
            ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL,
            ApplicationAcceptConsts::SUCCESS_LEVEL_FULL,
        ];

        $successLevel = $this->getSuccessLevel();

        return in_array($successLevel, $permittedSuccessLevels);
    }

    /**
     * Proceeds the application from under consideration to unsuccessful during the accept scoring process
     *
     *
     * @throws ForbiddenException
     */
    public function proceedToUnsuccessful(RefData $unsuccessfulStatus)
    {
        if (!$this->isUnderConsideration()) {
            throw new ForbiddenException('This application is not in the correct state to proceed to unsuccessful (' . $this->status->getId() . ')');
        }

        $this->status = $unsuccessfulStatus;
    }

    /**
     * Proceeds the application from under consideration to awaiting fee during the accept scoring process
     *
     *
     * @throws ForbiddenException
     */
    public function proceedToAwaitingFee(RefData $awaitingFeeStatus)
    {
        if (!$this->isUnderConsideration()) {
            throw new ForbiddenException('This application is not in the correct state to proceed to awaiting fee (' . $this->status->getId() . ')');
        }

        $this->status = $awaitingFeeStatus;
    }

    /**
     * Return the number of permits awarded to this application. Only applicable to applications that are currently
     * under consideration
     *
     * @return int
     *
     * @throws ForbiddenException
     */
    public function getPermitsAwarded()
    {
        if (!$this->isUnderConsideration() && !$this->isAwaitingFee()) {
            throw new ForbiddenException(
                'This application is not in the correct state to return permits awarded (' . $this->status->getId() . ')'
            );
        }

        return $this->getFirstIrhpPermitApplication()->countPermitsAwarded();
    }

    /**
     * Return a list of country codes, names and completion statuses associated with a bilateral application, ordered
     * alphabetically by country name
     *
     * @return array
     */
    protected function getBilateralCountriesAndStatuses()
    {
        $countryStatuses = [];

        $countryEntities = $this->countrys;
        foreach ($countryEntities as $countryEntity) {
            $countryId = $countryEntity->getId();

            $countryStatuses[$countryId] = [
                self::COUNTRY_PROPERTY_CODE => $countryId,
                self::COUNTRY_PROPERTY_NAME => $countryEntity->getCountryDesc(),
                self::COUNTRY_PROPERTY_STATUS => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
            ];
        }

        foreach ($this->irhpPermitApplications as $irhpPermitApplication) {
            $countryId = $irhpPermitApplication->getIrhpPermitWindow()
                ->getIrhpPermitStock()
                ->getCountry()
                ->getId();

            if (!isset($countryStatuses[$countryId])) {
                throw new RuntimeException(
                    'Found irhp_permit_application instance without accompanying irhp_application_country_link'
                );
            }

            $newStatus = SectionableInterface::SECTION_COMPLETION_INCOMPLETE;

            assert($irhpPermitApplication instanceof IrhpPermitApplication);
            if ($irhpPermitApplication->getCheckedAnswers()) {
                $newStatus = SectionableInterface::SECTION_COMPLETION_COMPLETED;
            }

            $countryStatuses[$countryId][self::COUNTRY_PROPERTY_STATUS] = $newStatus;
            $countryStatuses[$countryId][self::COUNTRY_PROPERTY_IPA_ID] = $irhpPermitApplication->getId();
        }

        usort($countryStatuses, $this->usortByCountryName(...));

        $result = [];
        foreach ($countryStatuses as $properties) {
            $irhpPermitAppId = array_key_exists(self::COUNTRY_PROPERTY_IPA_ID, $properties) ? $properties[self::COUNTRY_PROPERTY_IPA_ID] : null;
            $result[] = [
                'countryCode' => $properties[self::COUNTRY_PROPERTY_CODE],
                'countryName' => $properties[self::COUNTRY_PROPERTY_NAME],
                'status' => $properties[self::COUNTRY_PROPERTY_STATUS],
                'irhpPermitApplication' => $irhpPermitAppId,
            ];
        }

        return $result;
    }

    /**
     * Sorting method used by getBilateralCountriesAndStatuses
     *
     * @return int
     */
    protected function usortByCountryName(array $country1, array $country2)
    {
        $countryName1 = $country1[self::COUNTRY_PROPERTY_NAME];
        $countryName2 = $country2[self::COUNTRY_PROPERTY_NAME];
        return $countryName1 <=> $countryName2;
    }

    /**
     * Whether all country sections within a bilateral application have been completed
     *
     * @return bool
     */
    protected function areBilateralCountriesCompleted()
    {
        $countries = $this->getBilateralCountriesAndStatuses();

        foreach ($countries as $country) {
            if ($country[self::COUNTRY_PROPERTY_STATUS] != SectionableInterface::SECTION_COMPLETION_COMPLETED) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function createAnswer(QuestionText $questionText)
    {
        return Answer::createNewForIrhpApplication($questionText, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function onSubmitApplicationStep()
    {
        $this->resetCheckAnswersAndDeclaration();
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalQaViewData(ApplicationStep $applicationStep)
    {
        return [
            'applicationReference' => $this->getApplicationRef()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicationPathEnabled()
    {
        return $this->irhpPermitType->isApplicationPathEnabled();
    }

    /**
     * @param string $countryId
     * @return mixed
     */
    public function getIrhpPermitApplicationIdForCountry(Country $countryEntity)
    {
        $countries = $this->getBilateralCountriesAndStatuses();
        foreach ($countries as $country) {
            if ($country['countryCode'] == $countryEntity->getId()) {
                return $country['irhpPermitApplication'];
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepositoryName()
    {
        return 'IrhpApplication';
    }

    /**
     * @param string $countryId
     *
     * @return IrhpPermitApplication|null
     */
    public function getIrhpPermitApplicationByStockCountryId($countryId)
    {
        $irhpPermitApplications = $this->irhpPermitApplications;

        foreach ($irhpPermitApplications as $irhpPermitApplication) {
            $stockCountryId = $irhpPermitApplication->getIrhpPermitWindow()
                ->getIrhpPermitStock()
                ->getCountry()
                ->getId();

            if ($countryId == $stockCountryId) {
                return $irhpPermitApplication;
            }
        }

        return null;
    }

    /**
     * Whether the permit application can be reset to NotYetSubmitted from Valid
     *
     * @return bool
     */
    public function canBeResetToNotYetSubmittedFromValid()
    {
        return $this->isValid() && $this->isCertificateOfRoadworthiness();
    }

    /**
     * Reset to NotYetSubmitted from Valid
     *
     *
     * @throws ForbiddenException
     */
    public function resetToNotYetSubmittedFromValid(RefData $status)
    {
        if (!$this->canBeResetToNotYetSubmittedFromValid()) {
            throw new ForbiddenException('Unable to reset this application to Not Yet Submitted');
        }

        $this->status = $status;
    }

    /**
     * Whether the permit application can be reset to NotYetSubmitted from Cancelled
     *
     * @return bool
     */
    public function canBeResetToNotYetSubmittedFromCancelled()
    {
        $statusAndPermitTypeEligible = $this->isCancelled() &&
            ($this->irhpPermitType->isEcmtShortTerm() || $this->irhpPermitType->isEcmtAnnual());

        if (!$statusAndPermitTypeEligible) {
            return false;
        }

        return !$this->licence->hasUnderConsiderationOrAwaitingFeeApplicationForStock(
            $this->getAssociatedStock()
        );
    }

    /**
     * Reset to NotYetSubmitted from Cancelled
     *
     *
     * @throws ForbiddenException
     */
    public function resetToNotYetSubmittedFromCancelled(RefData $status)
    {
        if (!$this->canBeResetToNotYetSubmittedFromCancelled()) {
            throw new ForbiddenException('Unable to reset this application to Not Yet Submitted');
        }

        $this->status = $status;
    }

    /**
     * Whether the permit application is ongoing
     *
     * @return bool
     */
    public function isOngoing()
    {
        return $this->isNotYetSubmitted() || $this->isUnderConsideration() || $this->isAwaitingFee();
    }

    /**
     * Fetch documents associated with this application matching the specified category and sub category
     *
     * @param int $categoryId
     * @param int $subCategoryId
     *
     * @return ArrayCollection
     */
    public function getDocumentsByCategoryAndSubCategory($categoryId, $subCategoryId)
    {
        $matchingDocuments = new ArrayCollection();

        foreach ($this->documents as $document) {
            if (
                $document->getCategory()->getId() == $categoryId &&
                is_object($document->getSubCategory()) &&
                $document->getSubCategory()->getId() == $subCategoryId
            ) {
                $matchingDocuments->add($document);
            }
        }

        return $matchingDocuments;
    }

    /**
     * Whether this application is either under consideration or awating fee, and associated with the specified stock
     *
     *
     * @return bool
     */
    public function isUnderConsiderationOrAwaitingFeeAndAssociatedWithStock(IrhpPermitStock $irhpPermitStock)
    {
        return ($this->isUnderConsideration() || $this->isAwaitingFee())
            && $this->getAssociatedStock() === $irhpPermitStock;
    }

    /**
     * Update the certificate number for a certificate of roadworthiness application
     *
     * @param string $corCertificateNumber
     */
    public function updateCorCertificateNumber($corCertificateNumber)
    {
        $this->corCertificateNumber = $corCertificateNumber;
    }
}
