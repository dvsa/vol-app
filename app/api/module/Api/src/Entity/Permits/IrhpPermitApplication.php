<?php

namespace Dvsa\Olcs\Api\Entity\Permits;

use DateTime;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPath;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Entity\Generic\Question;
use Dvsa\Olcs\Api\Entity\Generic\QuestionText;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Traits\TieredProductReference;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use RuntimeException;

/**
 * IrhpPermitApplication Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="irhp_permit_application",
 *    indexes={
 *        @ORM\Index(name="fk_irhp_permit_applications_irhp_permit_windows1_idx",
     *     columns={"irhp_permit_window_id"}),
 *        @ORM\Index(name="fk_irhp_permit_applications_licence1_idx", columns={"licence_id"}),
 *        @ORM\Index(name="fk_irhp_permit_application_sectors_id1_idx", columns={"sectors_id"}),
 *        @ORM\Index(name="irhp_permit_type_ref_data_status_id_fk", columns={"status"}),
 *        @ORM\Index(name="fk_irhp_permit_application_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_irhp_permit_application_last_modified_by_user_id",
     *     columns={"last_modified_by"})
 *    }
 * )
 */
class IrhpPermitApplication extends AbstractIrhpPermitApplication implements QaEntityInterface
{
    use TieredProductReference;

    public const BILATERAL_STANDARD_REQUIRED = 'standard';
    public const BILATERAL_CABOTAGE_REQUIRED = 'cabotage';
    public const BILATERAL_MOROCCO_REQUIRED = 'morocco';

    public const DEFAULT_BILATERAL_REQUIRED = [
        self::BILATERAL_STANDARD_REQUIRED => null,
        self::BILATERAL_CABOTAGE_REQUIRED => null,
        self::BILATERAL_MOROCCO_REQUIRED => null,
    ];

    public const BILATERAL_FEE_PRODUCT_REFS_TYPE_1 = [
        FeeType::FEE_TYPE_IRHP_APP_BILATERAL_PRODUCT_REF,
        FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_PRODUCT_REF,
    ];

    public const BILATERAL_FEE_PRODUCT_REFS_TYPE_2 = [
        FeeType::FEE_TYPE_IRHP_APP_BILATERAL_SINGLE_PRODUCT_REF,
        FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF,
    ];

    public const DEFAULT_BILATERAL_FEE_PRODUCT_REFS = [
        RefData::JOURNEY_SINGLE => [
            self::BILATERAL_STANDARD_REQUIRED => self::BILATERAL_FEE_PRODUCT_REFS_TYPE_2,
            self::BILATERAL_CABOTAGE_REQUIRED => self::BILATERAL_FEE_PRODUCT_REFS_TYPE_2,
        ],
        RefData::JOURNEY_MULTIPLE => [
            self::BILATERAL_STANDARD_REQUIRED => self::BILATERAL_FEE_PRODUCT_REFS_TYPE_1,
            self::BILATERAL_CABOTAGE_REQUIRED => self::BILATERAL_FEE_PRODUCT_REFS_TYPE_2,
        ],
    ];

    public const BILATERAL_FEE_COUNTRY_CONFIG_NON_EU_STANDARD = [
        RefData::JOURNEY_SINGLE => [
            self::BILATERAL_STANDARD_REQUIRED => [
                FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF_NON_EU,
            ]
        ]
    ];

    public const CUSTOM_BILATERAL_FEE_PRODUCT_REFS = [
        Country::ID_BELARUS => self::BILATERAL_FEE_COUNTRY_CONFIG_NON_EU_STANDARD,
        Country::ID_GEORGIA => self::BILATERAL_FEE_COUNTRY_CONFIG_NON_EU_STANDARD,
        Country::ID_RUSSIA => self::BILATERAL_FEE_COUNTRY_CONFIG_NON_EU_STANDARD,
        Country::ID_TUNISIA => self::BILATERAL_FEE_COUNTRY_CONFIG_NON_EU_STANDARD,
        Country::ID_TURKEY => self::BILATERAL_FEE_COUNTRY_CONFIG_NON_EU_STANDARD,
        Country::ID_UKRAINE => self::BILATERAL_FEE_COUNTRY_CONFIG_NON_EU_STANDARD,
        Country::ID_KAZAKHSTAN => [
            RefData::JOURNEY_SINGLE => [
                self::BILATERAL_STANDARD_REQUIRED => []
            ]
        ]
    ];

