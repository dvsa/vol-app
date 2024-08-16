<?php

namespace Dvsa\Olcs\Api\Domain\Command\PrintScheduler;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Enqueue a print job
 */
final class Enqueue extends AbstractCommand
{
    // kept for backward compatibility only
    protected $documentId;

    /** @var array */
    protected $documents = [];

    /** @var string */
    protected $type;

    protected $jobName;

    protected $user;

    protected $isDiscPrinting = false;

    /** @var int */
    protected $copies;

    /**
     * Get the document ID
     *
     * @return int
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * Get the list of document ids to be printed
     *
     * @return array
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Get queue type to enqueue as
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get job name
     *
     * @return string
     */
    public function getJobName()
    {
        return $this->jobName;
    }

    /**
     * Get user id
     *
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get is a disc printing job
     *
     * @return bool
     */
    public function getIsDiscPrinting()
    {
        return $this->isDiscPrinting;
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
