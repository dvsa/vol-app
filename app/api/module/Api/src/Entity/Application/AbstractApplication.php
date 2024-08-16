<?php

namespace Dvsa\Olcs\Api\Entity\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Application Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="application",
 *    indexes={
 *        @ORM\Index(name="fk_application_grant_authority_ref_data_id", columns={"grant_authority"}),
 *        @ORM\Index(name="fk_application_vehicle_type_ref_data_id", columns={"vehicle_type"}),
 *        @ORM\Index(name="ix_application_applied_via", columns={"applied_via"}),
 *        @ORM\Index(name="ix_application_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_application_digital_signature_id", columns={"digital_signature_id"}),
 *        @ORM\Index(name="ix_application_goods_or_psv", columns={"goods_or_psv"}),
 *        @ORM\Index(name="ix_application_interim_status", columns={"interim_status"}),
 *        @ORM\Index(name="ix_application_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_application_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_application_licence_type", columns={"licence_type"}),
 *        @ORM\Index(name="ix_application_psv_which_vehicle_sizes",
     *     columns={"psv_which_vehicle_sizes"}),
 *        @ORM\Index(name="ix_application_signature_type", columns={"signature_type"}),
 *        @ORM\Index(name="ix_application_status", columns={"status"}),
 *        @ORM\Index(name="ix_application_variation_type", columns={"variation_type"}),
 *        @ORM\Index(name="ix_application_withdrawn_reason", columns={"withdrawn_reason"})
 *    }
 * )
 */