    public const MOROCCO_BILATERAL_FEE_PRODUCT_REFS = [
        RefData::PERMIT_CAT_STANDARD_MULTIPLE_15 => [
            FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_MULTI_MOROCCO_PRODUCT_REF
        ],
        RefData::PERMIT_CAT_STANDARD_SINGLE => [
            FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF_NON_EU
        ],
        RefData::PERMIT_CAT_EMPTY_ENTRY => [
            FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF_NON_EU
        ],
        RefData::PERMIT_CAT_HORS_CONTINGENT => [
            FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF_NON_EU
        ],
    ];

    public const MULTILATERAL_ISSUE_FEE_PRODUCT_REFERENCE_MONTH_ARRAY = [
        'Jan' => FeeType::FEE_TYPE_IRHP_ISSUE_MULTILATERAL_PRODUCT_REF,
        'Feb' => FeeType::FEE_TYPE_IRHP_ISSUE_MULTILATERAL_PRODUCT_REF,
        'Mar' => FeeType::FEE_TYPE_IRHP_ISSUE_MULTILATERAL_PRODUCT_REF,
        'Apr' => FeeType::FEE_TYPE_IRHP_MULTI_ISSUE_75_PRODUCT_REF,
        'May' => FeeType::FEE_TYPE_IRHP_MULTI_ISSUE_75_PRODUCT_REF,
        'Jun' => FeeType::FEE_TYPE_IRHP_MULTI_ISSUE_75_PRODUCT_REF,
        'Jul' => FeeType::FEE_TYPE_IRHP_MULTI_ISSUE_50_PRODUCT_REF,
        'Aug' => FeeType::FEE_TYPE_IRHP_MULTI_ISSUE_50_PRODUCT_REF,
        'Sep' => FeeType::FEE_TYPE_IRHP_MULTI_ISSUE_50_PRODUCT_REF,
        'Oct' => FeeType::FEE_TYPE_IRHP_MULTI_ISSUE_25_PRODUCT_REF,
        'Nov' => FeeType::FEE_TYPE_IRHP_MULTI_ISSUE_25_PRODUCT_REF,
        'Dec' => FeeType::FEE_TYPE_IRHP_MULTI_ISSUE_25_PRODUCT_REF,
    ];

    public const REQUESTED_PERMITS_KEY = 'requestedPermits';
    public const RANGE_ENTITY_KEY = 'rangeEntity';

    public const ERR_CANT_CHECK_ANSWERS = 'Unable to check answers.';

    public static function createNew(
        IrhpPermitWindow $IrhpPermitWindow,
        Licence $licence,
        IrhpApplication $irhpApplication = null
    ) {
        $IrhpPermitApplication = new self();

        $IrhpPermitApplication->irhpPermitWindow = $IrhpPermitWindow;
        $IrhpPermitApplication->licence = $licence;
        $IrhpPermitApplication->irhpApplication = $irhpApplication;

        return $IrhpPermitApplication;
    }

    /**
     * createNewForIrhpApplication
     *
     * @param IrhpApplication  $irhpApplication  IRHP Application
     * @param IrhpPermitWindow $irhpPermitWindow IRHP Permit Window
     *
     * @return IrhpPermitApplication
     */
    public static function createNewForIrhpApplication(
        IrhpApplication $irhpApplication,
        IrhpPermitWindow $irhpPermitWindow
    ) {
        $irhpPermitApplication = new self();
        $irhpPermitApplication->irhpApplication = $irhpApplication;
        $irhpPermitApplication->irhpPermitWindow = $irhpPermitWindow;

        return $irhpPermitApplication;
    }

