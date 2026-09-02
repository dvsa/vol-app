<?php

namespace Dvsa\Olcs\Api\Domain\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Internal only: raised as a side effect of application submission and never routed from the
 * frontends, so it lives here rather than in olcs-transfer.
 *
 * Properties are deliberately untyped - AbstractCommand::exchangeArray() populates via
 * get_object_vars(), which omits typed properties that have no default.
 */
final class AnalyseFinancialEvidence extends AbstractCommand implements AnalyseDocumentCommandInterface
{
    protected $application;

    protected $document;

    #[\Override]
    public function getApplication(): ?int
    {
        return $this->application === null ? null : (int)$this->application;
    }

    #[\Override]
    public function getDocument(): ?int
    {
        return $this->document === null ? null : (int)$this->document;
    }
}