abstract class AbstractApplication implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Administration
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="administration", nullable=true)
     */
    protected $administration;

    /**
     * Application Referred to PI
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="application_referred_to_pi", nullable=true)
     */
    protected $applicationReferredToPi;

    /**
     * Applied via
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="applied_via", referencedColumnName="id", nullable=false)
     */
    protected $appliedVia;

    /**
     * Auth signature
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="auth_signature", nullable=false, options={"default": 0})
     */
    protected $authSignature = 0;

    /**
     * Bankrupt
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="bankrupt", nullable=true)
     */
    protected $bankrupt;

    /**
     * Convictions confirmation
     *
     * @var string
     *
     * @ORM\Column(type="yesno",
     *     name="convictions_confirmation",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $convictionsConfirmation = 0;

    /**
     * Created by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="create")
     */
    protected $createdBy;

    /**
     * Declaration confirmation
     *
     * @var string
     *
     * @ORM\Column(type="yesno",
     *     name="declaration_confirmation",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $declarationConfirmation = 0;

    /**
     * Digital signature
     *
     * @var \Dvsa\Olcs\Api\Entity\DigitalSignature
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\DigitalSignature", fetch="LAZY")
     * @ORM\JoinColumn(name="digital_signature_id", referencedColumnName="id", nullable=true)
     */
    protected $digitalSignature;

    /**
     * Disqualified
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="disqualified", nullable=true)
     */
    protected $disqualified;

    /**
     * Financial evidence uploaded
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="financial_evidence_uploaded", nullable=true)
     */
    protected $financialEvidenceUploaded;

    /**
     * Goods or psv
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="goods_or_psv", referencedColumnName="id", nullable=true)
     */
    protected $goodsOrPsv;

    /**
     * Grant authority
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="grant_authority", referencedColumnName="id", nullable=true)
     */
    protected $grantAuthority;

    /**
     * Granted date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="granted_date", nullable=true)
     */
    protected $grantedDate;

    /**
     * Has entered reg
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="has_entered_reg", nullable=true)
     */
    protected $hasEnteredReg;

    /**
     * Identifier - Id
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Insolvency confirmation
     *
     * @var string
     *
     * @ORM\Column(type="yesno",
     *     name="insolvency_confirmation",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $insolvencyConfirmation = 0;

    /**
     * Insolvency details
     *
     * @var string
     *
     * @ORM\Column(type="string", name="insolvency_details", length=4000, nullable=true)
     */
    protected $insolvencyDetails;

    /**
     * Interim auth hgv vehicles
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="interim_auth_hgv_vehicles", nullable=true)
     */
    protected $interimAuthHgvVehicles;

    /**
     * Interim auth lgv vehicles
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="interim_auth_lgv_vehicles", nullable=true)
     */
    protected $interimAuthLgvVehicles;

    /**
     * Interim auth trailers
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="interim_auth_trailers", nullable=true)
     */
    protected $interimAuthTrailers;

    /**
     * Interim auth vehicles
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="interim_auth_vehicles", nullable=true)
     */
    protected $interimAuthVehicles;

    /**
     * Interim end
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="interim_end", nullable=true)
     */
    protected $interimEnd;

    /**
     * Interim reason
     *
     * @var string
     *
     * @ORM\Column(type="string", name="interim_reason", length=1000, nullable=true)
     */
    protected $interimReason;

    /**
     * Interim start
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="interim_start", nullable=true)
     */
    protected $interimStart;

    /**
     * Interim status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="interim_status", referencedColumnName="id", nullable=true)
     */
    protected $interimStatus;

    /**
     * Is maintenance suitable
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_maintenance_suitable", nullable=true)
     */
    protected $isMaintenanceSuitable;

    /**
     * Is variation
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_variation", nullable=false)
     */
    protected $isVariation;

    /**
     * Last modified by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="last_modified_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="update")
     */
    protected $lastModifiedBy;

    /**
     * Lgv declaration confirmation
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean",
     *     name="lgv_declaration_confirmation",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $lgvDeclarationConfirmation = 0;

    /**
     * Licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence",
     *     fetch="LAZY",
     *     cascade={"persist"},
     *     inversedBy="applications"
     * )
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id", nullable=false)
     */
    protected $licence;

    /**
     * Licence type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_type", referencedColumnName="id", nullable=true)
     */
    protected $licenceType;

    /**
     * Liquidation
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="liquidation", nullable=true)
     */
    protected $liquidation;

    /**
     * Ni flag
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="ni_flag", nullable=true)
     */
    protected $niFlag;

    /**
     * Override ooo
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="override_ooo", nullable=false, options={"default": 0})
     */
    protected $overrideOoo = 0;

    /**
     * Prev been at pi
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="prev_been_at_pi", nullable=true)
     */
    protected $prevBeenAtPi;

    /**
     * Prev been disqualified tc
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="prev_been_disqualified_tc", nullable=true)
     */
    protected $prevBeenDisqualifiedTc;

    /**
     * Prev been refused
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="prev_been_refused", nullable=true)
     */
    protected $prevBeenRefused;

    /**
     * Prev been revoked
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="prev_been_revoked", nullable=true)
     */
    protected $prevBeenRevoked;

    /**
     * Prev conviction
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="prev_conviction", nullable=true)
     */
    protected $prevConviction;

    /**
     * Prev had licence
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="prev_had_licence", nullable=true)
     */
    protected $prevHadLicence;

    /**
     * Prev has licence
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="prev_has_licence", nullable=true)
     */
    protected $prevHasLicence;

    /**
     * Prev purchased assets
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="prev_purchased_assets", nullable=true)
     */
    protected $prevPurchasedAssets;

    /**
     * Psv limousines
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="psv_limousines", nullable=true)
     */
    protected $psvLimousines;

    /**
     * Psv medium vhl confirmation
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="psv_medium_vhl_confirmation", nullable=true)
     */
    protected $psvMediumVhlConfirmation;

    /**
     * Psv medium vhl notes
     *
     * @var string
     *
     * @ORM\Column(type="string", name="psv_medium_vhl_notes", length=1000, nullable=true)
     */
    protected $psvMediumVhlNotes;

    /**
     * Psv no limousine confirmation
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="psv_no_limousine_confirmation", nullable=true)
     */
    protected $psvNoLimousineConfirmation;

    /**
     * Psv no small vhl confirmation
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="psv_no_small_vhl_confirmation", nullable=true)
     */
    protected $psvNoSmallVhlConfirmation;

    /**
     * Psv only limousines confirmation
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="psv_only_limousines_confirmation", nullable=true)
     */
    protected $psvOnlyLimousinesConfirmation;

    /**
     * Psv operate small vhl
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="psv_operate_small_vhl", nullable=true)
     */
    protected $psvOperateSmallVhl;

    /**
     * Psv small vhl confirmation
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="psv_small_vhl_confirmation", nullable=true)
     */
    protected $psvSmallVhlConfirmation;

    /**
     * Psv small vhl notes
     *
     * @var string
     *
     * @ORM\Column(type="string", name="psv_small_vhl_notes", length=4000, nullable=true)
     */
    protected $psvSmallVhlNotes;

    /**
     * Psv which vehicle sizes
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="psv_which_vehicle_sizes", referencedColumnName="id", nullable=true)
     */
    protected $psvWhichVehicleSizes;

    /**
     * Received date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="received_date", nullable=true)
     */
    protected $receivedDate;

    /**
     * Receivership
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="receivership", nullable=true)
     */
    protected $receivership;

    /**
     * Refused date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="refused_date", nullable=true)
     */
    protected $refusedDate;

    /**
     * Request inspection
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="request_inspection", nullable=true)
     */
    protected $requestInspection;

    /**
     * Request inspection comment
     *
     * @var string
     *
     * @ORM\Column(type="string", name="request_inspection_comment", length=300, nullable=true)
     */
    protected $requestInspectionComment;

    /**
     * Request inspection delay
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="request_inspection_delay", nullable=true)
     */
    protected $requestInspectionDelay;

    /**
     * Safety confirmation
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="safety_confirmation", nullable=false, options={"default": 0})
     */
    protected $safetyConfirmation = 0;

    /**
     * Signature type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="signature_type", referencedColumnName="id", nullable=true)
     */
    protected $signatureType;

    /**
     * Status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="status", referencedColumnName="id", nullable=false)
     */
    protected $status;

    /**
     * Target completion date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="target_completion_date", nullable=true)
     */
    protected $targetCompletionDate;

    /**
     * Tot auth hgv vehicles
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="tot_auth_hgv_vehicles", nullable=true)
     */
    protected $totAuthHgvVehicles;

    /**
     * Tot auth lgv vehicles
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="tot_auth_lgv_vehicles", nullable=true)
     */
    protected $totAuthLgvVehicles;

    /**
     * Tot auth trailers
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="tot_auth_trailers", nullable=true)
     */
    protected $totAuthTrailers;

    /**
     * Tot auth vehicles
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="tot_auth_vehicles", nullable=true)
     */
    protected $totAuthVehicles;

    /**
     * Tot community licences
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="tot_community_licences", nullable=true)
     */
    protected $totCommunityLicences;

    /**
     * Variation type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="variation_type", referencedColumnName="id", nullable=true)
     */
    protected $variationType;

    /**
     * Vehicle type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="vehicle_type", referencedColumnName="id", nullable=true)
     */
    protected $vehicleType;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=false, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Withdrawn date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="withdrawn_date", nullable=true)
     */
    protected $withdrawnDate;

    /**
     * Withdrawn reason
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="withdrawn_reason", referencedColumnName="id", nullable=true)
     */
    protected $withdrawnReason;

    /**
     * Application completion
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion
     *
     * @ORM\OneToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion",
     *     mappedBy="application",
     *     cascade={"persist"}
     * )
     */
    protected $applicationCompletion;

    /**
     * Operating centre
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre",
     *     mappedBy="application"
     * )
     */
    protected $operatingCentres;

    /**
     * Application organisation person
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson",
     *     mappedBy="application"
     * )
     */
    protected $applicationOrganisationPersons;

    /**
     * Read audit
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Application\ApplicationReadAudit",
     *     mappedBy="application"
     * )
     */
    protected $readAudits;

    /**
     * Application tracking
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\ApplicationTracking
     *
     * @ORM\OneToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Application\ApplicationTracking",
     *     mappedBy="application",
     *     cascade={"persist"}
     * )
     */
    protected $applicationTracking;

    /**
     * Case
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases", mappedBy="application")
     */
    protected $cases;

    /**
     * Condition undertaking
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking",
     *     mappedBy="application"
     * )
     */
    protected $conditionUndertakings;

    /**
     * Document
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document",
     *     mappedBy="application",
     *     fetch="EXTRA_LAZY"
     * )
     */
    protected $documents;

    /**
     * Fee
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Fee\Fee", mappedBy="application")
     */
    protected $fees;

    /**
     * Licence vehicle
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle",
     *     mappedBy="application"
     * )
     */
    protected $licenceVehicles;

    /**
     * Interim licence vehicle
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle",
     *     mappedBy="interimApplication"
     * )
     */
    protected $interimLicenceVehicles;

    /**
     * Other licence
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence",
     *     mappedBy="application"
     * )
     */
    protected $otherLicences;

    /**
     * Previous conviction
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Application\PreviousConviction",
     *     mappedBy="application"
     * )
     */
    protected $previousConvictions;

    /**
     * Publication link
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Publication\PublicationLink",
     *     mappedBy="application"
     * )
     */
    protected $publicationLinks;

    /**
     * S4
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Application\S4", mappedBy="application")
     */
    protected $s4s;

    /**
     * Task
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Task\Task", mappedBy="application")
     */
    protected $tasks;

    /**
     * Transport manager
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication",
     *     mappedBy="application",
     *     cascade={"remove"}
     * )
     */
    protected $transportManagers;

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function initCollections()
    {
        $this->operatingCentres = new ArrayCollection();
        $this->applicationOrganisationPersons = new ArrayCollection();
        $this->readAudits = new ArrayCollection();
        $this->cases = new ArrayCollection();
        $this->conditionUndertakings = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->fees = new ArrayCollection();
        $this->licenceVehicles = new ArrayCollection();
        $this->interimLicenceVehicles = new ArrayCollection();
        $this->otherLicences = new ArrayCollection();
        $this->previousConvictions = new ArrayCollection();
        $this->publicationLinks = new ArrayCollection();
        $this->s4s = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->transportManagers = new ArrayCollection();
    }

    /**
     * Set the administration
     *
     * @param string $administration new value being set
     *
     * @return Application
     */
    public function setAdministration($administration)
    {
        $this->administration = $administration;

        return $this;
    }

    /**
     * Get applicationReferredToPi
     *
     * @return string
     */
    public function getApplicationReferredToPi()
    {
        return $this->applicationReferredToPi;
    }

    /**
     * Set applicationReferredToPi
     *
     * @param string $applicationReferredToPi new value being set
     *
     * @return Application
     */
    public function setApplicationReferredToPi($applicationReferredToPi)
    {
        $this->applicationReferredToPi = $applicationReferredToPi;

        return $this;
    }

    /**
     * Get the administration
     *
     * @return string
     */
    public function getAdministration()
    {
        return $this->administration;
    }

    /**
     * Set the applied via
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $appliedVia entity being set as the value
     *
     * @return Application
     */
    public function setAppliedVia($appliedVia)
    {
        $this->appliedVia = $appliedVia;

        return $this;
    }

    /**
     * Get the applied via
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getAppliedVia()
    {
        return $this->appliedVia;
    }

    /**
     * Set the auth signature
     *
     * @param boolean $authSignature new value being set
     *
     * @return Application
     */
    public function setAuthSignature($authSignature)
    {
        $this->authSignature = $authSignature;

        return $this;
    }

    /**
     * Get the auth signature
     *
     * @return boolean
     */
    public function getAuthSignature()
    {
        return $this->authSignature;
    }

    /**
     * Set the bankrupt
     *
     * @param string $bankrupt new value being set
     *
     * @return Application
     */
    public function setBankrupt($bankrupt)
    {
        $this->bankrupt = $bankrupt;

        return $this;
    }

    /**
     * Get the bankrupt
     *
     * @return string
     */
    public function getBankrupt()
    {
        return $this->bankrupt;
    }

    /**
     * Set the convictions confirmation
     *
     * @param string $convictionsConfirmation new value being set
     *
     * @return Application
     */
    public function setConvictionsConfirmation($convictionsConfirmation)
    {
        $this->convictionsConfirmation = $convictionsConfirmation;

        return $this;
    }

    /**
     * Get the convictions confirmation
     *
     * @return string
     */
    public function getConvictionsConfirmation()
    {
        return $this->convictionsConfirmation;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return Application
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the declaration confirmation
     *
     * @param string $declarationConfirmation new value being set
     *
     * @return Application
     */
    public function setDeclarationConfirmation($declarationConfirmation)
    {
        $this->declarationConfirmation = $declarationConfirmation;

        return $this;
    }

    /**
     * Get the declaration confirmation
     *
     * @return string
     */
    public function getDeclarationConfirmation()
    {
        return $this->declarationConfirmation;
    }

    /**
     * Set the digital signature
     *
     * @param \Dvsa\Olcs\Api\Entity\DigitalSignature $digitalSignature entity being set as the value
     *
     * @return Application
     */
    public function setDigitalSignature($digitalSignature)
    {
        $this->digitalSignature = $digitalSignature;

        return $this;
    }

    /**
     * Get the digital signature
     *
     * @return \Dvsa\Olcs\Api\Entity\DigitalSignature
     */
    public function getDigitalSignature()
    {
        return $this->digitalSignature;
    }

    /**
     * Set the disqualified
     *
     * @param string $disqualified new value being set
     *
     * @return Application
     */
    public function setDisqualified($disqualified)
    {
        $this->disqualified = $disqualified;

        return $this;
    }

    /**
     * Get the disqualified
     *
     * @return string
     */
    public function getDisqualified()
    {
        return $this->disqualified;
    }

    /**
     * Set the financial evidence uploaded
     *
     * @param int $financialEvidenceUploaded new value being set
     *
     * @return Application
     */
    public function setFinancialEvidenceUploaded($financialEvidenceUploaded)
    {
        $this->financialEvidenceUploaded = $financialEvidenceUploaded;

        return $this;
    }

    /**
     * Get the financial evidence uploaded
     *
     * @return int
     */
    public function getFinancialEvidenceUploaded()
    {
        return $this->financialEvidenceUploaded;
    }

    /**
     * Set the goods or psv
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $goodsOrPsv entity being set as the value
     *
     * @return Application
     */
    public function setGoodsOrPsv($goodsOrPsv)
    {
        $this->goodsOrPsv = $goodsOrPsv;

        return $this;
    }

    /**
     * Get the goods or psv
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getGoodsOrPsv()
    {
        return $this->goodsOrPsv;
    }

    /**
     * Set the grant authority
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $grantAuthority entity being set as the value
     *
     * @return Application
     */
    public function setGrantAuthority($grantAuthority)
    {
        $this->grantAuthority = $grantAuthority;

        return $this;
    }

    /**
     * Get the grant authority
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getGrantAuthority()
    {
        return $this->grantAuthority;
    }

    /**
     * Set the granted date
     *
     * @param \DateTime $grantedDate new value being set
     *
     * @return Application
     */
    public function setGrantedDate($grantedDate)
    {
        $this->grantedDate = $grantedDate;

        return $this;
    }

    /**
     * Get the granted date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getGrantedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->grantedDate);
        }

        return $this->grantedDate;
    }

    /**
     * Set the has entered reg
     *
     * @param string $hasEnteredReg new value being set
     *
     * @return Application
     */
    public function setHasEnteredReg($hasEnteredReg)
    {
        $this->hasEnteredReg = $hasEnteredReg;

        return $this;
    }

    /**
     * Get the has entered reg
     *
     * @return string
     */
    public function getHasEnteredReg()
    {
        return $this->hasEnteredReg;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Application
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the insolvency confirmation
     *
     * @param string $insolvencyConfirmation new value being set
     *
     * @return Application
     */
    public function setInsolvencyConfirmation($insolvencyConfirmation)
    {
        $this->insolvencyConfirmation = $insolvencyConfirmation;

        return $this;
    }

    /**
     * Get the insolvency confirmation
     *
     * @return string
     */
    public function getInsolvencyConfirmation()
    {
        return $this->insolvencyConfirmation;
    }

    /**
     * Set the insolvency details
     *
     * @param string $insolvencyDetails new value being set
     *
     * @return Application
     */
    public function setInsolvencyDetails($insolvencyDetails)
    {
        $this->insolvencyDetails = $insolvencyDetails;

        return $this;
    }

    /**
     * Get the insolvency details
     *
     * @return string
     */
    public function getInsolvencyDetails()
    {
        return $this->insolvencyDetails;
    }

    /**
     * Set the interim auth hgv vehicles
     *
     * @param int $interimAuthHgvVehicles new value being set
     *
     * @return Application
     */
    public function setInterimAuthHgvVehicles($interimAuthHgvVehicles)
    {
        $this->interimAuthHgvVehicles = $interimAuthHgvVehicles;

        return $this;
    }

    /**
     * Get the interim auth hgv vehicles
     *
     * @return int
     */
    public function getInterimAuthHgvVehicles()
    {
        return $this->interimAuthHgvVehicles;
    }

    /**
     * Set the interim auth lgv vehicles
     *
     * @param int $interimAuthLgvVehicles new value being set
     *
     * @return Application
     */
    public function setInterimAuthLgvVehicles($interimAuthLgvVehicles)
    {
        $this->interimAuthLgvVehicles = $interimAuthLgvVehicles;

        return $this;
    }

    /**
     * Get the interim auth lgv vehicles
     *
     * @return int
     */
    public function getInterimAuthLgvVehicles()
    {
        return $this->interimAuthLgvVehicles;
    }

    /**
     * Set the interim auth trailers
     *
     * @param int $interimAuthTrailers new value being set
     *
     * @return Application
     */
    public function setInterimAuthTrailers($interimAuthTrailers)
    {
        $this->interimAuthTrailers = $interimAuthTrailers;

        return $this;
    }

    /**
     * Get the interim auth trailers
     *
     * @return int
     */
    public function getInterimAuthTrailers()
    {
        return $this->interimAuthTrailers;
    }

    /**
     * Set the interim auth vehicles
     *
     * @param int $interimAuthVehicles new value being set
     *
     * @return Application
     */
    public function setInterimAuthVehicles($interimAuthVehicles)
    {
        $this->interimAuthVehicles = $interimAuthVehicles;

        return $this;
    }

    /**
     * Get the interim auth vehicles
     *
     * @return int
     */
    public function getInterimAuthVehicles()
    {
        return $this->interimAuthVehicles;
    }

    /**
     * Set the interim end
     *
     * @param \DateTime $interimEnd new value being set
     *
     * @return Application
     */
    public function setInterimEnd($interimEnd)
    {
        $this->interimEnd = $interimEnd;

        return $this;
    }

    /**
     * Get the interim end
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getInterimEnd($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->interimEnd);
        }

        return $this->interimEnd;
    }

    /**
     * Set the interim reason
     *
     * @param string $interimReason new value being set
     *
     * @return Application
     */
    public function setInterimReason($interimReason)
    {
        $this->interimReason = $interimReason;

        return $this;
    }

    /**
     * Get the interim reason
     *
     * @return string
     */
    public function getInterimReason()
    {
        return $this->interimReason;
    }

    /**
     * Set the interim start
     *
     * @param \DateTime $interimStart new value being set
     *
     * @return Application
     */
    public function setInterimStart($interimStart)
    {
        $this->interimStart = $interimStart;

        return $this;
    }

    /**
     * Get the interim start
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getInterimStart($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->interimStart);
        }

        return $this->interimStart;
    }

    /**
     * Set the interim status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $interimStatus entity being set as the value
     *
     * @return Application
     */
    public function setInterimStatus($interimStatus)
    {
        $this->interimStatus = $interimStatus;

        return $this;
    }

    /**
     * Get the interim status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getInterimStatus()
    {
        return $this->interimStatus;
    }

    /**
     * Set the is maintenance suitable
     *
     * @param string $isMaintenanceSuitable new value being set
     *
     * @return Application
     */
    public function setIsMaintenanceSuitable($isMaintenanceSuitable)
    {
        $this->isMaintenanceSuitable = $isMaintenanceSuitable;

        return $this;
    }

    /**
     * Get the is maintenance suitable
     *
     * @return string
     */
    public function getIsMaintenanceSuitable()
    {
        return $this->isMaintenanceSuitable;
    }

    /**
     * Set the is variation
     *
     * @param boolean $isVariation new value being set
     *
     * @return Application
     */
    public function setIsVariation($isVariation)
    {
        $this->isVariation = $isVariation;

        return $this;
    }

    /**
     * Get the is variation
     *
     * @return boolean
     */
    public function getIsVariation()
    {
        return $this->isVariation;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return Application
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the lgv declaration confirmation
     *
     * @param boolean $lgvDeclarationConfirmation new value being set
     *
     * @return Application
     */
    public function setLgvDeclarationConfirmation($lgvDeclarationConfirmation)
    {
        $this->lgvDeclarationConfirmation = $lgvDeclarationConfirmation;

        return $this;
    }

    /**
     * Get the lgv declaration confirmation
     *
     * @return boolean
     */
    public function getLgvDeclarationConfirmation()
    {
        return $this->lgvDeclarationConfirmation;
    }

    /**
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence entity being set as the value
     *
     * @return Application
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;

        return $this;
    }

    /**
     * Get the licence
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\Licence
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Set the licence type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $licenceType entity being set as the value
     *
     * @return Application
     */
    public function setLicenceType($licenceType)
    {
        $this->licenceType = $licenceType;

        return $this;
    }

    /**
     * Get the licence type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getLicenceType()
    {
        return $this->licenceType;
    }

    /**
     * Set the liquidation
     *
     * @param string $liquidation new value being set
     *
     * @return Application
     */
    public function setLiquidation($liquidation)
    {
        $this->liquidation = $liquidation;

        return $this;
    }

    /**
     * Get the liquidation
     *
     * @return string
     */
    public function getLiquidation()
    {
        return $this->liquidation;
    }

    /**
     * Set the ni flag
     *
     * @param string $niFlag new value being set
     *
     * @return Application
     */
    public function setNiFlag($niFlag)
    {
        $this->niFlag = $niFlag;

        return $this;
    }

    /**
     * Get the ni flag
     *
     * @return string
     */
    public function getNiFlag()
    {
        return $this->niFlag;
    }

    /**
     * Set the override ooo
     *
     * @param string $overrideOoo new value being set
     *
     * @return Application
     */
    public function setOverrideOoo($overrideOoo)
    {
        $this->overrideOoo = $overrideOoo;

        return $this;
    }

    /**
     * Get the override ooo
     *
     * @return string
     */
    public function getOverrideOoo()
    {
        return $this->overrideOoo;
    }

    /**
     * Set the prev been at pi
     *
     * @param string $prevBeenAtPi new value being set
     *
     * @return Application
     */
    public function setPrevBeenAtPi($prevBeenAtPi)
    {
        $this->prevBeenAtPi = $prevBeenAtPi;

        return $this;
    }

    /**
     * Get the prev been at pi
     *
     * @return string
     */
    public function getPrevBeenAtPi()
    {
        return $this->prevBeenAtPi;
    }

    /**
     * Set the prev been disqualified tc
     *
     * @param string $prevBeenDisqualifiedTc new value being set
     *
     * @return Application
     */
    public function setPrevBeenDisqualifiedTc($prevBeenDisqualifiedTc)
    {
        $this->prevBeenDisqualifiedTc = $prevBeenDisqualifiedTc;

        return $this;
    }

    /**
     * Get the prev been disqualified tc
     *
     * @return string
     */
    public function getPrevBeenDisqualifiedTc()
    {
        return $this->prevBeenDisqualifiedTc;
    }

    /**
     * Set the prev been refused
     *
     * @param string $prevBeenRefused new value being set
     *
     * @return Application
     */
    public function setPrevBeenRefused($prevBeenRefused)
    {
        $this->prevBeenRefused = $prevBeenRefused;

        return $this;
    }

    /**
     * Get the prev been refused
     *
     * @return string
     */
    public function getPrevBeenRefused()
    {
        return $this->prevBeenRefused;
    }

    /**
     * Set the prev been revoked
     *
     * @param string $prevBeenRevoked new value being set
     *
     * @return Application
     */
    public function setPrevBeenRevoked($prevBeenRevoked)
    {
        $this->prevBeenRevoked = $prevBeenRevoked;

        return $this;
    }

    /**
     * Get the prev been revoked
     *
     * @return string
     */
    public function getPrevBeenRevoked()
    {
        return $this->prevBeenRevoked;
    }

    /**
     * Set the prev conviction
     *
     * @param string $prevConviction new value being set
     *
     * @return Application
     */
    public function setPrevConviction($prevConviction)
    {
        $this->prevConviction = $prevConviction;

        return $this;
    }

    /**
     * Get the prev conviction
     *
     * @return string
     */
    public function getPrevConviction()
    {
        return $this->prevConviction;
    }

    /**
     * Set the prev had licence
     *
     * @param string $prevHadLicence new value being set
     *
     * @return Application
     */
    public function setPrevHadLicence($prevHadLicence)
    {
        $this->prevHadLicence = $prevHadLicence;

        return $this;
    }

    /**
     * Get the prev had licence
     *
     * @return string
     */
    public function getPrevHadLicence()
    {
        return $this->prevHadLicence;
    }

    /**
     * Set the prev has licence
     *
     * @param string $prevHasLicence new value being set
     *
     * @return Application
     */
    public function setPrevHasLicence($prevHasLicence)
    {
        $this->prevHasLicence = $prevHasLicence;

        return $this;
    }

    /**
     * Get the prev has licence
     *
     * @return string
     */
    public function getPrevHasLicence()
    {
        return $this->prevHasLicence;
    }

    /**
     * Set the prev purchased assets
     *
     * @param string $prevPurchasedAssets new value being set
     *
     * @return Application
     */
    public function setPrevPurchasedAssets($prevPurchasedAssets)
    {
        $this->prevPurchasedAssets = $prevPurchasedAssets;

        return $this;
    }

    /**
     * Get the prev purchased assets
     *
     * @return string
     */
    public function getPrevPurchasedAssets()
    {
        return $this->prevPurchasedAssets;
    }

    /**
     * Set the psv limousines
     *
     * @param string $psvLimousines new value being set
     *
     * @return Application
     */
    public function setPsvLimousines($psvLimousines)
    {
        $this->psvLimousines = $psvLimousines;

        return $this;
    }

    /**
     * Get the psv limousines
     *
     * @return string
     */
    public function getPsvLimousines()
    {
        return $this->psvLimousines;
    }

    /**
     * Set the psv medium vhl confirmation
     *
     * @param string $psvMediumVhlConfirmation new value being set
     *
     * @return Application
     */
    public function setPsvMediumVhlConfirmation($psvMediumVhlConfirmation)
    {
        $this->psvMediumVhlConfirmation = $psvMediumVhlConfirmation;

        return $this;
    }

    /**
     * Get the psv medium vhl confirmation
     *
     * @return string
     */
    public function getPsvMediumVhlConfirmation()
    {
        return $this->psvMediumVhlConfirmation;
    }

    /**
     * Set the psv medium vhl notes
     *
     * @param string $psvMediumVhlNotes new value being set
     *
     * @return Application
     */
    public function setPsvMediumVhlNotes($psvMediumVhlNotes)
    {
        $this->psvMediumVhlNotes = $psvMediumVhlNotes;

        return $this;
    }

    /**
     * Get the psv medium vhl notes
     *
     * @return string
     */
    public function getPsvMediumVhlNotes()
    {
        return $this->psvMediumVhlNotes;
    }

    /**
     * Set the psv no limousine confirmation
     *
     * @param string $psvNoLimousineConfirmation new value being set
     *
     * @return Application
     */
    public function setPsvNoLimousineConfirmation($psvNoLimousineConfirmation)
    {
        $this->psvNoLimousineConfirmation = $psvNoLimousineConfirmation;

        return $this;
    }

    /**
     * Get the psv no limousine confirmation
     *
     * @return string
     */
    public function getPsvNoLimousineConfirmation()
    {
        return $this->psvNoLimousineConfirmation;
    }

    /**
     * Set the psv no small vhl confirmation
     *
     * @param string $psvNoSmallVhlConfirmation new value being set
     *
     * @return Application
     */
    public function setPsvNoSmallVhlConfirmation($psvNoSmallVhlConfirmation)
    {
        $this->psvNoSmallVhlConfirmation = $psvNoSmallVhlConfirmation;

        return $this;
    }

    /**
     * Get the psv no small vhl confirmation
     *
     * @return string
     */
    public function getPsvNoSmallVhlConfirmation()
    {
        return $this->psvNoSmallVhlConfirmation;
    }

    /**
     * Set the psv only limousines confirmation
     *
     * @param string $psvOnlyLimousinesConfirmation new value being set
     *
     * @return Application
     */
    public function setPsvOnlyLimousinesConfirmation($psvOnlyLimousinesConfirmation)
    {
        $this->psvOnlyLimousinesConfirmation = $psvOnlyLimousinesConfirmation;

        return $this;
    }

    /**
     * Get the psv only limousines confirmation
     *
     * @return string
     */
    public function getPsvOnlyLimousinesConfirmation()
    {
        return $this->psvOnlyLimousinesConfirmation;
    }

    /**
     * Set the psv operate small vhl
     *
     * @param string $psvOperateSmallVhl new value being set
     *
     * @return Application
     */
    public function setPsvOperateSmallVhl($psvOperateSmallVhl)
    {
        $this->psvOperateSmallVhl = $psvOperateSmallVhl;

        return $this;
    }

    /**
     * Get the psv operate small vhl
     *
     * @return string
     */
    public function getPsvOperateSmallVhl()
    {
        return $this->psvOperateSmallVhl;
    }

    /**
     * Set the psv small vhl confirmation
     *
     * @param string $psvSmallVhlConfirmation new value being set
     *
     * @return Application
     */
    public function setPsvSmallVhlConfirmation($psvSmallVhlConfirmation)
    {
        $this->psvSmallVhlConfirmation = $psvSmallVhlConfirmation;

        return $this;
    }

    /**
     * Get the psv small vhl confirmation
     *
     * @return string
     */
    public function getPsvSmallVhlConfirmation()
    {
        return $this->psvSmallVhlConfirmation;
    }

    /**
     * Set the psv small vhl notes
     *
     * @param string $psvSmallVhlNotes new value being set
     *
     * @return Application
     */
    public function setPsvSmallVhlNotes($psvSmallVhlNotes)
    {
        $this->psvSmallVhlNotes = $psvSmallVhlNotes;

        return $this;
    }

    /**
     * Get the psv small vhl notes
     *
     * @return string
     */
    public function getPsvSmallVhlNotes()
    {
        return $this->psvSmallVhlNotes;
    }

    /**
     * Set the psv which vehicle sizes
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $psvWhichVehicleSizes entity being set as the value
     *
     * @return Application
     */
    public function setPsvWhichVehicleSizes($psvWhichVehicleSizes)
    {
        $this->psvWhichVehicleSizes = $psvWhichVehicleSizes;

        return $this;
    }

    /**
     * Get the psv which vehicle sizes
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getPsvWhichVehicleSizes()
    {
        return $this->psvWhichVehicleSizes;
    }

    /**
     * Set the received date
     *
     * @param \DateTime $receivedDate new value being set
     *
     * @return Application
     */
    public function setReceivedDate($receivedDate)
    {
        $this->receivedDate = $receivedDate;

        return $this;
    }

    /**
     * Get the received date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getReceivedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->receivedDate);
        }

        return $this->receivedDate;
    }

    /**
     * Set the receivership
     *
     * @param string $receivership new value being set
     *
     * @return Application
     */
    public function setReceivership($receivership)
    {
        $this->receivership = $receivership;

        return $this;
    }

    /**
     * Get the receivership
     *
     * @return string
     */
    public function getReceivership()
    {
        return $this->receivership;
    }

    /**
     * Set the refused date
     *
     * @param \DateTime $refusedDate new value being set
     *
     * @return Application
     */
    public function setRefusedDate($refusedDate)
    {
        $this->refusedDate = $refusedDate;

        return $this;
    }

    /**
     * Get the refused date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getRefusedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->refusedDate);
        }

        return $this->refusedDate;
    }

    /**
     * Set the request inspection
     *
     * @param int $requestInspection new value being set
     *
     * @return Application
     */
    public function setRequestInspection($requestInspection)
    {
        $this->requestInspection = $requestInspection;

        return $this;
    }

    /**
     * Get the request inspection
     *
     * @return int
     */
    public function getRequestInspection()
    {
        return $this->requestInspection;
    }

    /**
     * Set the request inspection comment
     *
     * @param string $requestInspectionComment new value being set
     *
     * @return Application
     */
    public function setRequestInspectionComment($requestInspectionComment)
    {
        $this->requestInspectionComment = $requestInspectionComment;

        return $this;
    }

    /**
     * Get the request inspection comment
     *
     * @return string
     */
    public function getRequestInspectionComment()
    {
        return $this->requestInspectionComment;
    }

    /**
     * Set the request inspection delay
     *
     * @param int $requestInspectionDelay new value being set
     *
     * @return Application
     */
    public function setRequestInspectionDelay($requestInspectionDelay)
    {
        $this->requestInspectionDelay = $requestInspectionDelay;

        return $this;
    }

    /**
     * Get the request inspection delay
     *
     * @return int
     */
    public function getRequestInspectionDelay()
    {
        return $this->requestInspectionDelay;
    }

    /**
     * Set the safety confirmation
     *
     * @param string $safetyConfirmation new value being set
     *
     * @return Application
     */
    public function setSafetyConfirmation($safetyConfirmation)
    {
        $this->safetyConfirmation = $safetyConfirmation;

        return $this;
    }

    /**
     * Get the safety confirmation
     *
     * @return string
     */
    public function getSafetyConfirmation()
    {
        return $this->safetyConfirmation;
    }

    /**
     * Set the signature type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $signatureType entity being set as the value
     *
     * @return Application
     */
    public function setSignatureType($signatureType)
    {
        $this->signatureType = $signatureType;

        return $this;
    }

    /**
     * Get the signature type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getSignatureType()
    {
        return $this->signatureType;
    }

    /**
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status entity being set as the value
     *
     * @return Application
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the target completion date
     *
     * @param \DateTime $targetCompletionDate new value being set
     *
     * @return Application
     */
    public function setTargetCompletionDate($targetCompletionDate)
    {
        $this->targetCompletionDate = $targetCompletionDate;

        return $this;
    }

    /**
     * Get the target completion date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getTargetCompletionDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->targetCompletionDate);
        }

        return $this->targetCompletionDate;
    }

    /**
     * Set the tot auth hgv vehicles
     *
     * @param int $totAuthHgvVehicles new value being set
     *
     * @return Application
     */
    public function setTotAuthHgvVehicles($totAuthHgvVehicles)
    {
        $this->totAuthHgvVehicles = $totAuthHgvVehicles;

        return $this;
    }

    /**
     * Get the tot auth hgv vehicles
     *
     * @return int
     */
    public function getTotAuthHgvVehicles()
    {
        return $this->totAuthHgvVehicles;
    }

    /**
     * Set the tot auth lgv vehicles
     *
     * @param int $totAuthLgvVehicles new value being set
     *
     * @return Application
     */
    public function setTotAuthLgvVehicles($totAuthLgvVehicles)
    {
        $this->totAuthLgvVehicles = $totAuthLgvVehicles;

        return $this;
    }

    /**
     * Get the tot auth lgv vehicles
     *
     * @return int
     */
    public function getTotAuthLgvVehicles()
    {
        return $this->totAuthLgvVehicles;
    }

    /**
     * Set the tot auth trailers
     *
     * @param int $totAuthTrailers new value being set
     *
     * @return Application
     */
    public function setTotAuthTrailers($totAuthTrailers)
    {
        $this->totAuthTrailers = $totAuthTrailers;

        return $this;
    }

    /**
     * Get the tot auth trailers
     *
     * @return int
     */
    public function getTotAuthTrailers()
    {
        return $this->totAuthTrailers;
    }

    /**
     * Set the tot auth vehicles
     *
     * @param int $totAuthVehicles new value being set
     *
     * @return Application
     */
    public function setTotAuthVehicles($totAuthVehicles)
    {
        $this->totAuthVehicles = $totAuthVehicles;

        return $this;
    }

    /**
     * Get the tot auth vehicles
     *
     * @return int
     */
    public function getTotAuthVehicles()
    {
        return $this->totAuthVehicles;
    }

    /**
     * Set the tot community licences
     *
     * @param int $totCommunityLicences new value being set
     *
     * @return Application
     */
    public function setTotCommunityLicences($totCommunityLicences)
    {
        $this->totCommunityLicences = $totCommunityLicences;

        return $this;
    }

    /**
     * Get the tot community licences
     *
     * @return int
     */
    public function getTotCommunityLicences()
    {
        return $this->totCommunityLicences;
    }

    /**
     * Set the variation type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $variationType entity being set as the value
     *
     * @return Application
     */
    public function setVariationType($variationType)
    {
        $this->variationType = $variationType;

        return $this;
    }

    /**
     * Get the variation type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getVariationType()
    {
        return $this->variationType;
    }

    /**
     * Set the vehicle type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $vehicleType entity being set as the value
     *
     * @return Application
     */
    public function setVehicleType($vehicleType)
    {
        $this->vehicleType = $vehicleType;

        return $this;
    }

    /**
     * Get the vehicle type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getVehicleType()
    {
        return $this->vehicleType;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Application
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the withdrawn date
     *
     * @param \DateTime $withdrawnDate new value being set
     *
     * @return Application
     */
    public function setWithdrawnDate($withdrawnDate)
    {
        $this->withdrawnDate = $withdrawnDate;

        return $this;
    }

    /**
     * Get the withdrawn date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getWithdrawnDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->withdrawnDate);
        }

        return $this->withdrawnDate;
    }

    /**
     * Set the withdrawn reason
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $withdrawnReason entity being set as the value
     *
     * @return Application
     */
    public function setWithdrawnReason($withdrawnReason)
    {
        $this->withdrawnReason = $withdrawnReason;

        return $this;
    }

    /**
     * Get the withdrawn reason
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getWithdrawnReason()
    {
        return $this->withdrawnReason;
    }

    /**
     * Set the application completion
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion $applicationCompletion entity being set as the value
     *
     * @return Application
     */
    public function setApplicationCompletion($applicationCompletion)
    {
        $this->applicationCompletion = $applicationCompletion;

        return $this;
    }

    /**
     * Get the application completion
     *
     * @return \Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion
     */
    public function getApplicationCompletion()
    {
        return $this->applicationCompletion;
    }

    /**
     * Set the operating centre
     *
     * @param ArrayCollection $operatingCentres collection being set as the value
     *
     * @return Application
     */
    public function setOperatingCentres($operatingCentres)
    {
        $this->operatingCentres = $operatingCentres;

        return $this;
    }

    /**
     * Get the operating centres
     *
     * @return ArrayCollection
     */
    public function getOperatingCentres()
    {
        return $this->operatingCentres;
    }

    /**
     * Add a operating centres
     *
     * @param ArrayCollection|mixed $operatingCentres collection being added
     *
     * @return Application
     */
    public function addOperatingCentres($operatingCentres)
    {
        if ($operatingCentres instanceof ArrayCollection) {
            $this->operatingCentres = new ArrayCollection(
                array_merge(
                    $this->operatingCentres->toArray(),
                    $operatingCentres->toArray()
                )
            );
        } elseif (!$this->operatingCentres->contains($operatingCentres)) {
            $this->operatingCentres->add($operatingCentres);
        }

        return $this;
    }

    /**
     * Remove a operating centres
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $operatingCentres collection being removed
     *
     * @return Application
     */
    public function removeOperatingCentres($operatingCentres)
    {
        if ($this->operatingCentres->contains($operatingCentres)) {
            $this->operatingCentres->removeElement($operatingCentres);
        }

        return $this;
    }

    /**
     * Set the application organisation person
     *
     * @param ArrayCollection $applicationOrganisationPersons collection being set as the value
     *
     * @return Application
     */
    public function setApplicationOrganisationPersons($applicationOrganisationPersons)
    {
        $this->applicationOrganisationPersons = $applicationOrganisationPersons;

        return $this;
    }

    /**
     * Get the application organisation persons
     *
     * @return ArrayCollection
     */
    public function getApplicationOrganisationPersons()
    {
        return $this->applicationOrganisationPersons;
    }

    /**
     * Add a application organisation persons
     *
     * @param ArrayCollection|mixed $applicationOrganisationPersons collection being added
     *
     * @return Application
     */
    public function addApplicationOrganisationPersons($applicationOrganisationPersons)
    {
        if ($applicationOrganisationPersons instanceof ArrayCollection) {
            $this->applicationOrganisationPersons = new ArrayCollection(
                array_merge(
                    $this->applicationOrganisationPersons->toArray(),
                    $applicationOrganisationPersons->toArray()
                )
            );
        } elseif (!$this->applicationOrganisationPersons->contains($applicationOrganisationPersons)) {
            $this->applicationOrganisationPersons->add($applicationOrganisationPersons);
        }

        return $this;
    }

    /**
     * Remove a application organisation persons
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $applicationOrganisationPersons collection being removed
     *
     * @return Application
     */
    public function removeApplicationOrganisationPersons($applicationOrganisationPersons)
    {
        if ($this->applicationOrganisationPersons->contains($applicationOrganisationPersons)) {
            $this->applicationOrganisationPersons->removeElement($applicationOrganisationPersons);
        }

        return $this;
    }

    /**
     * Set the read audit
     *
     * @param ArrayCollection $readAudits collection being set as the value
     *
     * @return Application
     */
    public function setReadAudits($readAudits)
    {
        $this->readAudits = $readAudits;

        return $this;
    }

    /**
     * Get the read audits
     *
     * @return ArrayCollection
     */
    public function getReadAudits()
    {
        return $this->readAudits;
    }

    /**
     * Add a read audits
     *
     * @param ArrayCollection|mixed $readAudits collection being added
     *
     * @return Application
     */
    public function addReadAudits($readAudits)
    {
        if ($readAudits instanceof ArrayCollection) {
            $this->readAudits = new ArrayCollection(
                array_merge(
                    $this->readAudits->toArray(),
                    $readAudits->toArray()
                )
            );
        } elseif (!$this->readAudits->contains($readAudits)) {
            $this->readAudits->add($readAudits);
        }

        return $this;
    }

    /**
     * Remove a read audits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $readAudits collection being removed
     *
     * @return Application
     */
    public function removeReadAudits($readAudits)
    {
        if ($this->readAudits->contains($readAudits)) {
            $this->readAudits->removeElement($readAudits);
        }

        return $this;
    }

    /**
     * Set the application tracking
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\ApplicationTracking $applicationTracking entity being set as the value
     *
     * @return Application
     */
    public function setApplicationTracking($applicationTracking)
    {
        $this->applicationTracking = $applicationTracking;

        return $this;
    }

    /**
     * Get the application tracking
     *
     * @return \Dvsa\Olcs\Api\Entity\Application\ApplicationTracking
     */
    public function getApplicationTracking()
    {
        return $this->applicationTracking;
    }

    /**
     * Set the case
     *
     * @param ArrayCollection $cases collection being set as the value
     *
     * @return Application
     */
    public function setCases($cases)
    {
        $this->cases = $cases;

        return $this;
    }

    /**
     * Get the cases
     *
     * @return ArrayCollection
     */
    public function getCases()
    {
        return $this->cases;
    }

    /**
     * Add a cases
     *
     * @param ArrayCollection|mixed $cases collection being added
     *
     * @return Application
     */
    public function addCases($cases)
    {
        if ($cases instanceof ArrayCollection) {
            $this->cases = new ArrayCollection(
                array_merge(
                    $this->cases->toArray(),
                    $cases->toArray()
                )
            );
        } elseif (!$this->cases->contains($cases)) {
            $this->cases->add($cases);
        }

        return $this;
    }

    /**
     * Remove a cases
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $cases collection being removed
     *
     * @return Application
     */
    public function removeCases($cases)
    {
        if ($this->cases->contains($cases)) {
            $this->cases->removeElement($cases);
        }

        return $this;
    }

    /**
     * Set the condition undertaking
     *
     * @param ArrayCollection $conditionUndertakings collection being set as the value
     *
     * @return Application
     */
    public function setConditionUndertakings($conditionUndertakings)
    {
        $this->conditionUndertakings = $conditionUndertakings;

        return $this;
    }

    /**
     * Get the condition undertakings
     *
     * @return ArrayCollection
     */
    public function getConditionUndertakings()
    {
        return $this->conditionUndertakings;
    }

    /**
     * Add a condition undertakings
     *
     * @param ArrayCollection|mixed $conditionUndertakings collection being added
     *
     * @return Application
     */
    public function addConditionUndertakings($conditionUndertakings)
    {
        if ($conditionUndertakings instanceof ArrayCollection) {
            $this->conditionUndertakings = new ArrayCollection(
                array_merge(
                    $this->conditionUndertakings->toArray(),
                    $conditionUndertakings->toArray()
                )
            );
        } elseif (!$this->conditionUndertakings->contains($conditionUndertakings)) {
            $this->conditionUndertakings->add($conditionUndertakings);
        }

        return $this;
    }

    /**
     * Remove a condition undertakings
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $conditionUndertakings collection being removed
     *
     * @return Application
     */
    public function removeConditionUndertakings($conditionUndertakings)
    {
        if ($this->conditionUndertakings->contains($conditionUndertakings)) {
            $this->conditionUndertakings->removeElement($conditionUndertakings);
        }

        return $this;
    }

    /**
     * Set the document
     *
     * @param ArrayCollection $documents collection being set as the value
     *
     * @return Application
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * Get the documents
     *
     * @return ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Add a documents
     *
     * @param ArrayCollection|mixed $documents collection being added
     *
     * @return Application
     */
    public function addDocuments($documents)
    {
        if ($documents instanceof ArrayCollection) {
            $this->documents = new ArrayCollection(
                array_merge(
                    $this->documents->toArray(),
                    $documents->toArray()
                )
            );
        } elseif (!$this->documents->contains($documents)) {
            $this->documents->add($documents);
        }

        return $this;
    }

    /**
     * Remove a documents
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $documents collection being removed
     *
     * @return Application
     */
    public function removeDocuments($documents)
    {
        if ($this->documents->contains($documents)) {
            $this->documents->removeElement($documents);
        }

        return $this;
    }

    /**
     * Set the fee
     *
     * @param ArrayCollection $fees collection being set as the value
     *
     * @return Application
     */
    public function setFees($fees)
    {
        $this->fees = $fees;

        return $this;
    }

    /**
     * Get the fees
     *
     * @return ArrayCollection
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * Add a fees
     *
     * @param ArrayCollection|mixed $fees collection being added
     *
     * @return Application
     */
    public function addFees($fees)
    {
        if ($fees instanceof ArrayCollection) {
            $this->fees = new ArrayCollection(
                array_merge(
                    $this->fees->toArray(),
                    $fees->toArray()
                )
            );
        } elseif (!$this->fees->contains($fees)) {
            $this->fees->add($fees);
        }

        return $this;
    }

    /**
     * Remove a fees
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $fees collection being removed
     *
     * @return Application
     */
    public function removeFees($fees)
    {
        if ($this->fees->contains($fees)) {
            $this->fees->removeElement($fees);
        }

        return $this;
    }

    /**
     * Set the licence vehicle
     *
     * @param ArrayCollection $licenceVehicles collection being set as the value
     *
     * @return Application
     */
    public function setLicenceVehicles($licenceVehicles)
    {
        $this->licenceVehicles = $licenceVehicles;

        return $this;
    }

    /**
     * Get the licence vehicles
     *
     * @return ArrayCollection
     */
    public function getLicenceVehicles()
    {
        return $this->licenceVehicles;
    }

    /**
     * Add a licence vehicles
     *
     * @param ArrayCollection|mixed $licenceVehicles collection being added
     *
     * @return Application
     */
    public function addLicenceVehicles($licenceVehicles)
    {
        if ($licenceVehicles instanceof ArrayCollection) {
            $this->licenceVehicles = new ArrayCollection(
                array_merge(
                    $this->licenceVehicles->toArray(),
                    $licenceVehicles->toArray()
                )
            );
        } elseif (!$this->licenceVehicles->contains($licenceVehicles)) {
            $this->licenceVehicles->add($licenceVehicles);
        }

        return $this;
    }

    /**
     * Remove a licence vehicles
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $licenceVehicles collection being removed
     *
     * @return Application
     */
    public function removeLicenceVehicles($licenceVehicles)
    {
        if ($this->licenceVehicles->contains($licenceVehicles)) {
            $this->licenceVehicles->removeElement($licenceVehicles);
        }

        return $this;
    }

    /**
     * Set the interim licence vehicle
     *
     * @param ArrayCollection $interimLicenceVehicles collection being set as the value
     *
     * @return Application
     */
    public function setInterimLicenceVehicles($interimLicenceVehicles)
    {
        $this->interimLicenceVehicles = $interimLicenceVehicles;

        return $this;
    }

    /**
     * Get the interim licence vehicles
     *
     * @return ArrayCollection
     */
    public function getInterimLicenceVehicles()
    {
        return $this->interimLicenceVehicles;
    }

    /**
     * Add a interim licence vehicles
     *
     * @param ArrayCollection|mixed $interimLicenceVehicles collection being added
     *
     * @return Application
     */
    public function addInterimLicenceVehicles($interimLicenceVehicles)
    {
        if ($interimLicenceVehicles instanceof ArrayCollection) {
            $this->interimLicenceVehicles = new ArrayCollection(
                array_merge(
                    $this->interimLicenceVehicles->toArray(),
                    $interimLicenceVehicles->toArray()
                )
            );
        } elseif (!$this->interimLicenceVehicles->contains($interimLicenceVehicles)) {
            $this->interimLicenceVehicles->add($interimLicenceVehicles);
        }

        return $this;
    }

    /**
     * Remove a interim licence vehicles
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $interimLicenceVehicles collection being removed
     *
     * @return Application
     */
    public function removeInterimLicenceVehicles($interimLicenceVehicles)
    {
        if ($this->interimLicenceVehicles->contains($interimLicenceVehicles)) {
            $this->interimLicenceVehicles->removeElement($interimLicenceVehicles);
        }

        return $this;
    }

    /**
     * Set the other licence
     *
     * @param ArrayCollection $otherLicences collection being set as the value
     *
     * @return Application
     */
    public function setOtherLicences($otherLicences)
    {
        $this->otherLicences = $otherLicences;

        return $this;
    }

    /**
     * Get the other licences
     *
     * @return ArrayCollection
     */
    public function getOtherLicences()
    {
        return $this->otherLicences;
    }

    /**
     * Add a other licences
     *
     * @param ArrayCollection|mixed $otherLicences collection being added
     *
     * @return Application
     */
    public function addOtherLicences($otherLicences)
    {
        if ($otherLicences instanceof ArrayCollection) {
            $this->otherLicences = new ArrayCollection(
                array_merge(
                    $this->otherLicences->toArray(),
                    $otherLicences->toArray()
                )
            );
        } elseif (!$this->otherLicences->contains($otherLicences)) {
            $this->otherLicences->add($otherLicences);
        }

        return $this;
    }

    /**
     * Remove a other licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $otherLicences collection being removed
     *
     * @return Application
     */
    public function removeOtherLicences($otherLicences)
    {
        if ($this->otherLicences->contains($otherLicences)) {
            $this->otherLicences->removeElement($otherLicences);
        }

        return $this;
    }

    /**
     * Set the previous conviction
     *
     * @param ArrayCollection $previousConvictions collection being set as the value
     *
     * @return Application
     */
    public function setPreviousConvictions($previousConvictions)
    {
        $this->previousConvictions = $previousConvictions;

        return $this;
    }

    /**
     * Get the previous convictions
     *
     * @return ArrayCollection
     */
    public function getPreviousConvictions()
    {
        return $this->previousConvictions;
    }

    /**
     * Add a previous convictions
     *
     * @param ArrayCollection|mixed $previousConvictions collection being added
     *
     * @return Application
     */
    public function addPreviousConvictions($previousConvictions)
    {
        if ($previousConvictions instanceof ArrayCollection) {
            $this->previousConvictions = new ArrayCollection(
                array_merge(
                    $this->previousConvictions->toArray(),
                    $previousConvictions->toArray()
                )
            );
        } elseif (!$this->previousConvictions->contains($previousConvictions)) {
            $this->previousConvictions->add($previousConvictions);
        }

        return $this;
    }

    /**
     * Remove a previous convictions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $previousConvictions collection being removed
     *
     * @return Application
     */
    public function removePreviousConvictions($previousConvictions)
    {
        if ($this->previousConvictions->contains($previousConvictions)) {
            $this->previousConvictions->removeElement($previousConvictions);
        }

        return $this;
    }

    /**
     * Set the publication link
     *
     * @param ArrayCollection $publicationLinks collection being set as the value
     *
     * @return Application
     */
    public function setPublicationLinks($publicationLinks)
    {
        $this->publicationLinks = $publicationLinks;

        return $this;
    }

    /**
     * Get the publication links
     *
     * @return ArrayCollection
     */
    public function getPublicationLinks()
    {
        return $this->publicationLinks;
    }

    /**
     * Add a publication links
     *
     * @param ArrayCollection|mixed $publicationLinks collection being added
     *
     * @return Application
     */
    public function addPublicationLinks($publicationLinks)
    {
        if ($publicationLinks instanceof ArrayCollection) {
            $this->publicationLinks = new ArrayCollection(
                array_merge(
                    $this->publicationLinks->toArray(),
                    $publicationLinks->toArray()
                )
            );
        } elseif (!$this->publicationLinks->contains($publicationLinks)) {
            $this->publicationLinks->add($publicationLinks);
        }

        return $this;
    }

    /**
     * Remove a publication links
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $publicationLinks collection being removed
     *
     * @return Application
     */
    public function removePublicationLinks($publicationLinks)
    {
        if ($this->publicationLinks->contains($publicationLinks)) {
            $this->publicationLinks->removeElement($publicationLinks);
        }

        return $this;
    }

    /**
     * Set the s4
     *
     * @param ArrayCollection $s4s collection being set as the value
     *
     * @return Application
     */
    public function setS4s($s4s)
    {
        $this->s4s = $s4s;

        return $this;
    }

    /**
     * Get the s4s
     *
     * @return ArrayCollection
     */
    public function getS4s()
    {
        return $this->s4s;
    }

    /**
     * Add a s4s
     *
     * @param ArrayCollection|mixed $s4s collection being added
     *
     * @return Application
     */
    public function addS4s($s4s)
    {
        if ($s4s instanceof ArrayCollection) {
            $this->s4s = new ArrayCollection(
                array_merge(
                    $this->s4s->toArray(),
                    $s4s->toArray()
                )
            );
        } elseif (!$this->s4s->contains($s4s)) {
            $this->s4s->add($s4s);
        }

        return $this;
    }

    /**
     * Remove a s4s
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $s4s collection being removed
     *
     * @return Application
     */
    public function removeS4s($s4s)
    {
        if ($this->s4s->contains($s4s)) {
            $this->s4s->removeElement($s4s);
        }

        return $this;
    }

    /**
     * Set the task
     *
     * @param ArrayCollection $tasks collection being set as the value
     *
     * @return Application
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;

        return $this;
    }

    /**
     * Get the tasks
     *
     * @return ArrayCollection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Add a tasks
     *
     * @param ArrayCollection|mixed $tasks collection being added
     *
     * @return Application
     */
    public function addTasks($tasks)
    {
        if ($tasks instanceof ArrayCollection) {
            $this->tasks = new ArrayCollection(
                array_merge(
                    $this->tasks->toArray(),
                    $tasks->toArray()
                )
            );
        } elseif (!$this->tasks->contains($tasks)) {
            $this->tasks->add($tasks);
        }

        return $this;
    }

    /**
     * Remove a tasks
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tasks collection being removed
     *
     * @return Application
     */
    public function removeTasks($tasks)
    {
        if ($this->tasks->contains($tasks)) {
            $this->tasks->removeElement($tasks);
        }

        return $this;
    }

    /**
     * Set the transport manager
     *
     * @param ArrayCollection $transportManagers collection being set as the value
     *
     * @return Application
     */
    public function setTransportManagers($transportManagers)
    {
        $this->transportManagers = $transportManagers;

        return $this;
    }

    /**
     * Get the transport managers
     *
     * @return ArrayCollection
     */
    public function getTransportManagers()
    {
        return $this->transportManagers;
    }

    /**
     * Add a transport managers
     *
     * @param ArrayCollection|mixed $transportManagers collection being added
     *
     * @return Application
     */
    public function addTransportManagers($transportManagers)
    {
        if ($transportManagers instanceof ArrayCollection) {
            $this->transportManagers = new ArrayCollection(
                array_merge(
                    $this->transportManagers->toArray(),
                    $transportManagers->toArray()
                )
            );
        } elseif (!$this->transportManagers->contains($transportManagers)) {
            $this->transportManagers->add($transportManagers);
        }

        return $this;
    }

    /**
     * Remove a transport managers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $transportManagers collection being removed
     *
     * @return Application
     */
    public function removeTransportManagers($transportManagers)
    {
        if ($this->transportManagers->contains($transportManagers)) {
            $this->transportManagers->removeElement($transportManagers);
        }

        return $this;
    }
}
