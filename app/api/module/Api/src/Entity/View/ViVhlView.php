<?php

/**
 * VI Vehilce view
 *
 * @note Read only view, this entity has no setters
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Entity\View;

use Doctrine\ORM\Mapping as ORM;

/**
 * VI Vehilce view
 *
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vi_vhl_vw")
 */
class ViVhlView
{
    /**
     * Id
     *
     * @var int
     *
     * Note: The ID annotation here is to allow doctrine to create the table (Even though we remove it later)
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     */
    protected $id;

    /**
     * Licence ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="lic_id")
     */
    protected $licId;

    /**
     * Vehicle ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="vhl_id")
     */
    protected $vhlId;

    /**
     * VI line
     *
     * @var string
     *
     * @ORM\Column(type="string", name="vi_line")
     */
    protected $viLine;

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
     * Get the licence id
     *
     * @return int
     */
    public function getLicId()
    {
        return $this->licId;
    }

    /**
     * Get the vehicle id
     *
     * @return int
     */
    public function getVhlId()
    {
        return $this->vhlId;
    }

    /**
     * Get the VI line
     *
     * @return string
     */
    public function getViLine()
    {
        return $this->viLine;
    }
}
