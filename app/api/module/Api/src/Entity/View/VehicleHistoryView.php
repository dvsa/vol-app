<?php

/**
 * Vehicle History View
 *
 * @NOTE: This walks and talks like an entity but be warned, it is backed
 * by a view. As such it is is nicely readable and searchable, but writes
 * are a no go.
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Entity\View;

use Doctrine\ORM\Mapping as ORM;

/**
 * Task Search View
 *
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vehicle_history_view")
 */
class VehicleHistoryView
{
    /**
     * Id
     *
     * @var int
     *
     * NOTE: The ID annotation here is to allow doctrine to create the table (Even though we remove it later)
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     */
    protected $id;

    /**
     * Vrm
     *
     * @var string
     *
     * @ORM\Column(type="string", name="vrm", length=20, nullable=true)
     */
    protected $vrm;

    /**
     * Licence Number
     *
     * @var string
     *
     * @ORM\Column(type="string", name="lic_no")
     */
    protected $licenceNo;

    /**
     * Specified date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="specified_date")
     */
    protected $specifiedDate;

    /**
     * Removal date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="removal_date")
     */
    protected $removalDate;

    /**
     * Issued date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="issued_date")
     */
    protected $issuedDate;

    /**
     * Ceased date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="ceased_date")
     */
    protected $ceasedDate;

    /**
     * Disc No
     *
     * @var string
     *
     * @ORM\Column(type="string", name="disc_no", length=20, nullable=true)
     */
    protected $discNo;

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
     * Get the specified date
     *
     * @return \DateTime
     */
    public function getSpecifiedDate()
    {
        return $this->specifiedDate;
    }

    /**
     * Get the deleted date
     *
     * @return \DateTime
     */
    public function getRemovalDate()
    {
        return $this->removalDate;
    }

    /**
     * Get the issue date
     *
     * @return \DateTime
     */
    public function getIssuedDate()
    {
        return $this->issuedDate;
    }

    /**
     * Get the cease date
     *
     * @return \DateTime
     */
    public function getCeasedDate()
    {
        return $this->ceasedDate;
    }

    /**
     * Get the VRM
     *
     * @return string
     */
    public function getVrm()
    {
        return $this->vrm;
    }

    /**
     * Get the Licence Number
     *
     * @return string
     */
    public function getLicenceNo()
    {
        return $this->licenceNo;
    }

    /**
     * Get the disc no
     *
     * @return string
     */
    public function getDiscNo()
    {
        return $this->discNo;
    }
}
