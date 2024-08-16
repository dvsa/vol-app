<?php

namespace Dvsa\Olcs\Api\Domain\Command\PrintScheduler;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Print Job
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class PrintJob extends AbstractCommand
{
    use Identity;

    /** @var array */
    protected $documents;

    protected $title;

    protected $user;

    /** @var  int */
    protected $copies;

    /**
     * Get the list of document ids to be printed
     *
     * @return array
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get count of copies
     *
     * @return int
     */
    public function getCopies()
    {
        return $this->copies;
    }
}
