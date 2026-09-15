<?php

namespace Dvsa\Olcs\Transfer\Command\Bus\Ebsr;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * QueuePacks
 *
 * @Transfer\RouteName("backend/bus/queue-ebsr-packs")
 * @Transfer\Method("POST")
 */
class QueuePacks extends AbstractCommand
{
    /**
     * @var string
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"ebsrt_new","ebsrt_refresh"}})
     */
    protected $submissionType;

    /**
     * @return string
     */
    public function getSubmissionType()
    {
        return $this->submissionType;
    }
}
