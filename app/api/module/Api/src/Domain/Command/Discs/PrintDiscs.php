<?php

/**
 * Print Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Discs;

/**
/**
 * Print Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class PrintDiscs extends \Dvsa\Olcs\Transfer\Command\AbstractCommand
{
    protected $discs;

    protected $type;

    protected $startNumber;

    protected $user;

    /**
     * @return mixed
     */
    public function getDiscs()
    {
        return $this->discs;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getStartNumber()
    {
        return $this->startNumber;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}