    /**
     * Get the intensity of use from the associated ecmt permit application
     *
     * @param string $emissionsCategoryId|null
     *
     * @return float
     */
    public function getPermitIntensityOfUse($emissionsCategoryId = null)
    {
        return $this->irhpApplication->getPermitIntensityOfUse($emissionsCategoryId);
    }

    /**
     * Get the application score from the associated ecmt permit application
     *
     * @param string $emissionsCategoryId|null
     *
     * @return float
     */
    public function getPermitApplicationScore($emissionsCategoryId = null)
    {
        return $this->irhpApplication->getPermitApplicationScore($emissionsCategoryId);
    }

    /**
     * getCalculatedBundleValues
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        $relatedApplication = $this->irhpApplication;

        return [
            'permitsAwarded' => $this->countPermitsAwarded(),
            'euro5PermitsAwarded' => $this->countPermitsAwarded(RefData::EMISSIONS_CATEGORY_EURO5_REF),
            'euro6PermitsAwarded' => $this->countPermitsAwarded(RefData::EMISSIONS_CATEGORY_EURO6_REF),
            'euro5PermitsWanted' => $this->countPermitsAwarded(RefData::EMISSIONS_CATEGORY_EURO5_REF, true),
            'euro6PermitsWanted' => $this->countPermitsAwarded(RefData::EMISSIONS_CATEGORY_EURO6_REF, true),
            'validPermits' => $this->countValidPermits(),
            'permitsRequired' => $this->countPermitsRequired(),
            'relatedApplication' => isset($relatedApplication) ? $relatedApplication->serialize(
                [
                    'licence' => [
                        'organisation'
                    ]
                ]
            ) : null,
        ];
    }

    /**
     * Get num of successful permit applications
     *
     * @param string $assignedEmissionsCategoryId (optional)
     * @param bool $wantedOnly (optional)
     *
     * @return int
     */
    public function countPermitsAwarded($assignedEmissionsCategoryId = null, $wantedOnly = false)
    {
        $allocationMode = $this->irhpPermitWindow->getIrhpPermitStock()->getAllocationMode();
        return match ($allocationMode) {
            IrhpPermitStock::ALLOCATION_MODE_EMISSIONS_CATEGORIES => $this->getTotalEmissionsCategoryPermitsRequired($assignedEmissionsCategoryId),
            IrhpPermitStock::ALLOCATION_MODE_CANDIDATE_PERMITS => count($this->getSuccessfulIrhpCandidatePermits($assignedEmissionsCategoryId, $wantedOnly)),
            default => 0,
        };
    }

    /**
     * Get num of valid permits
     *
     * @return int
     */
    public function countValidPermits()
    {
        $permits = $this->getIrhpPermits()->filter(
            fn($element) => in_array($element->getStatus(), IrhpPermit::$validStatuses)
        );

        return $permits->count();
    }

    /**
     * Get candidate permits marked as successful
     *
     * @param string $assignedEmissionsCategoryId (optional)
     * @param bool $wantedOnly (optional)
     *
     * @return array
     */
    public function getSuccessfulIrhpCandidatePermits($assignedEmissionsCategoryId = null, $wantedOnly = false)
    {
        $criteria = Criteria::create();
        $criteria->where(
            $criteria->expr()->eq('successful', true)
        );

        if ($wantedOnly) {
            $criteria->andWhere(
                $criteria->expr()->eq('wanted', true)
            );
        }

        $candidatePermits = $this->getIrhpCandidatePermits()->matching($criteria);

        if (is_null($assignedEmissionsCategoryId)) {
            return $candidatePermits;
        }

        $filteredCandidatePermits = new ArrayCollection();
        foreach ($candidatePermits as $candidatePermit) {
            if ($candidatePermit->getAssignedEmissionsCategory()->getId() == $assignedEmissionsCategoryId) {
                $filteredCandidatePermits->add($candidatePermit);
            }
        }

        return $filteredCandidatePermits;
    }

    /**
     * Has permits required populated
     *
     * @return bool
     */
    public function hasPermitsRequired()
    {
        return $this->permitsRequired !== null;
    }

