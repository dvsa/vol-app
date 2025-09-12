<?php

namespace Dvsa\Olcs\Api\Entity\Pi;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * PiSlaException Abstract Entity
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="pi_sla_exception",
 *    indexes={
 *        @ORM\Index(name="ix_pi_sla_exception_pi_id", columns={"pi_id"}),
 *        @ORM\Index(name="ix_pi_sla_exception_pi_exception_id", columns={"pi_exception_id"}),
 *        @ORM\Index(name="ix_pi_sla_exception_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_pi_sla_exception_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractPiSlaException implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

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
     * PI
     *
     * @var \Dvsa\Olcs\Api\Entity\Pi\Pi
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Pi\Pi", fetch="LAZY")
     * @ORM\JoinColumn(name="pi_id", referencedColumnName="id", nullable=false)
     */
    protected $pi;

    /**
     * PI Exception (SLA Exception)
     *
     * @var \Dvsa\Olcs\Api\Entity\Pi\SlaException
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Pi\SlaException", fetch="LAZY")
     * @ORM\JoinColumn(name="pi_exception_id", referencedColumnName="id", nullable=false)
     */
    protected $piException;

    /**
     * Created by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=false)
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
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=false, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Set the PI
     *
     * @param \Dvsa\Olcs\Api\Entity\Pi\Pi $pi entity being set as the value
     *
     * @return AbstractPiSlaException
     */
    public function setPi($pi)
    {
        $this->pi = $pi;

        return $this;
    }

    /**
     * Get the PI
     *
     * @return \Dvsa\Olcs\Api\Entity\Pi\Pi
     */
    public function getPi()
    {
        return $this->pi;
    }

    /**
     * Set the PI Exception
     *
     * @param \Dvsa\Olcs\Api\Entity\Pi\SlaException $piException entity being set as the value
     *
     * @return AbstractPiSlaException
     */
    public function setPiException($piException)
    {
        $this->piException = $piException;

        return $this;
    }

    /**
     * Get the PI Exception
     *
     * @return \Dvsa\Olcs\Api\Entity\Pi\SlaException
     */
    public function getPiException()
    {
        return $this->piException;
    }

}
