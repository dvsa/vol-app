<?php

namespace Dvsa\Olcs\Api\Domain\Command\System;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Generate SlaTargetDate
 */
final class GenerateSlaTargetDate extends AbstractCommand
{
    /** @var int|null */
    protected $pi;

    /** @var int|null */
    protected $submission;

    /** @var int|null */
    protected $proposeToRevoke;

    /** @var int|null */
    protected $statement;

    /**
     * Get PI id
     *
     * @return int|null
     */
    public function getPi()
    {
        return $this->pi;
    }

    /**
     * Get Submission id
     *
     * @return int|null
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * Get the value of ProposeToRevoke id
     *
     * @return int|null
     */
    public function getProposeToRevoke()
    {
        return $this->proposeToRevoke;
    }

    /**
     * Get the value of Statement id
     *
     * @return int|null
     */
    public function getStatement()
    {
        return $this->statement;
    }
}
