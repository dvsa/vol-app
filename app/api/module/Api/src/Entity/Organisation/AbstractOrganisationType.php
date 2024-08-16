<?php

namespace Dvsa\Olcs\Api\Entity\Organisation;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * OrganisationType Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="organisation_type",
 *    indexes={
 *        @ORM\Index(name="ix_organisation_type_org_person_type_id", columns={"org_person_type_id"}),
 *        @ORM\Index(name="ix_organisation_type_org_type_id", columns={"org_type_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_organisation_type_org_type_id_org_person_type_id",
     *     columns={"org_type_id","org_person_type_id"})
 *    }
 * )
 */
abstract class AbstractOrganisationType implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;

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
     * Org person type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="org_person_type_id", referencedColumnName="id", nullable=false)
     */
    protected $orgPersonType;

    /**
     * Org type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="org_type_id", referencedColumnName="id", nullable=false)
     */
    protected $orgType;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the org person type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $orgPersonType entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getOrgPersonType()
    {
        return $this->orgPersonType;
    }

    /**
     * Set the org type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $orgType entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getOrgType()
    {
        return $this->orgType;
    }
}
