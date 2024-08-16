<?php

/**
 * Create Vehicle List for Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Discs;

/**
 * Create Vehicle List for Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreatePsvVehicleListForDiscs extends \Dvsa\Olcs\Transfer\Command\AbstractCommand
{
    protected $id;

    protected $knownValues;

    protected $user;

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
    public function getKnownValues()
    {
        return $this->knownValues;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}
