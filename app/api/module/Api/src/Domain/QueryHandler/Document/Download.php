<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\Http\Response;

class Download extends AbstractDownload
{
    /**
     * Process download
     *
     * @param \Dvsa\Olcs\Transfer\Query\Document\Download $query Download File Query
     *
     * @return Response\Stream
     * @throws NotFoundException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        $this->setIsInline($query->isInline());

        /* @var \Dvsa\Olcs\Api\Entity\Doc\Document $document */
        $document = $this->getRepo()->fetchById($query->getIdentifier());

        $chosenFileName = null;

        if ($this->isInternalUser()) {
            $description = $document->getDescription();

            if (!empty($description)) {
                $chosenFileName = $description;
            }
        }

        return $this->download(
            $document->getIdentifier(),
            null,
            $chosenFileName
        );
    }
}
