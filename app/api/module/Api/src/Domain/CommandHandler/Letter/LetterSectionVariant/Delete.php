<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterSectionVariant;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant as VariantEntity;

/**
 * Soft-delete LetterSectionVariant
 *
 * Sets deletedDate via SoftDeletableTrait. The variant and its versions
 * remain in the database for audit purposes (letter instances may reference
 * versions that belonged to this variant).
 */
final class Delete extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterSectionVariant';

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var VariantEntity $variant */
        $variant = $this->getRepo()->fetchById($command->getId());

        // Prevent deleting the default variant
        if ($variant->isDefault()) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\BadRequestException('Cannot delete the default variant');
        }

        // Soft delete via SoftDeletableTrait
        $this->getRepo()->delete($variant);

        $this->result->addMessage('Variant deleted');

        return $this->result;
    }
}
