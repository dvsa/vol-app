<?php

namespace Dvsa\Olcs\Api\Domain\Command\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;

/**
 * GenerateChecklistDocument
 */
final class GenerateChecklistDocument extends AbstractIdOnlyCommand
{
    protected $user;

    protected $enforcePrint = false;

    /**
     * Get user
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get should printing the document be enforced
     *
     * @return bool
     */
    public function getEnforcePrint()
    {
        return (bool)$this->enforcePrint;
    }
}
