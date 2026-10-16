<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Command\Document;

use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Ids only - the handler resolves the entities itself, so the command stays a plain
 * serialisable DTO.
 */
interface AnalyseDocumentCommandInterface extends CommandInterface
{
    /** Id of the application whose documents should be analysed. */
    public function getApplication(): ?int;

    /** A specific document to analyse; when null the handler resolves them itself. */
    public function getDocument(): ?int;
}
