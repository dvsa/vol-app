<?php

namespace Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Create a blank EBSR submission (file has been uploaded but user hasn't clicked confirm)
 */
final class CreateSubmission extends AbstractCommand
{
    protected $document;

    /**
     * @return int
     */
    public function getDocument()
    {
        return $this->document;
    }
}
