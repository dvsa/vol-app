<?php

namespace Dvsa\Olcs\Api\Entity\PrintScan;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * TeamPrinter Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="team_printer",
 *    indexes={
 *        @ORM\Index(name="ix_team_printer_printer_id", columns={"printer_id"}),
 *        @ORM\Index(name="ix_team_printer_sub_category_id", columns={"sub_category_id"}),
 *        @ORM\Index(name="ix_team_printer_team_id", columns={"team_id"}),
 *        @ORM\Index(name="ix_team_printer_user_id", columns={"user_id"})
 *    }
 * )
 */
abstract class AbstractTeamPrinter implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use SoftDeletableTrait;

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
     * Printer
     *
     * @var \Dvsa\Olcs\Api\Entity\PrintScan\Printer
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\PrintScan\Printer",
     *     fetch="LAZY",
     *     inversedBy="teamPrinters"
     * )
     * @ORM\JoinColumn(name="printer_id", referencedColumnName="id", nullable=false)
     */
    protected $printer;

    /**
     * Sub category
     *
     * @var \Dvsa\Olcs\Api\Entity\System\SubCategory
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\SubCategory", fetch="LAZY")
     * @ORM\JoinColumn(name="sub_category_id", referencedColumnName="id", nullable=true)
     */
    protected $subCategory;

    /**
     * Team
     *
     * @var \Dvsa\Olcs\Api\Entity\User\Team
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\User\Team",
     *     fetch="LAZY",
     *     inversedBy="teamPrinters"
     * )
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id", nullable=false)
     */
    protected $team;

    /**
     * User
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $user;

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
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return TeamPrinter
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
     * Set the printer
     *
     * @param \Dvsa\Olcs\Api\Entity\PrintScan\Printer $printer entity being set as the value
     *
     * @return TeamPrinter
     */
    public function setPrinter($printer)
    {
        $this->printer = $printer;

        return $this;
    }

    /**
     * Get the printer
     *
     * @return \Dvsa\Olcs\Api\Entity\PrintScan\Printer
     */
    public function getPrinter()
    {
        return $this->printer;
    }

    /**
     * Set the sub category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\SubCategory $subCategory entity being set as the value
     *
     * @return TeamPrinter
     */
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    /**
     * Get the sub category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\SubCategory
     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * Set the team
     *
     * @param \Dvsa\Olcs\Api\Entity\User\Team $team entity being set as the value
     *
     * @return TeamPrinter
     */
    public function setTeam($team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get the team
     *
     * @return \Dvsa\Olcs\Api\Entity\User\Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set the user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $user entity being set as the value
     *
     * @return TeamPrinter
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

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return TeamPrinter
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
