<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Messaging;

use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Conversation as ConversationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Document;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\Messaging\Documents as DocumentsQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;

class Documents extends AbstractQueryHandler implements ToggleRequiredInterface, CacheAwareInterface
{
    use CacheAwareTrait;
    use ToggleAwareTrait;
    use AuthAwareTrait;

    protected $toggleConfig = [FeatureToggle::MESSAGING];
    protected $extraRepos = [ConversationRepo::class, Document::class];

    /** @param DocumentsQuery|QueryInterface $query */
    public function handleQuery(QueryInterface $query): array
    {
        $documentIds = $this->getCache()->getCustomItem(CacheEncryption::GENERIC_STORAGE_IDENTIFIER, $query->getCorrelationId()) ?: [];
        $documentsRepo = $this->getRepo(Document::class);
        $documents = $documentsRepo->fetchUnassignedListForUser($this->getUser()->getId());
        $documents = array_values(array_filter($documents, fn($document) => in_array($document->getId(), $documentIds)));

        return $this->resultList($documents);
    }
}