    /**
     * Sets the permits required within the stock associated with this entity
     *
     * @param int $permitsRequired
     */
    public function updatePermitsRequired($permitsRequired)
    {
        if (!is_null($this->irhpApplication) && $this->irhpApplication->canBeUpdated()) {
            $this->permitsRequired = $permitsRequired;
        }
    }

    /**
     * Has valid permits
     *
     * @return bool
     */
    public function hasValidPermits()
    {
        return $this->countValidPermits() > 0;
    }

    /**
     * Returns the issue fee product reference for this application
     * Applicable to bilateral and multilateral only
     *
     * @param DateTime $dateTime (optional)
     *
     * @return string
     *
     * @throws ForbiddenException
     */
    public function getIssueFeeProductReference(?DateTime $dateTime = null)
    {
        if (is_null($dateTime)) {
            $dateTime = new DateTime();
        }

        $irhpPermitTypeId = $this->getIrhpApplication()->getIrhpPermitType()->getId();
        switch ($irhpPermitTypeId) {
            case IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL:
                $productReference = FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_PRODUCT_REF;
                break;
            case IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL:
                $irhpPermitStock = $this->getIrhpPermitWindow()->getIrhpPermitStock();
                $productReference = $this->genericGetProdRefForTier(
                    $irhpPermitStock->getValidFrom(true),
                    $irhpPermitStock->getValidTo(true),
                    $dateTime,
                    self::MULTILATERAL_ISSUE_FEE_PRODUCT_REFERENCE_MONTH_ARRAY
                );
                break;
            default:
                throw new ForbiddenException(
                    'Cannot get issue fee product ref and quantity for irhp permit type ' . $irhpPermitTypeId
                );
        }

        return $productReference;
    }

    /**
     * Clear permits required
     *
     * @return bool
     */
    public function clearPermitsRequired()
    {
        return $this->permitsRequired = null;
    }

    /**
     * Set permits required when in an emissions category context
     *
     * @param int|null $requiredEuro5
     * @param int|null $requiredEuro6
     */
    public function updateEmissionsCategoryPermitsRequired($requiredEuro5, $requiredEuro6)
    {
        $this->requiredEuro5 = $requiredEuro5;
        $this->requiredEuro6 = $requiredEuro6;
    }

    /**
     * Return permits required in accordance with the specified emissions category
     *
     * @param string $emissionsCategoryId
     *
     * @return int|null
     *
     * @throws RuntimeException
     */
    public function getRequiredPermitsByEmissionsCategory($emissionsCategoryId)
    {
        if ($emissionsCategoryId == RefData::EMISSIONS_CATEGORY_EURO5_REF) {
            return $this->requiredEuro5;
        } elseif ($emissionsCategoryId == RefData::EMISSIONS_CATEGORY_EURO6_REF) {
            return $this->requiredEuro6;
        }

        throw new RuntimeException('Unsupported emissions category for getRequiredPermitsByEmissionsCategory');
    }

    /**
     * Clear permits required when in an emissions category context
     */
    public function clearEmissionsCategoryPermitsRequired()
    {
        $this->requiredEuro5 = null;
        $this->requiredEuro6 = null;
    }

    /**
     * Get total permits required when in an emissions category context
     *
     * @param string|null $emissionsCategoryId
     *
     * @return int
     */
    public function getTotalEmissionsCategoryPermitsRequired($emissionsCategoryId = null)
    {
        $requiredEuro5 = is_null($this->requiredEuro5) ? 0 : $this->requiredEuro5;
        $requiredEuro6 = is_null($this->requiredEuro6) ? 0 : $this->requiredEuro6;

        return match ($emissionsCategoryId) {
            RefData::EMISSIONS_CATEGORY_EURO5_REF => $requiredEuro5,
            RefData::EMISSIONS_CATEGORY_EURO6_REF => $requiredEuro6,
            default => $requiredEuro5 + $requiredEuro6,
        };
    }

