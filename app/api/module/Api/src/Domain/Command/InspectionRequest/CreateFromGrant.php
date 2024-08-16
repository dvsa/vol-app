<?php

/**
 * Inspection Request / Create From Grant
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\InspectionRequest;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Inspection Request / Create From Grant
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateFromGrant extends AbstractCommand
{
    public $application;

    public $duePeriod;

    protected $caseworkerNotes;

    public function getApplication()
    {
        return $this->application;
    }

    public function getDuePeriod()
    {
        return $this->duePeriod;
    }

    public function getCaseworkerNotes()
    {
        return $this->caseworkerNotes;
    }
}
