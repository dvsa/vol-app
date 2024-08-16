<?php

namespace Dvsa\Olcs\Api\Entity\Cases;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * CasesReadAudit Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="cases_read_audit",
 *    indexes={
 *        @ORM\Index(name="ix_cases_read_audit_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_cases_read_audit_created_on", columns={"created_on"}),
 *        @ORM\Index(name="ix_cases_read_audit_user_id", columns={"user_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_cases_read_audit_case_id_user_id_created_on",
     *     columns={"case_id","user_id","created_on"})
 *    }
 * )
 */
abstract class AbstractCasesReadAudit implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;

    /**
     * Case
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Cases
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases",
     *     fetch="LAZY",
     *     inversedBy="readAudits"
     * )
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id", nullable=false)
     */
    protected $case;

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
     * User
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * Set the case
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Cases $case entity being set as the value
     *
     * @return CasesReadAudit
     */
    public function setCase($case)
    {
        $this->case = $case;

        return $this;
    }

    /**
     * Get the case
     *
     * @return \Dvsa\Olcs\Api\Entity\Cases\Cases
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return CasesReadAudit
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
     * Set the user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $user entity being set as the value
     *
     * @return CasesReadAudit
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