    /**
     * Get an array where each element contains a range entity and the number of candidate permits requested within
     * the range
     *
     * @return array
     */
    public function getRangesWithCandidatePermitCounts()
    {
        $ranges = [];

        foreach ($this->irhpCandidatePermits as $irhpCandidatePermit) {
            $irhpPermitRange = $irhpCandidatePermit->getIrhpPermitRange();
            $irhpPermitRangeId = $irhpPermitRange->getId();

            if (!array_key_exists($irhpPermitRangeId, $ranges)) {
                $ranges[$irhpPermitRangeId] = [
                    self::REQUESTED_PERMITS_KEY => 0,
                    self::RANGE_ENTITY_KEY => $irhpPermitRange,
                ];
            }

            $ranges[$irhpPermitRangeId][self::REQUESTED_PERMITS_KEY]++;
        }

        return $ranges;
    }

    /**
     * Return a permit issue date appropriate to this application
     *
     * @return DateTime
     */
    public function generateIssueDate()
    {
        if ($this->irhpApplication->getIrhpPermitType()->isEcmtRemoval()) {
            return new DateTime(
                $this->irhpApplication->getAnswerValueByQuestionId(Question::QUESTION_ID_REMOVAL_PERMIT_START_DATE)
            );
        }

        if ($this->irhpApplication->getIrhpPermitType()->isEcmtShortTerm()) {
            return new DateTime(
                $this->irhpApplication->getAnswerValueByQuestionId(Question::QUESTION_ID_SHORT_TERM_EARLIEST_PERMIT_START_DATE)
            );
        }

        return new DateTime();
    }

