<?php

namespace Dvsa\Olcs\Api\Entity\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\AbstractUpdateStatus;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculator;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculatorInterface;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\DigitalSignature;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceNoGen;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Service\Document\ContextProviderInterface;
use Dvsa\Olcs\Transfer\Validators;
use Laminas\Filter\Word\CamelCaseToUnderscore;
use Dvsa\Olcs\Api\Entity\Traits\TotAuthVehiclesTrait;
use RuntimeException;

/**
 * Application Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="application",
 *    indexes={
 *        @ORM\Index(name="ix_application_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_application_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_application_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_application_licence_type", columns={"licence_type"}),
 *        @ORM\Index(name="ix_application_status", columns={"status"}),
 *        @ORM\Index(name="ix_application_interim_status", columns={"interim_status"}),
 *        @ORM\Index(name="ix_application_withdrawn_reason", columns={"withdrawn_reason"}),
 *        @ORM\Index(name="ix_application_goods_or_psv", columns={"goods_or_psv"}),
 *        @ORM\Index(name="ix_application_applied_via", columns={"applied_via"}),
 *        @ORM\Index(name="ix_application_psv_which_vehicle_sizes", columns={"psv_which_vehicle_sizes"})
 *    }
 * )
 *
 * @see \Dvsa\OlcsTest\Api\Entity\Application\ApplicationEntityTest
 */
class Application extends AbstractApplication implements ContextProviderInterface, OrganisationProviderInterface
{
    use TotAuthVehiclesTrait;

    public const ERROR_NI_NON_GOODS = 'AP-TOL-1';
    public const ERROR_GV_NON_SR = 'AP-TOL-2';
    public const ERROR_VAR_UNCHANGE_NI = 'AP-TOL-3';
    public const ERROR_VAR_UNCHANGE_OT = 'AP-TOL-4';
    public const ERROR_REQUIRES_CONFIRMATION = 'AP-TOL-5';
    public const ERROR_OT_REQUIRED = 'AP-TOL-6';
    public const ERROR_FINANCIAL_HISTORY_DETAILS_REQUIRED = 'AP-FH-1';
    public const ERROR_SAFE_REQUIRE_CONFIRMATION = 'AP-SAFE-1';
    public const ERROR_NO_VEH_ENTERED = 'AP-VEH-1';
    public const ERROR_FEE_NOT_CREATED = 'AP-FEE-NOT-CREATED';

    public const APPLICATION_STATUS_NOT_SUBMITTED = 'apsts_not_submitted';
    // this status will be displayed everywhere as Awaiting grant fee as per OLCS-12606
    public const APPLICATION_STATUS_GRANTED = 'apsts_granted';
    public const APPLICATION_STATUS_UNDER_CONSIDERATION = 'apsts_consideration';
    // this status will be displayed everywhere as Granted as per OLCS-12606
    public const APPLICATION_STATUS_VALID = 'apsts_valid';
    public const APPLICATION_STATUS_WITHDRAWN = 'apsts_withdrawn';
    public const APPLICATION_STATUS_REFUSED = 'apsts_refused';
    public const APPLICATION_STATUS_NOT_TAKEN_UP = 'apsts_ntu';
    public const APPLICATION_STATUS_CURTAILED = 'apsts_curtailed';
    public const APPLICATION_STATUS_CANCELLED = 'apsts_cancelled';

    public const ALL_APPLICATION_STATUSES = [
        self::APPLICATION_STATUS_NOT_SUBMITTED,
        self::APPLICATION_STATUS_GRANTED,
        self::APPLICATION_STATUS_UNDER_CONSIDERATION,
        self::APPLICATION_STATUS_VALID,
        self::APPLICATION_STATUS_WITHDRAWN,
        self::APPLICATION_STATUS_REFUSED,
        self::APPLICATION_STATUS_NOT_TAKEN_UP,
        self::APPLICATION_STATUS_CURTAILED,
        self::APPLICATION_STATUS_CANCELLED
    ];

    public const INTERIM_STATUS_REQUESTED = 'int_sts_requested';
    public const INTERIM_STATUS_INFORCE = 'int_sts_in_force';
    public const INTERIM_STATUS_REFUSED = 'int_sts_refused';
    public const INTERIM_STATUS_REVOKED = 'int_sts_revoked';
    public const INTERIM_STATUS_GRANTED = 'int_sts_granted';
    public const INTERIM_STATUS_ENDED = 'int_sts_ended';

    public const VARIATION_STATUS_UNCHANGED = 0;
    public const VARIATION_STATUS_REQUIRES_ATTENTION = 1;
    public const VARIATION_STATUS_UPDATED = 2;

    public const APPLICATION_TYPE_NEW = 0;
    public const APPLICATION_TYPE_VARIATION = 1;

    // @to-do this needs moving to ref data
    public const APPLICATION_TYPE_NEW_DESCRIPTION = 'New';
    public const APPLICATION_TYPE_VARIATION_DESCRIPTION = 'Variation';

    public const CODE_GV_APP = 'GV79';
    public const CODE_GV_VAR_UPGRADE = 'GV80A';
    public const CODE_GV_VAR_NO_UPGRADE = 'GV81';
    public const CODE_PSV_APP = 'PSV421';
    public const CODE_PSV_APP_SR = 'PSV356';
    public const CODE_PSV_VAR_UPGRADE = 'PSV431A';
    public const CODE_PSV_VAR_NO_UPGRADE = 'PSV431';

    public const NOT_APPLICABLE = 'Not applicable';
    public const UNKNOWN = 'Unknown';

    public const TARGET_COMPLETION_TIME = '+8 week';
    public const OUT_OF_OPPOSITION_DATE_INTERVAL = '+22 days';
    public const OUT_OF_REPRESENTATION_DATE_INTERVAL = '+21 days';

    public const APPLIED_VIA_SELFSERVE = 'applied_via_selfserve';
    public const APPLIED_VIA_POST = 'applied_via_post';
    public const APPLIED_VIA_PHONE = 'applied_via_phone';

    public const PSV_VEHICLE_SIZE_SMALL = 'psvvs_small';
    public const PSV_VEHICLE_SIZE_MEDIUM_LARGE = 'psvvs_medium_large';
    public const PSV_VEHICLE_SIZE_BOTH = 'psvvs_both';

    public const SIG_PHYSICAL_SIGNATURE = RefData::SIG_PHYSICAL_SIGNATURE;
    public const SIG_DIGITAL_SIGNATURE = RefData::SIG_DIGITAL_SIGNATURE;
    public const SIG_SIGNATURE_NOT_REQUIRED = RefData::SIG_SIGNATURE_NOT_REQUIRED;

    public const DATE_FORMAT = 'd M Y';

    public const FINANCIAL_EVIDENCE_SEND_IN_POST = 0;
    public const FINANCIAL_EVIDENCE_UPLOADED = 1;
    public const FINANCIAL_EVIDENCE_UPLOAD_LATER = 2;

    public const VARIATION_TYPE_DIRECTOR_CHANGE = 'vtyp_director_change';

    /**
     * Publication No
     *
     * @var integer
     */
    protected $publicationNo;

    /**
     * Out of objection date
     *
     * @var string
     */
    protected $oooDate;

    /**
     * Out of representation date
     *
     * @var string
     */
    protected $oorDate;

    /**
     * isOpposed
     *
     * @var bool
     */
    protected $isOpposed;

    /**
     * publishedDate
     *
     * @var string
     */
    protected $publishedDate;

    /**
     * Application constructor.
     *
     * @param Licence $licence     Licence
     * @param RefData $status      Application status
     * @param bool    $isVariation Is it a variation
     *
     * @return void
     */
    public function __construct(Licence $licence, RefData $status, $isVariation)
    {
        parent::__construct();

        $this->setLicence($licence);
        $this->setStatus($status);
        $this->setIsVariation($isVariation);
    }

    /**
     * Update the Type of Licence
     *
     * @param string  $niFlag      'Y' or 'N'
     * @param RefData $goodsOrPsv  Goods or psv refdata
     * @param RefData $licenceType Licence type refdata
     * @param RefData|null $vehicleType Vehicle type refdata or null if not applicable
     *
     * @return true|null
     */
    public function updateTypeOfLicence($niFlag, $goodsOrPsv, $licenceType, $vehicleType, mixed $lgvDeclarationConfirmation)
    {
        if ($this->validateTol($niFlag, $goodsOrPsv, $licenceType, $vehicleType, $lgvDeclarationConfirmation)) {
            $this->setNiFlag($niFlag);
            $this->setGoodsOrPsv($goodsOrPsv);
            $this->setLicenceType($licenceType);
            $this->setVehicleType($vehicleType);
            $this->setLgvDeclarationConfirmation($lgvDeclarationConfirmation);

            // reset TotAuthVehicles
            $this->resetTotAuthVehicles();

            return true;
        }
        return null;
    }

    /**
     * Resets TotAuthVehicles based on Vehicle Type
     *
     * @return void
     */
    private function resetTotAuthVehicles()
    {
        if (!$this->canHaveLgv() && $this->totAuthLgvVehicles !== null) {
            // can't have LGV, reset TotAuthLgvVehicles
            $this->updateTotAuthLgvVehicles(null);
        }

        if (!$this->canHaveHgv() && $this->totAuthHgvVehicles !== null) {
            // can't have HGV, reset TotAuthHgvVehicles
            $this->updateTotAuthHgvVehicles(null);
        }

        if (!$this->canHaveTrailer() && $this->totAuthTrailers !== null) {
            // can't have Trailer, reset TotAuthTrailers
            $this->setTotAuthTrailers(null);
        }
    }

