<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\System;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractDiscSequence Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="disc_sequence",
 *    indexes={
 *        @ORM\Index(name="ix_disc_sequence_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_disc_sequence_goods_or_psv", columns={"goods_or_psv"}),
 *        @ORM\Index(name="ix_disc_sequence_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_disc_sequence_traffic_area_id", columns={"traffic_area_id"})
 *    }
 * )
 */
abstract class AbstractDiscSequence implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Primary key
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     */
    protected $id = 0;

    /**
     * GoodsOrPsv
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="goods_or_psv", referencedColumnName="id")
     */
    protected $goodsOrPsv;

    /**
     * Foreign Key to traffic_area
     *
     * @var \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea", fetch="LAZY")
     * @ORM\JoinColumn(name="traffic_area_id", referencedColumnName="id", nullable=true)
     */
    protected $trafficArea;

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
     * Restricted
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="restricted", nullable=true)
     */
    protected $restricted;

    /**
     * Special restricted
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="special_restricted", nullable=true)
     */
    protected $specialRestricted;

    /**
     * Standard national
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="standard_national", nullable=true)
     */
    protected $standardNational;

    /**
     * Standard international
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="standard_international", nullable=true)
     */
    protected $standardInternational;

    /**
     * R prefix
     *
     * @var string
     *
     * @ORM\Column(type="string", name="r_prefix", length=3, nullable=true)
     */
    protected $rPrefix;

    /**
     * Sr prefix
     *
     * @var string
     *
     * @ORM\Column(type="string", name="sr_prefix", length=3, nullable=true)
     */
    protected $srPrefix;

    /**
     * Sn prefix
     *
     * @var string
     *
     * @ORM\Column(type="string", name="sn_prefix", length=3, nullable=true)
     */
    protected $snPrefix;

    /**
     * Si prefix
     *
     * @var string
     *
     * @ORM\Column(type="string", name="si_prefix", length=3, nullable=true)
     */
    protected $siPrefix;

    /**
     * isSelfServe
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_self_serve", nullable=false, options={"default": 0})
     */
    protected $isSelfServe = 0;

    /**
     * isNiSelfServe
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_ni_self_serve", nullable=false, options={"default": 0})
     */
    protected $isNiSelfServe = 0;

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
     * Initialise the collections
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise collections
     */
    public function initCollections(): void
    {
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return DiscSequence
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the goods or psv
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $goodsOrPsv new value being set
     *
     * @return DiscSequence
     */
    public function setGoodsOrPsv($goodsOrPsv)
    {
        $this->goodsOrPsv = $goodsOrPsv;

        return $this;
    }

    /**
     * Get the goods or psv
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getGoodsOrPsv()
    {
        return $this->goodsOrPsv;
    }

    /**
     * Set the traffic area
     *
     * @param \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea $trafficArea new value being set
     *
     * @return DiscSequence
     */
    public function setTrafficArea($trafficArea)
    {
        $this->trafficArea = $trafficArea;

        return $this;
    }

    /**
     * Get the traffic area
     *
     * @return \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return DiscSequence
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy new value being set
     *
     * @return DiscSequence
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the restricted
     *
     * @param int $restricted new value being set
     *
     * @return DiscSequence
     */
    public function setRestricted($restricted)
    {
        $this->restricted = $restricted;

        return $this;
    }

    /**
     * Get the restricted
     *
     * @return int     */
    public function getRestricted()
    {
        return $this->restricted;
    }

    /**
     * Set the special restricted
     *
     * @param int $specialRestricted new value being set
     *
     * @return DiscSequence
     */
    public function setSpecialRestricted($specialRestricted)
    {
        $this->specialRestricted = $specialRestricted;

        return $this;
    }

    /**
     * Get the special restricted
     *
     * @return int     */
    public function getSpecialRestricted()
    {
        return $this->specialRestricted;
    }

    /**
     * Set the standard national
     *
     * @param int $standardNational new value being set
     *
     * @return DiscSequence
     */
    public function setStandardNational($standardNational)
    {
        $this->standardNational = $standardNational;

        return $this;
    }

    /**
     * Get the standard national
     *
     * @return int     */
    public function getStandardNational()
    {
        return $this->standardNational;
    }

    /**
     * Set the standard international
     *
     * @param int $standardInternational new value being set
     *
     * @return DiscSequence
     */
    public function setStandardInternational($standardInternational)
    {
        $this->standardInternational = $standardInternational;

        return $this;
    }

    /**
     * Get the standard international
     *
     * @return int     */
    public function getStandardInternational()
    {
        return $this->standardInternational;
    }

    /**
     * Set the r prefix
     *
     * @param string $rPrefix new value being set
     *
     * @return DiscSequence
     */
    public function setRPrefix($rPrefix)
    {
        $this->rPrefix = $rPrefix;

        return $this;
    }

    /**
     * Get the r prefix
     *
     * @return string     */
    public function getRPrefix()
    {
        return $this->rPrefix;
    }

    /**
     * Set the sr prefix
     *
     * @param string $srPrefix new value being set
     *
     * @return DiscSequence
     */
    public function setSrPrefix($srPrefix)
    {
        $this->srPrefix = $srPrefix;

        return $this;
    }

    /**
     * Get the sr prefix
     *
     * @return string     */
    public function getSrPrefix()
    {
        return $this->srPrefix;
    }

    /**
     * Set the sn prefix
     *
     * @param string $snPrefix new value being set
     *
     * @return DiscSequence
     */
    public function setSnPrefix($snPrefix)
    {
        $this->snPrefix = $snPrefix;

        return $this;
    }

    /**
     * Get the sn prefix
     *
     * @return string     */
    public function getSnPrefix()
    {
        return $this->snPrefix;
    }

    /**
     * Set the si prefix
     *
     * @param string $siPrefix new value being set
     *
     * @return DiscSequence
     */
    public function setSiPrefix($siPrefix)
    {
        $this->siPrefix = $siPrefix;

        return $this;
    }

    /**
     * Get the si prefix
     *
     * @return string     */
    public function getSiPrefix()
    {
        return $this->siPrefix;
    }

    /**
     * Set the is self serve
     *
     * @param string $isSelfServe new value being set
     *
     * @return DiscSequence
     */
    public function setIsSelfServe($isSelfServe)
    {
        $this->isSelfServe = $isSelfServe;

        return $this;
    }

    /**
     * Get the is self serve
     *
     * @return string     */
    public function getIsSelfServe()
    {
        return $this->isSelfServe;
    }

    /**
     * Set the is ni self serve
     *
     * @param string $isNiSelfServe new value being set
     *
     * @return DiscSequence
     */
    public function setIsNiSelfServe($isNiSelfServe)
    {
        $this->isNiSelfServe = $isNiSelfServe;

        return $this;
    }

    /**
     * Get the is ni self serve
     *
     * @return string     */
    public function getIsNiSelfServe()
    {
        return $this->isNiSelfServe;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return DiscSequence
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return int     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