    /**
     * Return a permit expiry date appropriate to this application
     *
     * @return DateTime
     */
    public function generateExpiryDate()
    {
        return $this->irhpApplication->getIrhpPermitType()->generateExpiryDate(
            $this->generateIssueDate()
        );
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
     * {@inheritdoc}
     */
    public function createAnswer(QuestionText $questionText)
    {
        return Answer::createNewForIrhpPermitApplication($questionText, $this);
    }

    /**
     * @return bool
     */
    public function isNotYetSubmitted()
    {
        return $this->irhpApplication->isNotYetSubmitted();
    }

    /**
     * Get the application path locked on datetime
     *
     * @return \DateTime
     */
    public function getApplicationPathLockedOn()
    {
        return $this->irhpApplication->getApplicationPathLockedOn();
    }

    /**
     * Get the active application path
     *
     * @return ApplicationPath|null
     */
    public function getActiveApplicationPath()
    {
        return $this->getIrhpPermitWindow()
            ->getIrhpPermitStock()
            ->getApplicationPathGroup()
            ->getActiveApplicationPath($this->getApplicationPathLockedOn());
    }

    /**
     * Get an answer to the given application step
     *
     * @return mixed|null
     */
    public function getAnswer(ApplicationStep $applicationStep)
    {
        $question = $applicationStep->getQuestion();

        if ($question->isCustom()) {
            throw new RuntimeException('Unable to retrieve answer status for custom form control types');
        }

        $applicationPathLockedOn = $this->getApplicationPathLockedOn();
        return $question->getStandardAnswer($this, $applicationPathLockedOn);
    }

    /**
     * Reset the checked answers section to a value representing 'not completed'
     */
    public function resetCheckAnswers()
    {
        if ($this->irhpApplication->canBeUpdated()) {
            $this->checkedAnswers = false;
        }
    }

    /**
     * Return the entity name in camel case
     *
     * @return string
     */
    public function getCamelCaseEntityName()
    {
        return 'irhpPermitApplication';
    }

    /**
     * Executed on submission of an application step
     */
    public function onSubmitApplicationStep()
    {
        $this->resetCheckAnswers();
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalQaViewData(ApplicationStep $applicationStep)
    {
        $country = $this->irhpPermitWindow->getIrhpPermitStock()->getCountry();

        return [
            'previousStepSlug' => $applicationStep->getPreviousStepSlug(),
            'countryName' => $country->getCountryDesc(),
            'countryCode' => $country->getId()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicationPathEnabled()
    {
        return $this->irhpApplication->getIrhpPermitType()->isIrhpPermitApplicationPathEnabled();
    }

    /**
     * {@inheritdoc}
     */
    public function getRepositoryName()
    {
        return 'IrhpPermitApplication';
    }

    /**
     * Returns the selection made on the bilateral cabotage page (or the default selection of standard only if the
     * journey doesn't contain a cabotage page)
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function getBilateralCabotageSelection()
    {
        $this->throwRuntimeExceptionIfNotBilateral(__FUNCTION__);

        $cabotageOnlyAnswer = $this->getAnswerValueByQuestionId(Question::QUESTION_ID_BILATERAL_CABOTAGE_ONLY);
        if (!is_null($cabotageOnlyAnswer)) {
            return $cabotageOnlyAnswer;
        }

        $standardAndCabotageAnswer = $this->getAnswerValueByQuestionId(
            Question::QUESTION_ID_BILATERAL_STANDARD_AND_CABOTAGE
        );

        if (!is_null($standardAndCabotageAnswer)) {
            return $standardAndCabotageAnswer;
        }

        return Answer::BILATERAL_STANDARD_ONLY;
    }

    /**
     * Returns the selection made on the bilateral permit usage page
     *
     * @return string
     */
    public function getBilateralPermitUsageSelection()
    {
        $this->throwRuntimeExceptionIfNotBilateral(__FUNCTION__);

        return $this->getAnswerValueByQuestionId(Question::QUESTION_ID_BILATERAL_PERMIT_USAGE);
    }

    /**
     * Update required permits for a bilateral application
     */
    public function updateBilateralRequired(array $required)
    {
        $this->throwRuntimeExceptionIfNotBilateral(__FUNCTION__);

        if (!$this->irhpApplication->canBeUpdated()) {
            throw new RuntimeException(__FUNCTION__ . ' called when application in unexpected state');
        }

        $inputArrayKeys = array_keys($required);
        if ($inputArrayKeys != array_keys(self::DEFAULT_BILATERAL_REQUIRED)) {
            throw new RuntimeException('Unexpected or missing array keys passed to ' . __FUNCTION__);
        }

        $this->requiredStandard = $required[self::BILATERAL_STANDARD_REQUIRED];
        $this->requiredCabotage = $required[self::BILATERAL_CABOTAGE_REQUIRED];
        $this->permitsRequired = $required[self::BILATERAL_MOROCCO_REQUIRED];
    }

    /**
     * Clear required permits for a bilateral application
     */
    public function clearBilateralRequired()
    {
        $this->updateBilateralRequired(self::DEFAULT_BILATERAL_REQUIRED);
    }

    /**
     * Get required permits for a bilateral application
     *
     * @return array
     */
    public function getBilateralRequired()
    {
        $this->throwRuntimeExceptionIfNotBilateral(__FUNCTION__);

        return [
            self::BILATERAL_STANDARD_REQUIRED => $this->requiredStandard,
            self::BILATERAL_CABOTAGE_REQUIRED => $this->requiredCabotage,
            self::BILATERAL_MOROCCO_REQUIRED => $this->permitsRequired,
        ];
    }

    /**
     * Get required permits for a bilateral application, including only types where a quantity is specified
     *
     * @return array
     */
    public function getFilteredBilateralRequired()
    {
        return array_filter(
            $this->getBilateralRequired()
        );
    }

    /**
     * Get a key value array containing product references and quantities for use in fee creation
     *
     * @return array
     */
    public function getBilateralFeeProductRefsAndQuantities()
    {
        $this->throwRuntimeExceptionIfNotBilateral(__FUNCTION__);

        $bilateralRequired = $this->getFilteredBilateralRequired();
        $irhpPermitStock = $this->irhpPermitWindow->getIrhpPermitStock();

        $productRefsAndQuantities = [];
        foreach ($bilateralRequired as $requestedType => $quantity) {
            $productReferences = $this->getBilateralFeeProductReferences($irhpPermitStock, $requestedType);

            foreach ($productReferences as $productReference) {
                if (isset($productRefsAndQuantities[$productReference])) {
                    $productRefsAndQuantities[$productReference] += $quantity;
                } else {
                    $productRefsAndQuantities[$productReference] = $quantity;
                }
            }
        }

        return $productRefsAndQuantities;
    }

    /**
     * Get an array of bilateral fee product references in the context of this application for the specified country
     * and standard/cabotage value
     *
     * @param string $requestedType
     *
     * @return array
     * @throws RuntimeException
     */
    public function getBilateralFeeProductReferences(IrhpPermitStock $irhpPermitStock, $requestedType)
    {
        $this->throwRuntimeExceptionIfNotBilateral(__FUNCTION__);

        if ($irhpPermitStock->isMorocco()) {
            $permitCategory = $irhpPermitStock->getPermitCategory()->getId();

            if (!isset(self::MOROCCO_BILATERAL_FEE_PRODUCT_REFS[$permitCategory])) {
                $message = sprintf('No bilateral morocco fee configuration found: %s', $permitCategory);

                throw new RuntimeException($message);
            }

            return self::MOROCCO_BILATERAL_FEE_PRODUCT_REFS[$permitCategory];
        }

        $permitUsage = $this->getBilateralPermitUsageSelection();
        $countryId = $irhpPermitStock->getCountry()->getId();

        $productReferences = self::DEFAULT_BILATERAL_FEE_PRODUCT_REFS;
        if (isset(self::CUSTOM_BILATERAL_FEE_PRODUCT_REFS[$countryId])) {
            $productReferences = self::CUSTOM_BILATERAL_FEE_PRODUCT_REFS[$countryId];
        }

        if (!isset($productReferences[$permitUsage][$requestedType])) {
            $message = sprintf(
                'No bilateral fee configuration found: %s/%s/%s',
                $countryId,
                $permitUsage,
                $requestedType
            );

            throw new RuntimeException($message);
        }

        return $productReferences[$permitUsage][$requestedType];
    }

    /**
     * Get the bilateral fee per permit
     *
     *
     * @return int
     * @throws RuntimeException
     */
    public function getBilateralFeePerPermit(array $feeTypes)
    {
        $this->throwRuntimeExceptionIfNotBilateral(__FUNCTION__);

        $feePerPermit = 0;
        foreach ($feeTypes as $feeType) {
            $feePerPermit += $feeType->getFixedValue();
        }

        return $feePerPermit;
    }

    /**
     * Throws an exception if the associated irhp application is not of type bilateral
     *
     * @param string $methodName
     *
     * @throws RuntimeException
     */
    private function throwRuntimeExceptionIfNotBilateral($methodName)
    {
        if (!$this->irhpApplication->isBilateral()) {
            throw new RuntimeException($methodName . ' is applicable only to bilateral applications');
        }
    }

    /**
     * Get outstanding fees
     *
     * @return array
     */
    public function getOutstandingFees()
    {
        $fees = [];
        foreach ($this->getFees() as $fee) {
            if ($fee->isOutstanding()) {
                $fees[] = $fee;
            }
        }

        return $fees;
    }

    /**
     * @param IrhpPermitWindow $irpPermitWindow
     */
    public function updateIrhpPermitWindow(IrhpPermitWindow $irhpPermitWindow)
    {
        $this->irhpPermitWindow = $irhpPermitWindow;
    }

    /**
     * Whether this application is associated with the bilateral cabotage only application path group
     *
     * @return bool
     */
    public function isAssociatedWithBilateralCabotageOnlyApplicationPathGroup()
    {
        return $this->getIrhpPermitWindow()
            ->getIrhpPermitStock()
            ->getApplicationPathGroup()
            ->isBilateralCabotageOnly();
    }

    /**
     * Update checkedAnswers to true
     *
     * @throws ForbiddenException
     */
    public function updateCheckAnswers()
    {
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
        return $this->irhpApplication->isBilateral() && $this->irhpApplication->canBeUpdated();
    }

    /**
     * Get the type-appropriate number of permits required by this application
     *
     * @return int|null
     */
    public function countPermitsRequired()
    {
        if ($this->irhpApplication->isBilateral()) {
            $permitsRequired = 0;

            $bilateralRequired = $this->getFilteredBilateralRequired();
            foreach ($bilateralRequired as $quantity) {
                $permitsRequired += $quantity;
            }

            return $permitsRequired;
        }

        return parent::getPermitsRequired();
    }
}
