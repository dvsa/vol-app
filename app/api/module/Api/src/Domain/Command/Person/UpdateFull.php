<?php

/**
 * Update a Person
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Person;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Update a Person
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class UpdateFull extends AbstractCommand
{
    protected $id;

    protected $version;

    protected $firstName;

    protected $lastName;

    protected $title;

    protected $birthDate;

    protected $birthPlace;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @return mixed
     */
    public function getBirthPlace()
    {
        return $this->birthPlace;
    }
}