    /**
     * Is Type of Licence valid
     *
     * @param string  $niFlag      'Y' or 'N'
     * @param RefData $goodsOrPsv  Goods or psv refdata
     * @param RefData $licenceType Licence type refdata
     * @param RefData|null $vehicleType Vehicle type refdata or null if not applicable
     *
     * @return false|null
     */
    public function isValidTol($niFlag, $goodsOrPsv, $licenceType, $vehicleType, mixed $lgvDeclarationConfirmation)
    {
        try {
            return $this->validateTol($niFlag, $goodsOrPsv, $licenceType, $vehicleType, $lgvDeclarationConfirmation);
        } catch (ValidationException) {
            return false;
        }
    }

    /**
     * Validate Type of Licence
     *
     * @param string  $niFlag      Is Northern Ireland
     * @param RefData $goodsOrPsv  Goods or Psv
     * @param RefData $licenceType Licence type
     * @param RefData|null $vehicleType Vehicle type refdata or null if not applicable
     *
     * @return bool
     * @throws ValidationException
     */
    public function validateTol($niFlag, $goodsOrPsv, $licenceType, $vehicleType, mixed $lgvDeclarationConfirmation)
    {
        $errors = [];

        if (!$goodsOrPsv) {
            $errors['goodsOrPsv'][] = [
                self::ERROR_OT_REQUIRED => 'Operator type is required'
            ];
            // need to throw exception ealier if operator type is empty
            throw new ValidationException($errors);
        }
        if ($niFlag === 'Y' && $goodsOrPsv->getId() === Licence::LICENCE_CATEGORY_PSV) {
            $errors['goodsOrPsv'][] = [
                self::ERROR_NI_NON_GOODS => 'NI can only apply for goods licences'
            ];
        }

        if (
            $goodsOrPsv->getId() === Licence::LICENCE_CATEGORY_GOODS_VEHICLE
            && $licenceType->getId() === Licence::LICENCE_TYPE_SPECIAL_RESTRICTED
        ) {
            $errors['licenceType'][] = [
                self::ERROR_GV_NON_SR => 'GV operators cannot apply for special restricted licences'
            ];
        }

        if ($this->getIsVariation()) {
            if ($this->getGoodsOrPsv() != $goodsOrPsv) {
                $errors['goodsOrPsv'][] = [
                    self::ERROR_GV_NON_SR => 'GV operators cannot apply for special restricted licences'
                ];
            }

            if ($this->getNiFlag() != $niFlag) {
                $errors['niFlag'][] = [
                    self::ERROR_GV_NON_SR => 'GV operators cannot apply for special restricted licences',
                ];
            }
        }

        if (is_null($vehicleType)) {
            $errors['licenceType'][] = [
                'Vehicle type must be specified for all licences'
            ];
        } else {
            if (
                $goodsOrPsv->getId() == Licence::LICENCE_CATEGORY_GOODS_VEHICLE &&
                $licenceType->getId() == Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
            ) {
                $validVehicleTypes = [
                    RefData::APP_VEHICLE_TYPE_MIXED,
                    RefData::APP_VEHICLE_TYPE_LGV
                ];

                $vehicleTypeId = $vehicleType->getId();
                if (!in_array($vehicleTypeId, $validVehicleTypes)) {
                    $errors['licenceType'][] = [
                        'Vehicle type must be either HGV or LGV when application is for Goods/Standard International'
                    ];
                } elseif (($vehicleTypeId == RefData::APP_VEHICLE_TYPE_LGV) && ($lgvDeclarationConfirmation != 1)) {
                    $errors['licenceType'][] = [
                        'LGV declaration confirmation must be ticked for Goods/Standard International/LGV'
                    ];
                }
            } else {
                $mappings = [
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE => RefData::APP_VEHICLE_TYPE_HGV,
                    Licence::LICENCE_CATEGORY_PSV => RefData::APP_VEHICLE_TYPE_PSV,
                ];

                if (
                    !isset($mappings[$goodsOrPsv->getId()]) ||
                    $mappings[$goodsOrPsv->getId()] != $vehicleType->getId()
                ) {
                    $errors['licenceType'][] = [
                        'Provided vehicle type must match the corresponding licence category value'
                    ];
                }

                if ($lgvDeclarationConfirmation != 0) {
                    $errors['licenceType'][] = [
                        'LGV declaration confirmation must only be specified for Goods/Standard International licences'
                    ];
                }
            }
        }

        if (empty($errors)) {
            return true;
        }

        throw new ValidationException($errors);
    }

    /**
     * Get application documents
     *
     * @param \Dvsa\Olcs\Api\Entity\System\Category                 $category        category
     * @param \Dvsa\Olcs\Api\Entity\System\SubCategory              $subCategory     sub category
     * @param \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre $operatingCentre operating centre
     *
     * @return ArrayCollection
     */
    public function getApplicationDocuments($category, $subCategory, $operatingCentre = null)
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();

        $criteria->where($expr->eq('category', $category));
        $criteria->andWhere(
            $expr->eq('subCategory', $subCategory)
        );
        if ($operatingCentre !== null) {
            $criteria->andWhere(
                $expr->eq('operatingCentre', $operatingCentre)
            );
        }

