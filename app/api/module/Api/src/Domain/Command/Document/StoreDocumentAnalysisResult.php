<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

final class StoreDocumentAnalysisResult extends AbstractCommand
{
    protected $analysisToken;

    protected $executionArn;

    public function getAnalysisToken(): string
    {
        return (string) $this->analysisToken;
    }

    public function getExecutionArn(): string
    {
        return (string) $this->executionArn;
    }
}
