<?php

/**
 * Publish a Pi Decision
 */

namespace Dvsa\Olcs\Api\Domain\Command\Publication;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;

/**
 * Publish a Pi Decision
 */
final class PiDecision extends AbstractIdOnlyCommand
{
    protected $trafficAreas;
    protected $pubType;
    protected $text2;

    /**
     * @return array
     */
    public function getTrafficAreas()
    {
        return $this->trafficAreas;
    }

    /**
     * @return array
     */
    public function getPubType()
    {
        return $this->pubType;
    }

    /**
     * @return string
     */
    public function getText2()
    {
        return $this->text2;
    }
}
