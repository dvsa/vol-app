<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\QueryHandler\Document\AbstractDownload;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\Http\Response\Stream;

/**
 * Stub class of AbstractDownload handler for testing
 */
class AbstractDownloadStub extends AbstractDownload
{
    #[\Override]
    public function download(string $identifier, ?string $path = null, ?string $fileName = null): Stream
    {
        return parent::download($identifier, $path, $fileName);
    }

    #[\Override]
    public function setIsInline($inline)
    {
        return parent::setIsInline($inline);
    }

    public function handleQuery(QueryInterface $query): void
    {
        // suppress codesniffer warning
        unset($query);
    }
}
