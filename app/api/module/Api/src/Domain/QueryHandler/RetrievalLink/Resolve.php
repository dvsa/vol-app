<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\RetrievalLink;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Retrieval\RetrievalLinkAccessTrait;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink as RetrievalLinkEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Resolves a link token to a redacted summary for the landing page: the gate mode, the document
 * list (by opaque member ref + display name), and the expiry. Never exposes recipient email or
 * real document ids. Unknown/expired/revoked all raise the same NotFoundException.
 */
final class Resolve extends AbstractQueryHandler implements UploaderAwareInterface
{
    use RetrievalLinkAccessTrait;
    use UploaderAwareTrait;

    protected $repoServiceName = 'RetrievalLink';

    protected $extraRepos = ['RetrievalLinkEvent'];

    /**
     * @param QueryInterface $query
     * @return array<string, mixed>
     * @throws NotFoundException
     */
    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        /** @var RetrievalLinkEntity|null $link */
        $link = $this->getRepo()->fetchByToken((string) $query->getToken());

        if (!$this->isLinkUsable($link, new \DateTimeImmutable())) {
            throw new NotFoundException('This link is no longer available');
        }

        $this->recordRetrievalEvent($link, 'viewed');

        $documents = [];
        foreach ($link->getDocuments() as $member) {
            $documents[] = [
                'memberRef' => $member->getMemberRef(),
                'displayFilename' => $member->getDisplayFilename(),
            ];
        }

        return [
            'gateMode' => $link->getGateMode(),
            'documentCount' => count($documents),
            'documents' => $documents,
            'expiresAt' => $link->getExpiresAt(),
            // How selfserve should fetch the bytes: 'presigned' (S3 — pull straight from S3) or
            // 'stream' (WebDAV — proxy the API stream).
            'downloadStrategy' => $this->getUploader()->supportsPresignedUrls() ? 'presigned' : 'stream',
        ];
    }
}
