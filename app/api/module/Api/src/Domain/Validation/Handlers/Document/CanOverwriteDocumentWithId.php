<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Document;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Overwrite a Document entity with an ID
 */
class CanOverwriteDocumentWithId extends AbstractHandler
{
    /**
     * Validate DTO
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommandInterface|\Dvsa\Olcs\Transfer\Query\QueryInterface $dto
     *
     * @return bool
     */
    #[\Override]
    public function isValid($dto)
    {
        return $this->canAccessDocument($this->getId($dto)) && $this->isDocumentCreator($this->getId($dto));
    }

    /**
     * Get the document ID
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommandInterface|\Dvsa\Olcs\Transfer\Query\QueryInterface $dto
     *
     * @return int
     */
    protected function getId($dto)
    {
        if (method_exists($dto, 'getIdentifier')) {
            return $dto->getIdentifier();
        }

        return $dto->getId();
    }
}
