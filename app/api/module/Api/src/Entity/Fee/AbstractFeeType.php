<?php

namespace Dvsa\Olcs\Api\Entity\Fee;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * FeeType Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="fee_type",
 *    indexes={
 *        @ORM\Index(name="ix_fee_type_accrual_rule", columns={"accrual_rule"}),
 *        @ORM\Index(name="ix_fee_type_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_fee_type_fee_type", columns={"fee_type"}),
 *        @ORM\Index(name="ix_fee_type_goods_or_psv", columns={"goods_or_psv"}),
 *        @ORM\Index(name="ix_fee_type_irfo_fee_type", columns={"irfo_fee_type"}),
 *        @ORM\Index(name="ix_fee_type_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_fee_type_licence_type", columns={"licence_type"}),
 *        @ORM\Index(name="ix_fee_type_traffic_area_id", columns={"traffic_area_id"})
 *    }
 * )
 */
abstract class AbstractFeeType implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Accrual rule
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="accrual_rule", referencedColumnName="id", nullable=false)
     */
    protected $accrualRule;

    /**
     * Annual value
     *
     * @var float
     *
     * @ORM\Column(type="decimal", name="annual_value", precision=10, scale=2, nullable=true)
     */
    protected $annualValue;

    /**
     * Cost centre ref
     *
     * @var string
     *
     * @ORM\Column(type="string", name="cost_centre_ref", length=50, nullable=true)
     */
    protected $costCentreRef;

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
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=255, nullable=false)
     */
    protected $description;

    /**
     * Effective from
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="effective_from", nullable=false)
     */
    protected $effectiveFrom;

    /**
     * Expire fee with licence
     *
     * @var string
     *
     * @ORM\Column(type="yesno",
     *     name="expire_fee_with_licence",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $expireFeeWithLicence = 0;

    /**
     * Fee type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="fee_type", referencedColumnName="id", nullable=false)
     */
    protected $feeType;

    /**
     * Five year value
     *
     * @var float
     *
     * @ORM\Column(type="decimal", name="five_year_value", precision=10, scale=2, nullable=true)
     */
    protected $fiveYearValue;

    /**
     * Fixed value
     *
     * @var float
     *
     * @ORM\Column(type="decimal", name="fixed_value", precision=10, scale=2, nullable=true)
     */
    protected $fixedValue;

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
     * Irfo fee type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="irfo_fee_type", referencedColumnName="id", nullable=true)
     */
    protected $irfoFeeType;

    /**
     * Is miscellaneous
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_miscellaneous", nullable=false, options={"default": 0})
     */
    protected $isMiscellaneous = 0;

    /**
     * Is ni
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_ni", nullable=false, options={"default": 0})
     */
    protected $isNi = 0;

    /**
     * Is visible in internal
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean",
     *     name="is_visible_in_internal",
     *     nullable=false,
     *     options={"default": 1})
     */
    protected $isVisibleInInternal = 1;

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
     * Licence type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_type", referencedColumnName="id", nullable=true)
     */
    protected $licenceType;

    /**
     * Product reference
     *
     * @var string
     *
     * @ORM\Column(type="string", name="product_reference", length=30, nullable=true)
     */
    protected $productReference;

    /**
     * Traffic area
     *
     * @var \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea", fetch="LAZY")
     * @ORM\JoinColumn(name="traffic_area_id", referencedColumnName="id", nullable=true)
     */
    protected $trafficArea;

    /**
     * Vat code
     *
     * @var string
     *
     * @ORM\Column(type="string", name="vat_code", length=1, nullable=true)
     */
    protected $vatCode;

    /**
     * Vat rate
     *
     * @var float
     *
     * @ORM\Column(type="decimal",
     *     name="vat_rate",
     *     precision=5,
     *     scale=2,
     *     nullable=false,
     *     options={"default": 0.00})
     */
    protected $vatRate = 0.00;

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
     * Set the accrual rule
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $accrualRule entity being set as the value
     *
     * @return FeeType
     */
    public function setAccrualRule($accrualRule)
    {
        $this->accrualRule = $accrualRule;

        return $this;
    }

    /**
     * Get the accrual rule
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getAccrualRule()
    {
        return $this->accrualRule;
    }

    /**
     * Set the annual value
     *
     * @param float $annualValue new value being set
     *
     * @return FeeType
     */
    public function setAnnualValue($annualValue)
    {
        $this->annualValue = $annualValue;

        return $this;
    }

    /**
     * Get the annual value
     *
     * @return float
     */
    public function getAnnualValue()
    {
        return $this->annualValue;
    }

    /**
     * Set the cost centre ref
     *
     * @param string $costCentreRef new value being set
     *
     * @return FeeType
     */
    public function setCostCentreRef($costCentreRef)
    {
        $this->costCentreRef = $costCentreRef;

        return $this;
    }

    /**
     * Get the cost centre ref
     *
     * @return string
     */
    public function getCostCentreRef()
    {
        return $this->costCentreRef;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return FeeType
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
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return FeeType
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the effective from
     *
     * @param \DateTime $effectiveFrom new value being set
     *
     * @return FeeType
     */
    public function setEffectiveFrom($effectiveFrom)
    {
        $this->effectiveFrom = $effectiveFrom;

        return $this;
    }

    /**
     * Get the effective from
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getEffectiveFrom($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->effectiveFrom);
        }

        return $this->effectiveFrom;
    }

    /**
     * Set the expire fee with licence
     *
     * @param string $expireFeeWithLicence new value being set
     *
     * @return FeeType
     */
    public function setExpireFeeWithLicence($expireFeeWithLicence)
    {
        $this->expireFeeWithLicence = $expireFeeWithLicence;

        return $this;
    }

    /**
     * Get the expire fee with licence
     *
     * @return string
     */
    public function getExpireFeeWithLicence()
    {
        return $this->expireFeeWithLicence;
    }

    /**
     * Set the fee type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $feeType entity being set as the value
     *
     * @return FeeType
     */
    public function setFeeType($feeType)
    {
        $this->feeType = $feeType;

        return $this;
    }

    /**
     * Get the fee type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getFeeType()
    {
        return $this->feeType;
    }

    /**
     * Set the five year value
     *
     * @param float $fiveYearValue new value being set
     *
     * @return FeeType
     */
    public function setFiveYearValue($fiveYearValue)
    {
        $this->fiveYearValue = $fiveYearValue;

        return $this;
    }

    /**
     * Get the five year value
     *
     * @return float
     */
    public function getFiveYearValue()
    {
        return $this->fiveYearValue;
    }

    /**
     * Set the fixed value
     *
     * @param float $fixedValue new value being set
     *
     * @return FeeType
     */
    public function setFixedValue($fixedValue)
    {
        $this->fixedValue = $fixedValue;

        return $this;
    }

    /**
     * Get the fixed value
     *
     * @return float
     */
    public function getFixedValue()
    {
        return $this->fixedValue;
    }

    /**
     * Set the goods or psv
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $goodsOrPsv entity being set as the value
     *
     * @return FeeType
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
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return FeeType
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
     * Set the irfo fee type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $irfoFeeType entity being set as the value
     *
     * @return FeeType
     */
    public function setIrfoFeeType($irfoFeeType)
    {
        $this->irfoFeeType = $irfoFeeType;

        return $this;
    }

    /**
     * Get the irfo fee type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getIrfoFeeType()
    {
        return $this->irfoFeeType;
    }

    /**
     * Set the is miscellaneous
     *
     * @param boolean $isMiscellaneous new value being set
     *
     * @return FeeType
     */
    public function setIsMiscellaneous($isMiscellaneous)
    {
        $this->isMiscellaneous = $isMiscellaneous;

        return $this;
    }

    /**
     * Get the is miscellaneous
     *
     * @return boolean
     */
    public function getIsMiscellaneous()
    {
        return $this->isMiscellaneous;
    }

    /**
     * Set the is ni
     *
     * @param string $isNi new value being set
     *
     * @return FeeType
     */
    public function setIsNi($isNi)
    {
        $this->isNi = $isNi;

        return $this;
    }

    /**
     * Get the is ni
     *
     * @return string
     */
    public function getIsNi()
    {
        return $this->isNi;
    }

    /**
     * Set the is visible in internal
     *
     * @param boolean $isVisibleInInternal new value being set
     *
     * @return FeeType
     */
    public function setIsVisibleInInternal($isVisibleInInternal)
    {
        $this->isVisibleInInternal = $isVisibleInInternal;

        return $this;
    }

    /**
     * Get the is visible in internal
     *
     * @return boolean
     */
    public function getIsVisibleInInternal()
    {
        return $this->isVisibleInInternal;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return FeeType
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
     * Set the licence type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $licenceType entity being set as the value
     *
     * @return FeeType
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
     * Set the product reference
     *
     * @param string $productReference new value being set
     *
     * @return FeeType
     */
    public function setProductReference($productReference)
    {
        $this->productReference = $productReference;

        return $this;
    }

    /**
     * Get the product reference
     *
     * @return string
     */
    public function getProductReference()
    {
        return $this->productReference;
    }

    /**
     * Set the traffic area
     *
     * @param \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea $trafficArea entity being set as the value
     *
     * @return FeeType
     */
    public function setTrafficArea($trafficArea)
    {
        $this->trafficArea = $trafficArea;

        return $this;
    }

    /**
     * Get the traffic area
     *
     * @return \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea
     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }

    /**
     * Set the vat code
     *
     * @param string $vatCode new value being set
     *
     * @return FeeType
     */
    public function setVatCode($vatCode)
    {
        $this->vatCode = $vatCode;

        return $this;
    }

    /**
     * Get the vat code
     *
     * @return string
     */
    public function getVatCode()
    {
        return $this->vatCode;
    }

    /**
     * Set the vat rate
     *
     * @param float $vatRate new value being set
     *
     * @return FeeType
     */
    public function setVatRate($vatRate)
    {
        $this->vatRate = $vatRate;

        return $this;
    }

    /**
     * Get the vat rate
     *
     * @return float
     */
    public function getVatRate()
    {
        return $this->vatRate;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return FeeType
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
}
