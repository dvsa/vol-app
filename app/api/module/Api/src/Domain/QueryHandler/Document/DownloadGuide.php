<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\Http\Response\Stream;

/**
 * Download a guide document, these are located in a "/guides/" directory in the content store
 */
class DownloadGuide extends AbstractDownload
{
    protected $extraRepos = ['DocTemplate'];

    /**
     * @param \Dvsa\Olcs\Transfer\Query\Document\DownloadGuide $query DTO
     *
     * @throws NotFoundException
     */
    public function handleQuery(QueryInterface $query): Stream
    {
        $this->setIsInline($query->isInline());

        $identifier = $query->getIdentifier();

        if ($query->getIsSlug()) {
            $identifier = $this->getIdentifierFromSlug($identifier);
        }

        // make sure that the file identifier cannot go up directory structure to secure documents
        if (str_contains($identifier, '..')) {
            throw new NotFoundException();
        }

        return $this->download($identifier, '/guides/' . $identifier);
    }

    /**
     * Lookup document store identifier for doc template slug
     *
     * @return string
     * @throws NotFoundException
     */
    protected function getIdentifierFromSlug(string $slug)
    {
        $docTemplate = $this->getRepo('DocTemplate')->fetchByTemplateSlug($slug);
        return basename((string) $docTemplate->getDocument()->getIdentifier());
    }
}