        return $this->documents->matching($criteria);
    }

    /**
     * Update Financial History
     *
     * @param string $bankrupt               'Y' or 'N'
     * @param string $liquidation            'Y' or 'N'
     * @param string $receivership           'Y' or 'N'
     * @param string $administration         'Y' or 'N'
     * @param string $disqualified           'Y' or 'N'
     * @param string $insolvencyDetails      Insolvency detail text
     * @param bool   $insolvencyConfirmation Insolvency Confirmation?
     *
     * @return void
     */
    public function updateFinancialHistory(
        $bankrupt,
        $liquidation,
        $receivership,
        $administration,
        $disqualified,
        $insolvencyDetails,
        $insolvencyConfirmation
    ) {
        $flags = compact('bankrupt', 'liquidation', 'receivership', 'administration', 'disqualified');

        //  can throw exception
        $this->validateFinancialHistory($flags, $insolvencyDetails);

        $this->bankrupt = $bankrupt;
        $this->liquidation = $liquidation;
        $this->receivership = $receivership;
        $this->administration = $administration;
        $this->disqualified = $disqualified;

        $this->setInsolvencyDetails($insolvencyDetails);
        if ($insolvencyConfirmation) {
            $this->setInsolvencyConfirmation('Y');
        }
    }

    /**
     * Get application operating centre by id
     *
     * @param int $aocId application operating centre
     *
     * @return ApplicationOperatingCentre
     */
    public function getApplicationOperatingCentreById($aocId)
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();

        $criteria->where($expr->eq('id', $aocId));
        return $this->operatingCentres->matching($criteria)->first();
    }

    /**
     * Validate Financial History data
     *
     * @param array  $flags             Flags
     * @param string $insolvencyDetails Details Text
     *
     * @return bool
     * @throws ValidationException
     */
    protected function validateFinancialHistory($flags, $insolvencyDetails)
    {
        $validator = new Validators\FhAdditionalInfo();
        if ($validator->isValid($insolvencyDetails, $flags)) {
            return true;
        }

        $errors = [
            'insolvencyDetails' => [
                self::ERROR_FINANCIAL_HISTORY_DETAILS_REQUIRED => 'FhAdditionalInfo.api.validation.too_short',
            ]
        ];
        throw new ValidationException($errors);
    }

    /**
     * Get other licence by type
     *
     * @param mixed $type licence type
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|Collection
     */
    public function getOtherLicencesByType(mixed $type)
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();
        $criteria->where($expr->eq('previousLicenceType', $type));

        return $this->otherLicences->matching($criteria);
    }

    /**
     * Determine whether the licence has changed within set parameters that would
     * qualify this variation to be an interim.
     *
     * @return bool
     */
    protected function hasUpgrade()
    {
        $applicationLicenceTypeId = null;
        if ($this->getLicenceType()) {
            $applicationLicenceTypeId = $this->getLicenceType()->getId();
        }

        $licenceTypeId = null;
        if ($this->getLicence()->getLicenceType()) {
            $licenceTypeId = $this->getLicence()->getLicenceType()->getId();
        }

        // If licence type has been changed from restricted to national or international.
        if (
            $licenceTypeId === Licence::LICENCE_TYPE_RESTRICTED &&
            (
                $applicationLicenceTypeId === Licence::LICENCE_TYPE_STANDARD_NATIONAL ||
                $applicationLicenceTypeId === Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
            )
        ) {
            return true;
        }

        // If licence is is updated from a standard national to an international.
        if (
            $licenceTypeId === Licence::LICENCE_TYPE_STANDARD_NATIONAL &&
            (
                $applicationLicenceTypeId === Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Has the overall number of trailers authority increased.
     *
     * @return bool
     */
    public function hasAuthTrailersIncrease()
    {
        return ($this->getTotAuthTrailers() > $this->getLicence()->getTotAuthTrailers());
    }

    /**
     * Does this variation specify an additional operating centre.
     *
     * @return bool
     */
    public function hasNewOperatingCentre()
    {
        $operatingCentres = $this->getOperatingCentres();
        /* @var $operatingCentre ApplicationOperatingCentre */
        foreach ($operatingCentres as $operatingCentre) {
            if ($operatingCentre->getAction() === ApplicationOperatingCentre::ACTION_ADD) {
                return true;
            }
        }

        return false;
    }

    /**
     * Does this variation increment an operating centres vehicles or trailers.
     *
     * @return bool
     */
    public function hasIncreaseInOperatingCentre()
    {
        $licence = [];
        $variation = [];

        // Makes dealing with the records easier.
        /* @var $lOperatingCentre \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre */
        foreach ($this->getLicence()->getOperatingCentres() as $lOperatingCentre) {
            $licence[$lOperatingCentre->getOperatingCentre()->getId()] = $lOperatingCentre;
        }

        /* @var $aOperatingCentre ApplicationOperatingCentre */
        foreach ($this->getOperatingCentres() as $aOperatingCentre) {
            $variation[$aOperatingCentre->getOperatingCentre()->getId()] = $aOperatingCentre;
        }

        // foreach of the licence op centres.
        foreach (array_keys($licence) as $operatingCenterId) {
            // If a variation record doesnt exists or its a removal op centre.
            if (
                !isset($variation[$operatingCenterId]) ||
                $variation[$operatingCenterId]->getAction() === ApplicationOperatingCentre::ACTION_DELETE
            ) {
                continue;
            }

            if (
                ($variation[$operatingCenterId]->getNoOfVehiclesRequired() >
                    $licence[$operatingCenterId]->getNoOfVehiclesRequired()) ||
                ($variation[$operatingCenterId]->getNoOfTrailersRequired() >
                    $licence[$operatingCenterId]->getNoOfTrailersRequired())
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Can this variation create an interim licence application?
     *
     * @return boolean
     */
    public function canHaveInterimLicence()
    {
        // only goods can have an interim
        if ($this->getGoodsOrPsv()->getId() !== Licence::LICENCE_CATEGORY_GOODS_VEHICLE) {
            return false;
        }

        // if its an application then it can have an interim
        if (!$this->getIsVariation()) {
            return true;
        }

        if (
            $this->hasHgvAuthorisationIncreased() ||
            $this->hasLgvAuthorisationIncreased() ||
            $this->hasAuthTrailersIncrease() ||
            $this->hasUpgrade() ||
            $this->hasNewOperatingCentre() ||
            $this->hasIncreaseInOperatingCentre()
        ) {
            return true;
        }

        return false;
    }

    /**
     * If the application involves a licence upgrade
     * from Restricted to Standard
     *
     * @return boolean
     */
    public function isLicenceUpgrade()
    {
        // only a variation can be an upgrade
        if (!$this->isVariation()) {
            return false;
        }

        $restrictedUpgrades = [
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
        ];

        return (
            $this->getLicence()->getLicenceType()->getId() === Licence::LICENCE_TYPE_RESTRICTED
            && in_array($this->getLicenceType()->getId(), $restrictedUpgrades)
        );
    }

    /**
     * Does the application involve a licence change which requires operating centre
     *
     * @return bool
     */
    public function isLicenceChangeWhichRequiresOperatingCentre(): bool
    {
        // only applies to a variation
        if (!$this->isVariation()) {
            return false;
        }

        return $this->mustHaveOperatingCentre() && !$this->getLicence()->mustHaveOperatingCentre();
    }

    /**
     * is Psv Downgrade
     *
     * @return bool
     */
    public function isPsvDowngrade()
    {
        if ($this->isGoods() || $this->isRestricted() === false) {
            return false;
        }

        return $this->getLicence()->isRestricted() === false;
    }

    /**
     * Update Licence history
     *
     * @param mixed $prevHasLicence         previous hasLicence
     * @param mixed $prevHadLicence         previous hadLicence
     * @param mixed $prevBeenRefused        previous beenRefused
     * @param mixed $prevBeenRevoked        previous beenRevoked
     * @param mixed $prevBeenAtPi           previous beenAtPi
     * @param mixed $prevBeenDisqualifiedTc previous beenDisqualifiedTc
     * @param mixed $prevPurchasedAssets    previous purchasedAssets
     *
     * @return void
     */
    public function updateLicenceHistory(
        mixed $prevHasLicence,
        mixed $prevHadLicence,
        mixed $prevBeenRefused,
        mixed $prevBeenRevoked,
        mixed $prevBeenAtPi,
        mixed $prevBeenDisqualifiedTc,
        mixed $prevPurchasedAssets
    ) {
        $this->prevHasLicence = $prevHasLicence;
        $this->prevHadLicence = $prevHadLicence;
        $this->prevBeenRefused = $prevBeenRefused;
        $this->prevBeenRevoked = $prevBeenRevoked;
        $this->prevBeenAtPi = $prevBeenAtPi;
        $this->prevBeenDisqualifiedTc = $prevBeenDisqualifiedTc;
        $this->prevPurchasedAssets = $prevPurchasedAssets;
    }

    /**
     * Get the application type
     *
     * @return int
     */
    public function getApplicationType()
    {
        if ($this->isVariation()) {
            return self::APPLICATION_TYPE_VARIATION;
        }

        return self::APPLICATION_TYPE_NEW;
    }

    /**
     * Until the application type is moved to ref data, this method aids the return of a sensible description for
     * display. Eventually there will be lots more logic around this, hence it lives here within the entity itself
     * and not tucked inside a table formatter.
     *
     * @to-do Replaced constants with ref data
     *
     * @return string
     */
    public function getApplicationTypeDescription()
    {
        if ($this->getApplicationType() === self::APPLICATION_TYPE_VARIATION) {
            return self::APPLICATION_TYPE_VARIATION_DESCRIPTION;
        }

        return self::APPLICATION_TYPE_NEW_DESCRIPTION;
    }

    /**
     * Get application date
     *
     * @return \DateTime
     */
    public function getApplicationDate()
    {
        if ($this->getReceivedDate() === null) {
            return $this->getCreatedOn();
        }

        return $this->getReceivedDate();
    }

    /**
     * Can this application be submitted
     *
     * @return bool
     */
    public function canSubmit()
    {
        return $this->getStatus()->getId() === self::APPLICATION_STATUS_NOT_SUBMITTED;
    }

    /**
     * Returns true/false depending on whether a case can be created for the application
     *
     * @return bool
     */
    public function canCreateCase()
    {
        if (
            $this->getStatus()->getId() === self::APPLICATION_STATUS_NOT_SUBMITTED
            || $this->getLicence()->getLicNo() === null
        ) {
            return false;
        }

        return true;
    }

    /**
     * Essentially an alias of getIsVariation()
     *
     * @return @boolean
     */
    public function isVariation()
    {
        return (bool)$this->getIsVariation();
    }

    /**
     * Check if the application is for a new licence
     *
     * @return bool
     */
    public function isNew()
    {
        return !$this->isVariation();
    }

    /**
     * Is Goods
     *
     * @return boolean
     */
    public function isGoods()
    {
        if ($this->getGoodsOrPsv()) {
            return $this->getGoodsOrPsv()->getId() === Licence::LICENCE_CATEGORY_GOODS_VEHICLE;
        }

        return null;
    }

    /**
     * Is Psv
     *
     * @return boolean
     */
    public function isPsv()
    {
        if ($this->getGoodsOrPsv()) {
            return $this->getGoodsOrPsv()->getId() === Licence::LICENCE_CATEGORY_PSV;
        }

        return null;
    }

    /**
     * Is Northern Ireland application
     *
     * @return boolean
     */
    public function isNi(): bool
    {
        return $this->getNiFlag() === 'Y';
    }

    /**
     * Is Special Restricted
     *
     * @return boolean
     */
    public function isSpecialRestricted()
    {
        if ($this->getLicenceType()) {
            return $this->getLicenceType()->getId() === Licence::LICENCE_TYPE_SPECIAL_RESTRICTED;
        }

        return null;
    }

    /**
     * Is Restricted
     *
     * @return boolean
     */
    public function isRestricted()
    {
        if ($this->getLicenceType() !== null) {
            return $this->getLicenceType()->getId() === Licence::LICENCE_TYPE_RESTRICTED;
        }

        return false;
    }

    /**
     * Is Standard National
     *
     * @return boolean
     */
    public function isStandardNational()
    {
        if ($this->getLicenceType() !== null) {
            return $this->getLicenceType()->getId() === Licence::LICENCE_TYPE_STANDARD_NATIONAL;
        }

        return false;
    }

    /**
     * Is Standard International
     *
     * @return boolean
     */
    public function isStandardInternational()
    {
        if ($this->getLicenceType() !== null) {
            return $this->getLicenceType()->getId() === Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL;
        }

        return false;
    }

    /**
     * Has the HGV authorisation of this variation increased relative to the associated licence? Always returns false
     * for non-variation applications as the concept does not apply to these
     *
     * @return bool
     */
    public function hasHgvAuthorisationIncreased(): bool
    {
        if (!$this->isVariation()) {
            return false;
        }

        $variationAuthorisation = $this->getTotAuthHgvVehiclesZeroCoalesced();
        $licenceAuthorisation = $this->getLicence()->getTotAuthHgvVehiclesZeroCoalesced();

        return $variationAuthorisation > $licenceAuthorisation;
    }

    /**
     * Has the LGV authorisation of this variation increased relative to the associated licence? Always returns false
     * for non-variation applications as the concept does not apply to these
     *
     * @return bool
     */
    public function hasLgvAuthorisationIncreased(): bool
    {
        if (!$this->isVariation()) {
            return false;
        }

        $variationAuthorisation = $this->getTotAuthLgvVehiclesZeroCoalesced();
        $licenceAuthorisation = $this->getLicence()->getTotAuthLgvVehiclesZeroCoalesced();

        return $variationAuthorisation > $licenceAuthorisation;
    }

    /**
     * Has the LGV authorisation of this variation changed from null on the licence to a non-null numeric value on the
     * variation?
     *
     * @return bool
     *
     * @throws RuntimeException
     */
    public function hasLgvAuthorisationChangedFromNullToNumeric(): bool
    {
        if (!$this->isVariation()) {
            throw new RuntimeException(__FUNCTION__ . ' is only applicable in a variation context');
        }

        $variationAuthorisation = $this->getTotAuthLgvVehicles();
        $licenceAuthorisation = $this->getLicence()->getTotAuthLgvVehicles();

        return !is_null($variationAuthorisation) && is_null($licenceAuthorisation);
    }

    /**
     * Has the total vehicle authorisation of this variation application increased ?
     * Always returns false for non-variation applications
     *
     * @return bool
     */
    public function hasTotalVehicleAuthorisationIncreased(): bool
    {
        if (!$this->isVariation()) {
            return false;
        }

        $variationAuthorisation = $this->getTotAuthVehicles();
        $licenceAuthorisation = $this->getLicence()->getTotAuthVehicles();

        return $variationAuthorisation > $licenceAuthorisation;
    }

    /**
     * Get Code
     *
     * @return string
     */
    public function getCode()
    {
        if ($this->isVariation()) {
            $isUpgrade = $this->isRealUpgrade();
            if ($this->isGoods()) {
                $code = $isUpgrade
                    ? self::CODE_GV_VAR_UPGRADE
                    : self::CODE_GV_VAR_NO_UPGRADE;
            } else {
                $code = $isUpgrade
                    ? self::CODE_PSV_VAR_UPGRADE
                    : self::CODE_PSV_VAR_NO_UPGRADE;
            }
        } else {
            // new app.
            if ($this->isGoods()) {
                $code = self::CODE_GV_APP;
            } else {
                if ($this->isSpecialRestricted()) {
                    $code = self::CODE_PSV_APP_SR;
                } else {
                    $code = self::CODE_PSV_APP;
                }
            }
        }

        return $code;
    }

    /**
     * Get number of remaining spaces eg difference between the total authorized vehicles and number of active vehicles
     *
     * @return int
     */
    public function getRemainingSpaces()
    {
        return $this->getTotAuthVehicles()
            - $this->getActiveLicenceVehicles()->count()
            - $this->getActiveVehicles()->count();
    }

    /**
     * Get list of active Licence Vehicles on the Licence, not including ones attached to any applications
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getActiveLicenceVehicles()
    {
        return $this->getLicence()->getActiveVehicles();
    }

    /**
     * Get count of active Licence Vehicles on the Licence, not including 46ones attached to any applications
     *
     * @return int
     */
    public function getActiveLicenceVehiclesCount()
    {
        return $this->getActiveLicenceVehicles()->count();
    }

    /**
     * Is real upgrade
     * This considers upgrade either from Restricted to Standard
     * or from Standard National To Standard International
     *
     * @return bool
     */
    public function isRealUpgrade()
    {
        if (!$this->isVariation()) {
            return false;
        }

        // If we have upgraded from restricted
        if ($this->isLicenceUpgrade()) {
            return true;
        }

        // If we have upgraded from stand nat, to stand inter
        if (
            $this->getLicence()->getLicenceType()->getId() === Licence::LICENCE_TYPE_STANDARD_NATIONAL
            && $this->getLicenceType()->getId() === Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get Oc For Inspection Request
     *
     * @return array
     */
    public function getOcForInspectionRequest()
    {
        $list = [];
        $deleted = [];

        $applicationOperatingCentres = $this->getOperatingCentres();
        /** @var Entity\Application\ApplicationOperatingCentre $applicationOperatingCentre */
        foreach ($applicationOperatingCentres as $applicationOperatingCentre) {
            $id = $applicationOperatingCentre->getOperatingCentre()->getId();
            if ($applicationOperatingCentre->getAction() !== 'D') {
                $list[$id] = $applicationOperatingCentre->getOperatingCentre();
            } else {
                $deleted[] = $id;
            }
        }

        $licenceOperatingCentres = $this->getLicence()->getOperatingCentres();
        /** @var Entity\Licence\LicenceOperatingCentre $licenceOperatingCentre */
        foreach ($licenceOperatingCentres as $licenceOperatingCentre) {
            $id = $licenceOperatingCentre->getOperatingCentre()->getId();
            if (!in_array($id, $deleted)) {
                $list[$id] = $licenceOperatingCentre->getOperatingCentre();
            }
        }

        return array_values($list);
    }

    /**
     * Get a list of sections with their completion status
     *
     * @return array|null
     */
    public function getVariationCompletion()
    {
        if (!$this->isVariation()) {
            return null;
        }

        $applicationCompletion = $this->getApplicationCompletion()->serialize();

        $completions = [];
        $converter = new CamelCaseToUnderscore();
        foreach ($applicationCompletion as $key => $value) {
            if (preg_match('/^([a-zA-Z]+)Status$/', $key, $matches)) {
                $section = strtolower((string) $converter->filter($matches[1]));
                $completions[$section] = (int)$value;
            }
        }

        return $completions;
    }

    /**
     * Determine the traffic area used for fee lookup.
     *
     * @return null|string
     */
    public function getFeeTrafficAreaId()
    {
        $trafficArea = $this->getLicence()->getTrafficArea();

        if (!is_null($trafficArea)) {
            return $trafficArea->getId();
        }

        if ($this->getNiFlag() === 'Y') {
            return TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE;
        }

        return null;
    }

    /**
     * Has Variation Changes
     *
     * @return bool
     */
    public function hasVariationChanges()
    {
        $completion = $this->getApplicationCompletion();

        $data = $completion->serialize([]);

        foreach ($data as $key => $value) {
            if (preg_match('/^([a-zA-Z]+)Status$/', $key) && (int)$value !== self::VARIATION_STATUS_UNCHANGED) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get List of section are required attentions
     *
     * @return array
     */
    public function getSectionsRequiringAttention()
    {
        $completion = $this->getApplicationCompletion();
        $data = $completion->serialize();

        $sections = [];

        $filter = new CamelCaseToUnderscore();

        foreach ($data as $key => $value) {
            if (!preg_match('/^([a-zA-Z]+)Status$/', $key, $matches)) {
                continue;
            }

            $section = strtolower((string) $filter->filter($matches[1]));

            if ($this->isSectionRequireAttention($section, (int)$value)) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    /**
     * Contains rules for defining is section required attention or not
     *
     * @param string $section Section key
     * @param int    $value   Flag value
     *
     * @return bool
     */
    private function isSectionRequireAttention($section, $value)
    {
        //  after submit App/Var on internal side, section "Review and declarations" at selfserve section
        //  stay unreachable and user can't change status, so it should be ignored
        if (
            $section === ApplicationCompletion::SECTION_DECLARATION
            && $this->getStatus()->getId() !== Application::APPLICATION_STATUS_NOT_SUBMITTED
            && $this->getAuthSignature() === true
        ) {
            return false;
        }

        return ($value === self::VARIATION_STATUS_REQUIRES_ATTENTION);
    }

    /**
     * Get active vehicle list on the application
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getActiveVehicles()
    {
        $criteria = Criteria::create();
        $criteria->andWhere(
            $criteria->expr()->isNull('removalDate')
        );

        return $this->getLicenceVehicles()->matching($criteria);
    }

    /**
     * Copy information from a licence to this application
     *
     * @param Licence $licence Licence to copy from
     *
     * @return void
     */
    public function copyInformationFromLicence(Licence $licence)
    {
        $this->setLicenceType($licence->getLicenceType());
        $this->setGoodsOrPsv($licence->getGoodsOrPsv());
        $this->setVehicleType($licence->getVehicleType());
        $this->setLgvDeclarationConfirmation($licence->getLgvDeclarationConfirmation());
        $this->setTotAuthTrailers($licence->getTotAuthTrailers());
        $this->setTotAuthVehicles($licence->getTotAuthVehicles());
        $this->setTotAuthHgvVehicles($licence->getTotAuthHgvVehicles());
        $this->setTotAuthLgvVehicles($licence->getTotAuthLgvVehicles());
        $this->setNiFlag($licence->getNiFlag());
    }

    /**
     * Should Deltas be used in the people section
     *
     * @return boolean
     */
    public function useDeltasInPeopleSection()
    {
        // if application/variation organisation is sole trader or partnership
        if (
            $this->getLicence()->getOrganisation()->isSoleTrader() ||
            $this->getLicence()->getOrganisation()->isPartnership()
        ) {
            return false;
        }

        // if is an application AND no current ApplicationOrganisationUsers AND no inforce licences
        if (
            !$this->getIsVariation() &&
            $this->getApplicationOrganisationPersons()->count() === 0 &&
            !$this->getLicence()->getOrganisation()->hasInforceLicences()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Get current Interim status
     *
     * @return null|string Refdata ID
     */
    public function getCurrentInterimStatus()
    {
        $currentStatus = $this->getInterimStatus();
        return $currentStatus !== null ? $currentStatus->getId() : null;
    }

    /**
     * Get the Out Of Representation Date
     *
     * @return DateTime|string DateTime if date can be calculated, otherwise a string const NOT_APPLICABLE or UNKNOWN
     */
    public function getOutOfRepresentationDate()
    {
        // out of representation date not applicable to psv or lgv
        $notApplicableVehicleTypes = [
            RefData::APP_VEHICLE_TYPE_PSV,
            RefData::APP_VEHICLE_TYPE_LGV,
        ];

        if (in_array($this->vehicleType->getId(), $notApplicableVehicleTypes)) {
            return self::NOT_APPLICABLE;
        }

        $updatedAddedOperatingCentres = 0;
        /* @var Entity\Application\ApplicationOperatingCentre $aoc */
        foreach ($this->getOperatingCentres() as $aoc) {
            if ($aoc->getAction() === 'A' || $aoc->getAction() === 'U') {
                $updatedAddedOperatingCentres++;
            }
        }

        // If a new goods application and if 0 operating centres have been added/updated then
        if (!$this->isVariation() && $updatedAddedOperatingCentres === 0) {
            return self::UNKNOWN;
        }

        // if a goods variation and 0 operating centres have been added/updated then
        if ($this->isVariation() && $updatedAddedOperatingCentres === 0) {
            return self::NOT_APPLICABLE;
        }

        // If a goods new/variation application and operating centres have been added/udpated:
        /* @var Entity\Application\ApplicationOperatingCentre $aoc */
        $maximumDate = null;
        foreach ($this->getOperatingCentres() as $aoc) {
            // deleted O/C are not considered within the OOR date
            if ($aoc->getAction() === 'D') {
                continue;
            }

            $operatingCentreOorDate = $this->calcOperatingCentreOutOfRepresentationDate($aoc);

            // If 1 or more of the operating centres are 'Unknown' then the overall OOR date = 'Unknown'
            if ($operatingCentreOorDate === self::UNKNOWN) {
                return self::UNKNOWN;
            }

            if ($operatingCentreOorDate === self::NOT_APPLICABLE) {
                continue;
            }

            // store the maximum (newest) date
            $ocDate = new DateTime($operatingCentreOorDate);
            if ($ocDate > $maximumDate) {
                $maximumDate = $ocDate;
            }
        }

        // If all the operating centres are 'Not applicable' then the overall OOR date = 'Not applicable'
        if ($maximumDate === null) {
            return self::NOT_APPLICABLE;
        }

        // Otherwise = <maximum date> = 21 days
        return $maximumDate->modify(self::OUT_OF_REPRESENTATION_DATE_INTERVAL);
    }

    /**
     * Calculate the Out of Representation date for an ApplicationOperatingCentre
     * If a date can be calcuated this will return a string date (YYYY-MM-DD)
     * If a date cannot be calculated it will return a string of either self::NOT_APPLICABLE or self::UNKNOWN
     *
     * @param Entity\Application\ApplicationOperatingCentre $aoc Application Operation Center
     *
     * @return string date|self::NOT_APPLICABLE|self::UNKNOWN
     */
    private function calcOperatingCentreOutOfRepresentationDate(ApplicationOperatingCentre $aoc)
    {
        // For added operating centres that are linked to a schedule 4
        // where there has been no increase to the vehicles as compared with the donor licence then
        if ($aoc->getAction() === 'A' && $aoc->getS4()) {
            $donorOperatingCentres = $aoc->getS4()->getLicence()->getOperatingCentres();
            /* @var $donorOperatingCentre \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre */
            foreach ($donorOperatingCentres as $donorOperatingCentre) {
                if ($donorOperatingCentre->getOperatingCentre() === $aoc->getOperatingCentre()) {
                    if ($aoc->getNoOfVehiclesRequired() <= $donorOperatingCentre->getNoOfVehiclesRequired()) {
                        return self::NOT_APPLICABLE;
                    }
                }
            }
        } elseif ($aoc->getAction() === 'U') {
            // For updated operating centres, if there has been no increase to the vehicles
            $licenceOperatingCentres = $this->getLicence()->getOperatingCentres();

            /* @var Entity\Licence\LicenceOperatingCentre $licOc */
            foreach ($licenceOperatingCentres as $licOc) {
                if ($licOc->getOperatingCentre() !== $aoc->getOperatingCentre()) {
                    continue;
                }

                if (
                    $aoc->getNoOfVehiclesRequired() <= $licOc->getNoOfVehiclesRequired()
                    && $aoc->getNoOfTrailersRequired() <= $licOc->getNoOfTrailersRequired()
                ) {
                    return self::NOT_APPLICABLE;
                }
            }
        }

        // If there is an advertisement date then
        if ($aoc->getAdPlaced() === ApplicationOperatingCentre::AD_UPLOAD_NOW && $aoc->getAdPlacedDate()) {
            return $aoc->getAdPlacedDate();
        }

        // If the advertisement date is missing then
        return self::UNKNOWN;
    }

    /**
     * Get the Out Of Opposition Date
     *
     * @return DateTime|string DateTime if it can be calculated, otherwise const UNKNOWN
     */
    public function getOutOfOppositionDate()
    {
        // It is a PSV variation;
        if ($this->isPsv() && $this->isVariation()) {
            return self::NOT_APPLICABLE;
        }

        // if not publishable NOT APPLICABLE
        if (!$this->isPublishable()) {
            return self::NOT_APPLICABLE;
        }

        // if S41 AND true AND approved AND without licence upgrade then NA
        if ($this->hasApprovedTrueS4() && !$this->isRealUpgrade()) {
            return self::NOT_APPLICABLE;
        }

        if ($this->isGoods() && $this->isVariation()) {
            // It is a goods variation and 0 operating centres have been added; AND
            // It is a goods variation and 0 operating centres have been updated with an increase
            // of vehicles or trailers
            if (
                $this->getOperatingCentresAdded()->count() === 0 &&
                !$this->hasIncreaseInOperatingCentre() &&
                !$this->isRealUpgrade() &&
                !$this->hasHgvAuthorisationIncreased() &&
                !$this->hasLgvAuthorisationIncreased() &&
                !$this->hasAuthTrailersIncrease()
            ) {
                return self::NOT_APPLICABLE;
            }
        }

        /** @var PublicationEntity $latestPublication */
        $latestPublication = $this->getLatestPublication();

        if (!empty($latestPublication)) {
            $oooDate = new DateTime($latestPublication->getPubDate());
            $oooDate->modify(self::OUT_OF_OPPOSITION_DATE_INTERVAL);

            return $oooDate;
        }

        return self::UNKNOWN;
    }

    /**
     * Method to return ooo as strings. Used in submission tables
     *
     * @return string
     */
    public function getOutOfOppositionDateAsString()
    {
        $ooo = $this->getOutOfOppositionDate();
        return $this->formatDateToString($ooo);
    }

    /**
     * Method to return oor as strings. Used in submission tables
     *
     * @return string
     */
    public function getOutOfRepresentationDateAsString()
    {
        $oor = $this->getOutOfRepresentationDate();
        return $this->formatDateToString($oor);
    }

    /**
     * Format Date To String
     *
     * @param mixed  $date   Data
     * @param string $format Format
     *
     * @return string
     */
    private function formatDateToString(mixed $date, $format = self::DATE_FORMAT)
    {
        if (is_string($date)) {
            return $date;
        }
        if ($date instanceof \DateTime) {
            return $date->format($format);
        }
        return '';
    }

    /**
     * Get a collection of Application Operating Centres that have been added
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getOperatingCentresAdded()
    {
        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('action', 'A'));

        return $this->getOperatingCentres()->matching($criteria);
    }

    /**
     * Gets the latest publication for an application. (used to calculate OOO date)
     *
     * @return PublicationEntity|null
     */
    private function getLatestPublication()
    {
        $publicationSections = [
            \Dvsa\Olcs\Api\Entity\Publication\PublicationSection::APP_NEW_SECTION,
            \Dvsa\Olcs\Api\Entity\Publication\PublicationSection::VAR_NEW_SECTION,
            \Dvsa\Olcs\Api\Entity\Publication\PublicationSection::SCHEDULE_4_NEW,
            \Dvsa\Olcs\Api\Entity\Publication\PublicationSection::SCHEDULE_4_UNTRUE,
            \Dvsa\Olcs\Api\Entity\Publication\PublicationSection::SCHEDULE_1_NI_NEW,
            \Dvsa\Olcs\Api\Entity\Publication\PublicationSection::SCHEDULE_1_NI_UNTRUE,
        ];

        $latestPublication = null;
        foreach ($this->getPublicationLinks() as $publicationLink) {
            /* @var $publicationLink \Dvsa\Olcs\Api\Entity\Publication\PublicationLink */
            if (!in_array($publicationLink->getPublicationSection()->getId(), $publicationSections)) {
                continue;
            }
            /** @var PublicationEntity $latestPublication */
            if ($latestPublication === null) {
                $latestPublication = $publicationLink->getPublication();
            } elseif (
                new \DateTime($publicationLink->getPublication()->getPubDate()) >
                new \DateTime($latestPublication->getPubDate())
            ) {
                $latestPublication = $publicationLink->getPublication();
            }
        }

        return $latestPublication;
    }

    /**
     * Get a list of active S4's
     *
     * @return array of S4 Entities
     */
    public function getActiveS4s()
    {
        $activeS4s = [];

        /** @var S4 $s4 */
        foreach ($this->getS4s() as $s4) {
            if ($s4->getOutcome() === null) {
                $activeS4s[] = $s4;
            } elseif ($s4->getOutcome()->getId() === S4::STATUS_APPROVED) {
                $activeS4s[] = $s4;
            }
        }

        return $activeS4s;
    }

    /**
     * Does this application have an active S4 attached
     *
     * @return bool
     */
    public function hasActiveS4()
    {
        return count($this->getActiveS4s()) > 0;
    }

    /**
     * Does this application have any approved true S4s
     *
     * @return bool
     */
    public function hasApprovedTrueS4()
    {
        /* @var $s4 S4 */
        foreach ($this->getS4s() as $s4) {
            if ($s4->getOutcome() && $s4->getOutcome()->getId() === S4::STATUS_APPROVED && $s4->getIsTrueS4() === 'Y') {
                return true;
            }
        }

        return false;
    }

    /**
     * Can this application have community licences
     *
     * @return bool
     */
    public function canHaveCommunityLicences()
    {
        return ($this->isStandardInternational() || ($this->isPsv() && $this->isRestricted()));
    }

    /**
     * Get a list of operating centre that are on the application
     *
     * @param OperatingCentre $oc Operating centre to look for
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getDeltaAocByOc(OperatingCentre $oc)
    {
        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('operatingCentre', $oc));

        return $this->getOperatingCentres()->matching($criteria);
    }

    /**
     * Get the lic no prefix eg 'P' or 'O'
     *
     * @return string
     */
    public function getCategoryPrefix()
    {
        return LicenceNoGen::getCategoryPrefix($this->getGoodsOrPsv());
    }

    /**
     * Get publication number
     *
     * @return int
     */
    public function getPublicationNo()
    {
        return $this->publicationNo;
    }

    /**
     * Set publication number
     *
     * @param int $publicationNo Publication number
     *
     * @return void
     */
    public function setPublicationNo($publicationNo)
    {
        $this->publicationNo = $publicationNo;
    }

    /**
     * Get Out Of Opposition Date
     *
     * @return string
     */
    public function getOooDate()
    {
        return $this->oooDate;
    }

    /**
     * Set Out Of Opposition Date
     *
     * @param string $oooDate Date
     *
     * @return $this
     */
    public function setOooDate($oooDate)
    {
        $this->oooDate = $oooDate;
        return $this;
    }

    /**
     * Get Out Of Representation Date
     *
     * @return string
     */
    public function getOorDate()
    {
        return $this->oorDate;
    }

    /**
     * Set Out Of Representation Date
     *
     * @param string $oorDate Date
     *
     * @return $this
     */
    public function setOorDate($oorDate)
    {
        $this->oorDate = $oorDate;
        return $this;
    }

    /**
     * Get Is Opposed
     *
     * @return boolean
     */
    public function getIsOpposed()
    {
        return $this->isOpposed;
    }

    /**
     * Set Is Opposed
     *
     * @param boolean $isOpposed Is opposed
     *
     * @return $this
     */
    public function setIsOpposed($isOpposed)
    {
        $this->isOpposed = $isOpposed;
        return $this;
    }

    /**
     * Get Published Date
     *
     * @return string
     */
    public function getPublishedDate()
    {
        return $this->publishedDate;
    }

    /**
     * Set Published Date
     *
     * @param string $publishedDate Publish date
     *
     * @return $this
     */
    public function setPublishedDate($publishedDate)
    {
        $this->publishedDate = $publishedDate;
        return $this;
    }

    /**
     * Determine and set the latest publication number
     *
     * @return mixed
     */
    public function determinePublicationNo()
    {
        /** @var PublicationEntity $latestPublication */
        $latestPublication = $this->getLatestPublication();

        if ($latestPublication instanceof PublicationEntity) {
            return $latestPublication->getPublicationNo();
        }

        return null;
    }

    /**
     * Determine and set the latest publication number
     *
     * @return mixed
     */
    public function determinePublishedDate()
    {
        /** @var PublicationEntity $latestPublication */
        $latestPublication = $this->getLatestPublication();

        if ($latestPublication instanceof PublicationEntity) {
            return $latestPublication->getPubDate();
        }

        return null;
    }

    /**
     * Has this application received any opposition
     *
     * @return bool
     */
    public function hasOpposition()
    {
        /** @var CasesEntity $case */
        foreach ($this->getCases() as $case) {
            if (count($case->getOppositions()) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * For an application, get the organisation's other licences (explicity
     * excludes the current application's licence)
     *
     * @note different from AbstractApplication::getOtherLicences() which is,
     * erm, something else entirely
     *
     * @return array Licence[]
     */
    public function getOtherActiveLicencesForOrganisation()
    {
        if ($this->getLicence() && $this->getLicence()->getOrganisation()) {
            $licences = $this->getActiveLicencesForOrganisation();

            if (empty($licences)) {
                return [];
            }

            $filtered = array_filter(
                $licences->toArray(),
                fn($licence) => $licence->getId() !== $this->getLicence()->getId()
            );

            return array_values($filtered);
        }

        return null;
    }

    /**
     * For an application, get the organisation's active licences
     *
     * @return ArrayCollection
     */
    public function getActiveLicencesForOrganisation()
    {
        return $this->getLicence()->getOrganisation()->getActiveLicences();
    }

    /**
     * Get Traffic Area
     *
     * @return TrafficArea
     */
    public function getTrafficArea()
    {
        return $this->getLicence()->getTrafficArea();
    }

    /**
     * Get Operating Centres Net Delta
     *
     * @return int
     */
    public function getOperatingCentresNetDelta()
    {
        $delta = 0;

        if (!empty($this->getOperatingCentres())) {
            foreach ($this->getOperatingCentres() as $aoc) {
                switch ($aoc->getAction()) {
                    case 'A':
                        $delta++;
                        break;
                    case 'D':
                        $delta--;
                        break;
                }
            }
        }

        return $delta;
    }

    /**
     * Set the target completion date to +8 weeks from received date
     *
     * @return $this
     */
    public function setTargetCompletionDateFromReceivedDate()
    {
        $received = $this->getReceivedDate();
        $target = clone $received;
        $target->modify(self::TARGET_COMPLETION_TIME);
        $this->setTargetCompletionDate($target);
        return $this;
    }

    /**
     * Has Allow Fee Payments
     *
     * @return bool
     */
    public function allowFeePayments()
    {
        if (
            in_array(
                $this->getStatus()->getId(),
                [
                self::APPLICATION_STATUS_REFUSED,
                self::APPLICATION_STATUS_WITHDRAWN,
                self::APPLICATION_STATUS_NOT_TAKEN_UP,
                ]
            )
        ) {
            return false;
        }

        return $this->getLicence()->allowFeePayments();
    }

    /**
     * Has application changed vehicle or trailer authorisation
     *
     * @return bool
     */
    public function hasAuthChanged()
    {
        if ($this->isNew()) {
            return false;
        }

        $comparisons = [
            'TotAuthHgvVehicles',
            'TotAuthLgvVehicles',
            'TotAuthTrailers',
        ];

        foreach ($comparisons as $comparison) {
            if ((int)$this->{'get' . $comparison}() !== (int)$this->getLicence()->{'get' . $comparison}()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Is Application has Under Consideration status
     *
     * @return bool
     */
    public function isUnderConsideration()
    {
        return !is_null($this->getStatus())
            && $this->getStatus()->getId() === self::APPLICATION_STATUS_UNDER_CONSIDERATION;
    }

    /**
     * Is Application has Not Submitted
     *
     * @return bool
     */
    public function isNotSubmitted()
    {
        return (string)$this->getStatus() === self::APPLICATION_STATUS_NOT_SUBMITTED;
    }

    /**
     * Get the Shortcode version of a licence type
     *
     * @return string|null if licence type is not set or shortcode does not exist
     */
    public function getLicenceTypeShortCode()
    {
        $shortCodes = [
            'ltyp_r' => 'R',
            'ltyp_si' => 'SI',
            'ltyp_sn' => 'SN',
            'ltyp_sr' => 'SR',
        ];

        if ($this->getLicenceType() === null || !isset($shortCodes[$this->getLicenceType()->getId()])) {
            return null;
        }

        return $shortCodes[$this->getLicenceType()->getId()];
    }

    /**
     * Get Context Value
     *
     * @return string
     */
    public function getContextValue()
    {
        return $this->getLicence()->getLicNo();
    }

    /**
     * Get a list of open tasks attached to the application, optionally filtered by category, sub category
     *
     * @param int $categoryId    Category ID
     * @param int $subCategoryId Sub category ID, null means all
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getOpenTasksForCategory($categoryId, $subCategoryId = null)
    {
        // use Criteria to retrieve open tasks
        $criteria = Criteria::create()->where(Criteria::expr()->eq('isClosed', 'N'));
        $openTasks = $this->getTasks()->matching($criteria);

        $tasks = new \Doctrine\Common\Collections\ArrayCollection();

        // iterate to get tasks of category, subcategory
        // NB reason to iterate is crtieria should only be used with scalar values
        /** @var Entity\Task\Task $task */
        foreach ($openTasks as $task) {
            if ($task->getCategory()->getId() !== $categoryId) {
                continue;
            }
            if ($subCategoryId !== null && $task->getSubCategory()->getId() !== $subCategoryId) {
                continue;
            }
            $tasks->add($task);
        }

        return $tasks;
    }

    /**
     * Is this application publishable
     *
     * @return boolean
     */
    public function isPublishable()
    {
        if ($this->isNew()) {
            // It is a new Goods or PSV application
            // Excluding PSV special restricted
            if ($this->isPsv() && $this->isSpecialRestricted()) {
                return false;
            }

            return true;
        } else {
            // It is a Goods or PSV variation
            // The LGV authorisation has increased (only returns true for licence types concerned with LGV)
            if ($this->hasLgvAuthorisationIncreased()) {
                return true;
            }

            // The HGV authorisation has increased (goods licence only)
            if ($this->hasHgvAuthorisationIncreased() && $this->isGoods()) {
                return true;
            }

            // The trailer authorisation has increased
            if ($this->hasAuthTrailersIncrease()) {
                return true;
            }

            // An operating centre has been added;
            if ($this->hasNewOperatingCentre()) {
                return true;
            }

            // An operating centre has been updated and either the OC vehicles or trailers have increased;
            if ($this->hasIncreaseInOperatingCentre()) {
                return true;
            }

            // A major upgrade has taken place
            // (ie restricted to standard national or restricted to standard international)
            if ($this->isRealUpgrade()) {
                return true;
            }

            // if condition/undertaking changes
            if ($this->getConditionUndertakings()->count() > 0) {
                return true;
            }

            return false;
        }
    }

    /**
     * If the application has been previously published
     *
     * @return boolean
     */
    public function isPreviouslyPublished()
    {
        $linkTypes = [
            PublicationSectionEntity::APP_NEW_SECTION,
            PublicationSectionEntity::VAR_NEW_SECTION,
            PublicationSectionEntity::APP_GRANTED_SECTION,
            PublicationSectionEntity::VAR_GRANTED_SECTION,
            PublicationSectionEntity::SCHEDULE_4_NEW,
            PublicationSectionEntity::SCHEDULE_4_UNTRUE,
            PublicationSectionEntity::SCHEDULE_4_TRUE,
            PublicationSectionEntity::SCHEDULE_1_NI_NEW,
            PublicationSectionEntity::SCHEDULE_1_NI_UNTRUE,
            PublicationSectionEntity::SCHEDULE_1_NI_TRUE
        ];

        $publicationLinks = $this->getPublicationLinks()->filter(
            fn($element) => in_array((string)$element->getPublicationSection(), $linkTypes)
        );

        return ($publicationLinks->count() > 0);
    }

    /**
     * @see AbstractUpdateStatus
     *
     * accepts a string from the old application completion code which uses the database,
     * and returns whether the given section is completed.
     *
     * This is intended as a step towards removing the application_completion database tables entirely
     */
    public function isSectionCompleted(string $applicationSection): bool
    {
        return match ($applicationSection) {
            'VehiclesSize' => $this->psvWhichVehicleSizes !== null,
            'PsvOperateLarge' => $this->psvNoSmallVhlConfirmation !== null,
            'PsvOperateSmall' => $this->psvOperateSmallVhl !== null,
            'PsvOperateNovelty' => $this->psvNoveltyVehiclesCompleted(),
            'PsvSmallConditions' => $this->psvSmallVhlConfirmation !== null,
            'PsvMainOccupationUndertakings' => $this->isMainOccupationUndertakingsSectionCompleted(),
            'PsvSmallPartWritten' => $this->isWrittenEvidenceSectionCompleted(),
            'PsvDocumentaryEvidenceSmall' => $this->smallVehicleEvidenceUploaded !== null,
            'PsvDocumentaryEvidenceLarge' => $this->occupationEvidenceUploaded !== null,
            default => throw new \RuntimeException('There is no validation for this section: ' . $applicationSection),
        };
    }

    public function updatePsvVehicleSize(RefData $vehicleSize): self
    {
        $size = $vehicleSize->getId();

        if ($this->psvWhichVehicleSizes !== null) {
            $existingSize = $this->psvWhichVehicleSizes->getId();

            if ($existingSize !== $size) {
                $this->updateFollowingVehicleSizeChange($size);
            }
        }

        switch($size) {
            case self::PSV_VEHICLE_SIZE_SMALL:
                $this->psvOperateSmallVhl = 'Y';
                break;
            case self::PSV_VEHICLE_SIZE_MEDIUM_LARGE:
                $this->psvOperateSmallVhl = 'N';
                break;
            default:
                $this->psvOperateSmallVhl = null;
        }

        $this->psvWhichVehicleSizes = $vehicleSize;

        return $this;
    }

    /**
     * if the vehicle size has changed, reset the following fields
     */
    public function updateFollowingVehicleSizeChange(string $size): self
    {
        $this->psvOccupationRecordsConfirmation = null;
        $this->psvIncomeRecordsConfirmation = null;
        $this->psvSmallVhlNotes = null;
        $this->psvTotalVehicleSmall = null;
        $this->psvTotalVehicleLarge = null;
        $this->psvNoSmallVhlConfirmation = null;
        $this->psvSmallVhlConfirmation = null;
        $this->psvOnlyLimousinesConfirmation = null;
        $this->psvNoLimousineConfirmation = null;
        $this->psvLimousines = null;
        $this->smallVehicleEvidenceUploaded = null;
        $this->occupationEvidenceUploaded = null;

        if ($this->isNew()) {
            $this->applicationCompletion->clearVehiclesSizeSectionsForApplication();
        }

        return $this;
    }

    public function psvNoveltyVehiclesCompleted(): bool
    {
        //must have at least answered yes or no
        if ($this->psvLimousines === null) {
            return false;
        }

        if ($this->psvLimousines === 'Y') {
            //for small vehicles we don't always ask further questions
            if ($this->psvWhichVehicleSizes instanceof RefData
                && $this->psvWhichVehicleSizes->getId() === self::PSV_VEHICLE_SIZE_SMALL
            ) {
                return true;
            }

            if ($this->psvOnlyLimousinesConfirmation !== null) {
                return true;
            }
        }

        if ($this->psvLimousines === 'N' && $this->psvNoLimousineConfirmation !== null) {
            return true;
        }

        return false;
    }

    public function updatePsvNoveltyVehicles(
        string $usingLimousines,
        ?string $psvNoLimousineConfirmation,
        ?string $psvOnlyLimousineConfirmation
    ): self {
        $this->psvLimousines = $usingLimousines;
        $this->psvNoLimousineConfirmation = $psvNoLimousineConfirmation;
        $this->psvOnlyLimousinesConfirmation = $psvOnlyLimousineConfirmation;

        return $this;
    }

    public function isMainOccupationUndertakingsSectionCompleted(): bool
    {
        return $this->psvOccupationRecordsConfirmation !== null && $this->psvIncomeRecordsConfirmation !== null;
    }

    public function updateMainOccupationUndertakings(
        string $psvOccupationRecordsConfirmation,
        string $psvIncomeRecordsConfirmation
    ): self {
        $this->psvOccupationRecordsConfirmation = $psvOccupationRecordsConfirmation;
        $this->psvIncomeRecordsConfirmation = $psvIncomeRecordsConfirmation;

        return $this;
    }

    public function isWrittenEvidenceSectionCompleted(): bool
    {
        return $this->psvSmallVhlNotes !== null
            && $this->psvTotalVehicleSmall !== null
            && $this->psvTotalVehicleLarge !== null;
    }

    public function updateWrittenEvidence(
        string $psvSmallVhlNotes,
        int $psvTotalVehicleSmall,
        int $psvTotalVehicleLarge
    ): self {
        $this->psvSmallVhlNotes = $psvSmallVhlNotes;
        $this->psvTotalVehicleSmall = $psvTotalVehicleSmall;
        $this->psvTotalVehicleLarge = $psvTotalVehicleLarge;

        return $this;
    }

    public function isOperatingSmallPsvAsPartOfLarge(): bool
    {
        return $this->isPsvVehicleSizeBoth() && $this->psvOperateSmallVhl === 'Y';
    }

    public function isPsvBothNotOperatingSmallPsvAsPartOfLarge(): bool
    {
        return $this->isPsvVehicleSizeBoth() && $this->psvOperateSmallVhl === 'N';
    }

    /**
     * Is the PSV Size set to Small
     *
     * @return bool
     */
    public function isPsvVehicleSizeSmall()
    {
        return !empty($this->getPsvWhichVehicleSizes()) &&
            $this->getPsvWhichVehicleSizes()->getId() === self::PSV_VEHICLE_SIZE_SMALL;
    }

    /**
     * Is the PSV Size set to Medium/Large
     *
     * @return bool
     */
    public function isPsvVehicleSizeMediumLarge()
    {
        return !empty($this->getPsvWhichVehicleSizes()) &&
            $this->getPsvWhichVehicleSizes()->getId() === self::PSV_VEHICLE_SIZE_MEDIUM_LARGE;
    }

    /**
     * Is the PSV Size set to Both
     *
     * @return bool
     */
    public function isPsvVehicleSizeBoth()
    {
        return !empty($this->getPsvWhichVehicleSizes()) &&
            $this->getPsvWhichVehicleSizes()->getId() === self::PSV_VEHICLE_SIZE_BOTH;
    }

    /**
     * Get Latest Outstanding Application Fee
     *
     * @return FeeEntity|null
     */
    public function getLatestOutstandingApplicationFee()
    {
        if ($this->isVariation()) {
            $feeTypeFeeTypeId = FeeTypeEntity::FEE_TYPE_VAR;
        } else {
            $feeTypeFeeTypeId = FeeTypeEntity::FEE_TYPE_APP;
        }

        $criteria = Criteria::create()
            ->orderBy(['invoicedDate' => Criteria::DESC]);

        /** @var Entity\Fee\Fee $fee */
        foreach ($this->getFees()->matching($criteria) as $fee) {
            if (
                $fee->isOutstanding()
                && $fee->getFeeType()->getFeeType()->getId() === $feeTypeFeeTypeId
            ) {
                return $fee;
            }
        }

        return null;
    }

    /**
     * Get Latest Outstanding Interim Fee
     *
     * @return FeeEntity|null
     */
    public function getLatestOutstandingInterimFee()
    {
        $criteria = Criteria::create()
            ->orderBy(['invoicedDate' => Criteria::DESC]);

        /** @var Entity\Fee\Fee $fee */
        foreach ($this->getFees()->matching($criteria) as $fee) {
            if (
                $fee->isOutstanding()
                && $fee->getFeeType()->getFeeType()->getId() === FeeTypeEntity::FEE_TYPE_GRANTINT
            ) {
                return $fee;
            }
        }

        return null;
    }

    /**
     * Has Outstanding Grant Fee
     *
     * @return bool
     */
    public function hasOutstandingGrantFee()
    {
        return !empty($this->getOutstandingGrantFees());
    }

    /**
     * Get Outstanding Grant Fees
     *
     * @return array
     */
    public function getOutstandingGrantFees()
    {
        $grantFees = [];

        /** @var Entity\Fee\Fee $fee */
        foreach ($this->getFees() ?? [] as $fee) {
            if ($fee->isGrantFee() && $fee->isOutstanding()) {
                $grantFees[$fee->getId()] = $fee;
            }
        }

        return $grantFees;
    }

    /**
     * Use the applied_via value to determine whether an application was
     * created internally or via selfserve
     *
     * @return bool|null
     */
    public function createdInternally()
    {
        if ($this->getAppliedVia()) {
            return !($this->getAppliedVia()->getId() === self::APPLIED_VIA_SELFSERVE);
        }

        return null;
    }

    /**
     * Has Application Fee
     *
     * @return bool whether the application already has a fee (that is not cancelled)
     */
    public function hasApplicationFee()
    {
        /** @var Entity\Fee\Fee $fee */
        foreach ($this->getFees() as $fee) {
            if (
                ($fee->isNewApplicationFee() || $fee->isVariationFee())
                && !$fee->isCancelled()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the application reference number.
     *
     * @return string eg OB12345/12
     */
    public function getApplicationReference()
    {
        if ($this->getLicence() && !empty($this->getLicence()->getLicNo())) {
            $applicationReference = $this->getLicence()->getLicNo() . '/' . $this->getId();
        } else {
            $applicationReference = $this->getId();
        }

        return $applicationReference;
    }

    /**
     * Calculated values to be added to a bundle
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        return [
            'applicationReference' => $this->getApplicationReference(),
            'awaitingGrantFeeId' => array_key_first($this->getOutstandingGrantFees() ?? []),
        ];
    }

    /**
     * Get the organisation
     *
     * @return Entity\Organisation\Organisation
     */
    public function getRelatedOrganisation()
    {
        return $this->getLicence()->getOrganisation();
    }

    /**
     * Updates the digital signature for the application, runs as a side effect of govuk signin
     */
    public function updateDigitalSignature(RefData $signatureType, DigitalSignature $signature): void
    {
        $this->signatureType = $signatureType;
        $this->digitalSignature = $signature;
    }

    /**
     * Is this application digitally signed
     *
     * @return bool
     */
    public function isDigitallySigned()
    {
        return (string)$this->getSignatureType() === self::SIG_DIGITAL_SIGNATURE
            && $this->getDigitalSignature() !== null;
    }

    /**
     * Can financial evidence be added?
     *
     * @return bool
     */
    public function canAddFinancialEvidence()
    {
        // The financial tracking section is NOT set to 'Accepted' or 'Not applicable'
        // (i.e. application_tracking.financial_evidence_status <> 1 or 3); AND
        $applicationTracking = $this->getApplicationTracking()->getFinancialEvidenceStatus();
        if (
            $applicationTracking === ApplicationTracking::STATUS_ACCEPTED
            || $applicationTracking === ApplicationTracking::STATUS_NOT_APPLICABLE
        ) {
            return false;
        }

        // The financial evidence uploader is set to 'Upload later' or 'Send in the post'
        // (i.e application.financial_evidence_uploaded = 0 or 2); AND
        if (
            $this->getFinancialEvidenceUploaded() !== Application::FINANCIAL_EVIDENCE_SEND_IN_POST
            && $this->getFinancialEvidenceUploaded() !== Application::FINANCIAL_EVIDENCE_UPLOAD_LATER
        ) {
            return false;
        }

        // The licence type is NOT special restricted; AND
        if ((string)$this->getLicenceType() == Licence::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            return false;
        }

        // It is a new application OR it is a variation where the 'Financial evidence' section has been updated
        $applicationCompletion = $this->getApplicationCompletion()->getFinancialEvidenceStatus();
        if (
            $this->isVariation()
            && $applicationCompletion !== ApplicationCompletion::STATUS_VARIATION_UPDATED
        ) {
            return false;
        }

        return true;
    }

    public function canAddPsvSmallEvidence()
    {
        // This only applies to PSV licences that are not special restricted
        if (!$this->isPsv() || $this->isSpecialRestricted()) {
            return false;
        }

        // If user didn't select to upload later, it can't be added now
        if ($this->smallVehicleEvidenceUploaded !== self::FINANCIAL_EVIDENCE_UPLOAD_LATER) {
            return false;
        }

        // The tracking section is NOT set to 'Accepted' or 'Not applicable'
        $applicationTracking = $this->applicationTracking->getPsvDocumentaryEvidenceSmallStatus();
        if (
            $applicationTracking === ApplicationTracking::STATUS_ACCEPTED
            || $applicationTracking === ApplicationTracking::STATUS_NOT_APPLICABLE
        ) {
            return false;
        }

        // It is a new application OR it is a variation where the section has been updated
        $applicationCompletion = $this->getApplicationCompletion()->getPsvDocumentaryEvidenceSmallStatus();
        if (
            $this->isVariation()
            && $applicationCompletion !== ApplicationCompletion::STATUS_VARIATION_UPDATED
        ) {
            return false;
        }

        return true;
    }

    public function canAddPsvLargeEvidence()
    {
        // This only applies to PSV licences that are not special restricted
        if (!$this->isPsv() || $this->isSpecialRestricted()) {
            return false;
        }

        // If user didn't select to upload later, it can't be added now
        if ($this->occupationEvidenceUploaded !== self::FINANCIAL_EVIDENCE_UPLOAD_LATER) {
            return false;
        }

        // The tracking section is NOT set to 'Accepted' or 'Not applicable'
        $applicationTracking = $this->applicationTracking->getPsvDocumentaryEvidenceLargeStatus();
        if (
            $applicationTracking === ApplicationTracking::STATUS_ACCEPTED
            || $applicationTracking === ApplicationTracking::STATUS_NOT_APPLICABLE
        ) {
            return false;
        }

        // It is a new application OR it is a variation where the section has been updated
        $applicationCompletion = $this->getApplicationCompletion()->getPsvDocumentaryEvidenceLargeStatus();
        if (
            $this->isVariation()
            && $applicationCompletion !== ApplicationCompletion::STATUS_VARIATION_UPDATED
        ) {
            return false;
        }

        return true;
    }

    /**
     * Can operating centre advert uploads be added
     *
     * @param array|null $applicationOperatingCentres List of application operating centres, optionally can be passed
     *                                                in case custom ordering is needed
     *
     * @return bool
     */
    public function canAddOperatingCentresEvidence(array $applicationOperatingCentres = null)
    {
        // The operating centre tracking section is NOT set to 'Acceptable' or 'Not applicable'
        // (i.e. application_tracking.operating_centre_status <> 1 or 3); AND
        $applicationTracking = $this->getApplicationTracking()->getOperatingCentresStatus();
        if (
            $applicationTracking === ApplicationTracking::STATUS_ACCEPTED
            || $applicationTracking === ApplicationTracking::STATUS_NOT_APPLICABLE
        ) {
            return false;
        }

        // It is a new goods application or variation; AND
        if (!$this->isGoods()) {
            return false;
        }

        return count($this->getApplicationOperatingCentresEvidenceRequired($applicationOperatingCentres)) > 0;
    }

    /**
     * Get a list of application operating centres that require advert evidence to be uploaded
     *
     * @param array|null $applicationOperatingCentres List of application operating centres, optionally can be passed
     *                                                in case custom ordering is needed
     *
     * @return array of ApplicationOperatingCentre
     */
    public function getApplicationOperatingCentresEvidenceRequired(array $applicationOperatingCentres = null)
    {
        // if $applicationOperatingCentres not passed as a parameter then get from this entity
        if ($applicationOperatingCentres === null) {
            $applicationOperatingCentres = $this->getOperatingCentres();
        }

        $aocsEvidenceRequired = [];

        // Get list of licence operating centres, using operating centre ID as the index
        $licenceOperatingCentres = [];
        /** @var Entity\Licence\LicenceOperatingCentre $loc */
        foreach ($this->getLicence()->getOperatingCentres() as $loc) {
            $licenceOperatingCentres[$loc->getOperatingCentre()->getId()] = $loc;
        }

        /** @var ApplicationOperatingCentre $aoc */
        foreach ($applicationOperatingCentres as $aoc) {
            // There are operating centres in the application where the advert uploader is set to 'Upload later' or
            // 'Send in the post' (i.e application_operating_centre.ad_placed = 0 or 2); AND
            if (
                $aoc->getAdPlaced() !== ApplicationOperatingCentre::AD_POST
                && $aoc->getAdPlaced() !== ApplicationOperatingCentre::AD_UPLOAD_LATER
            ) {
                continue;
            }
            // The operating centre has been added (i.e. application_operating_centre.action = A); OR
            if ($aoc->getAction() === ApplicationOperatingCentre::ACTION_ADD) {
                $aocsEvidenceRequired[] = $aoc;
                continue;
            }
            // The operating centre has been updated (i.e. application_operating_centre.action = U) and there has
            // been an increase in vehicles or trailers
            if ($aoc->getAction() === ApplicationOperatingCentre::ACTION_UPDATE) {
                $loc = $licenceOperatingCentres[$aoc->getOperatingCentre()->getId()];
                if (
                    $aoc->getNoOfVehiclesRequired() > $loc->getNoOfVehiclesRequired()
                    || $aoc->getNoOfTrailersRequired() > $loc->getNoOfTrailersRequired()
                ) {
                    $aocsEvidenceRequired[] = $aoc;
                }
                continue;
            }
        }

        return $aocsEvidenceRequired;
    }

    /**
     * Get the application organisation persons added
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|ApplicationOrganisationPerson[]
     */
    public function getApplicationOrganisationPersonsAdded()
    {
        $applicationOrgPeople = $this->getApplicationOrganisationPersons();
        $applicationOrgPeopleAdded = new ArrayCollection();
        /** @var ApplicationOrganisationPerson $applicationOrgPerson */
        foreach ($applicationOrgPeople as $applicationOrgPerson) {
            if ($applicationOrgPerson->getAction() == 'A') {
                $applicationOrgPeopleAdded->add($applicationOrgPerson);
            }
        }
        return $applicationOrgPeopleAdded;
    }

    public function getPostSubmissionApplicationDocuments($category, $subCategory, $operatingCentre = null)
    {
        $applicationDocuments = $this->getApplicationDocuments($category, $subCategory, $operatingCentre);

        $expr = Criteria::expr();
        $criteria = Criteria::create();
        $criteria->where($expr->eq('isPostSubmissionUpload', true));

        return $applicationDocuments->matching($criteria);
    }

    /**
     * Update the total number of hgv vehicles authorized for interim and refresh the property containing the total of hgv and lgv
     * vehicles authorized for interim.
     *
     * @return self
     */
    public function updateInterimAuthHgvVehicles(?int $interimAuthHgvVehicles): self
    {
        parent::setInterimAuthHgvVehicles($interimAuthHgvVehicles);
        return $this->updateInterimAuthVehicles();
    }

    /**
     * Update the total number of lgv vehicles authorized for interim and refresh the property containing the total of hgv and lgv
     * vehicles authorized for interim.
     *
     * @return self
     */
    public function updateInterimAuthLgvVehicles(?int $interimAuthLgvVehicles): self
    {
        parent::setInterimAuthLgvVehicles($interimAuthLgvVehicles);
        return $this->updateInterimAuthVehicles();
    }

    /**
     * Refresh the property containing the total of hgv and lgv authorized vehicles for interim
     */
    private function updateInterimAuthVehicles(): self
    {
        $this->interimAuthVehicles = ($this->interimAuthHgvVehicles ?? 0) + ($this->interimAuthLgvVehicles ?? 0);
        return $this;
    }
}
