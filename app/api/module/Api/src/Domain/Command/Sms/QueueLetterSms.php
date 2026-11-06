<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Command\Sms;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Queue an SMS telling the operator that a letter has been sent
 */
final class QueueLetterSms extends AbstractCommand
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
