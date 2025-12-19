<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Organisation;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * AbstractOrganisationType Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="organisation_type",
 *    indexes={
 *        @ORM\Index(name="ix_organisation_type_org_person_type_id", columns={"org_person_type_id"}),
 *        @ORM\Index(name="ix_organisation_type_org_type_id", columns={"org_type_id"}),
 *        @ORM\Index(name="uk_organisation_type_org_type_id_org_person_type_id", columns={"org_type_id", "org_person_type_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_organisation_type_org_type_id_org_person_type_id", columns={"org_type_id", "org_person_type_id"})
 *    }
 * )
 */
abstract class AbstractOrganisationType implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;

    /**
     * Primary key.  Auto incremented if numeric.
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * LTD, Partnership etc.
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="org_type_id", referencedColumnName="id")
     */
    protected $orgType;

    /**
     * Type if officers in org. Partners, directors etc.
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="org_person_type_id", referencedColumnName="id")
     */
    protected $orgPersonType;

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
     * @return OrganisationType
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
     * Set the org type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $orgType new value being set
     *
     * @return OrganisationType
     */
    public function setOrgType($orgType)
    {
        $this->orgType = $orgType;

        return $this;
    }

    /**
     * Get the org type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getOrgType()
    {
        return $this->orgType;
    }

    /**
     * Set the org person type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $orgPersonType new value being set
     *
     * @return OrganisationType
     */
    public function setOrgPersonType($orgPersonType)
    {
        $this->orgPersonType = $orgPersonType;

        return $this;
    }

    /**
     * Get the org person type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getOrgPersonType()
    {
        return $this->orgPersonType;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}