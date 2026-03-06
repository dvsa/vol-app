<?php

declare(strict_types=1);

namespace Olcs\Service\WebDav;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Dvsa\Olcs\Transfer\Command\Document\OverwriteContent;
use Dvsa\Olcs\Transfer\Query\Document\Download;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\IFile;

class VirtualFile implements IFile
{
    private ?int $cachedSize = null;
    private ?int $cachedLastModified = null;
    private ?string $cachedETag = null;

    public function __construct(
        private readonly string $name,
        private readonly int $documentId,
        private readonly AnnotationBuilder $annotationBuilder,
        private readonly CachingQueryService $queryService,
        private readonly CommandService $commandService,
        private readonly int $initialSize = 0,
    ) {
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[\Override]
    public function get()
    {
        $query = Download::create(['identifier' => $this->documentId]);
        $query = $this->annotationBuilder->createQuery($query);

        try {
            $response = $this->queryService->send($query);
        } catch (\Throwable $e) {
            throw new NotFound('Document download failed: ' . $e->getMessage());
        }

        if (!$response->isOk()) {
            throw new NotFound('Document not found (status: ' . $response->getStatusCode() . ')');
        }

        $body = $response->getHttpResponse()->getBody();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, (string) $body);
        rewind($stream);
        return $stream;
    }

    #[\Override]
    public function put($data): ?string
    {
        $content = is_resource($data) ? stream_get_contents($data) : $data;

        $command = OverwriteContent::create([
            'id' => $this->documentId,
            'content' => $content,
        ]);
        $command = $this->annotationBuilder->createCommand($command);

        try {
            $response = $this->commandService->send($command);
        } catch (\Throwable $e) {
            throw new Forbidden('Document upload failed: ' . $e->getMessage());
        }

        if (!$response->isOk()) {
            throw new Forbidden('Failed to update document (status: ' . $response->getStatusCode() . ')');
        }

        $this->cachedSize = strlen($content);
        $this->cachedLastModified = time();
        $this->cachedETag = '"doc-' . $this->documentId . '-' . time() . '"';

        return $this->cachedETag;
    }

    #[\Override]
    public function getSize(): ?int
    {
        return $this->cachedSize ?? $this->initialSize;
    }

    #[\Override]
    public function getContentType(): ?string
    {
        $ext = strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
        return match ($ext) {
            'rtf' => 'application/rtf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };
    }

    #[\Override]
    public function getETag(): ?string
    {
        return $this->cachedETag ?? '"doc-' . $this->documentId . '"';
    }

    #[\Override]
    public function getLastModified(): ?int
    {
        return $this->cachedLastModified ?? time();
    }

    #[\Override]
    public function delete(): void
    {
        throw new Forbidden('Deleting documents via WebDAV is not permitted');
    }

    #[\Override]
    public function setName($name): void
    {
        throw new Forbidden('Renaming documents via WebDAV is not permitted');
    }
}
