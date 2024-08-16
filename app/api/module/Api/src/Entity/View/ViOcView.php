<?php

/**
 * VI Operating Centres view
 *
 * @note Read only view, this entity has no setters
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Entity\View;

use Doctrine\ORM\Mapping as ORM;

/**
 * VI Operating Centres view
 *
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vi_oc_vw")
 */
class ViOcView
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
     * Operating centre ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="oc_id")
     */
    protected $ocId;

    /**
     * Licence number
     *
     * @var string
     *
     * @ORM\Column(type="string", name="Col_placeholder1")
     */
    protected $placeholder;

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
     * Get the operating centre id
     *
     * @return int
     */
    public function getOcId()
    {
        return $this->ocId;
    }

    /**
     * Get the placeholder
     *
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }
}
