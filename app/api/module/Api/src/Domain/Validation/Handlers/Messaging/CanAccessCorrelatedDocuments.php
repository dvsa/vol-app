<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareInterface;
use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Transfer\Query\Messaging\Documents;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;

class CanAccessCorrelatedDocuments extends AbstractHandler implements CacheAwareInterface, RepositoryManagerAwareInterface, AuthAwareInterface
{
    use CacheAwareTrait;
    use RepositoryManagerAwareTrait;
    use AuthAwareTrait;

    /** @param Documents $dto */
    public function isValid($dto)
    {
        $documentIds = $this->getCache()->getCustomItem(
            CacheEncryption::GENERIC_STORAGE_IDENTIFIER,
            $dto->getCorrelationId(),
        ) ?: [];

        $repo = $this->getRepo(Repository\Document::class);

        foreach ($documentIds as $documentId) {
            /** @var Document $doc */
            try {
                $doc = $repo->fetchById($documentId);
            } catch (NotFoundException) {
                continue;
            }
            if ($doc && $doc->getCreatedBy()->getId() !== $this->getUser()->getId()) {
                return false;
            }
        }

        return true;
    }
}
