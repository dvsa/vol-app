<?php

namespace Dvsa\Olcs\Api\Domain\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * thresholdMinutes overrides the configured default.
 *
 * Properties are untyped: AbstractCommand::exchangeArray() populates via get_object_vars(),
 * which omits typed properties that have no default.
 */
final class SweepStaleDocumentAnalysis extends AbstractCommand
{
    protected $thresholdMinutes;

    public function getThresholdMinutes(): ?int
    {
        return $this->thresholdMinutes === null ? null : (int)$this->thresholdMinutes;
    }
}
