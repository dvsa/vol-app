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

        // Prevent deleting the last default variant. Duplicate defaults can exist -- the unique key
        // on the condition columns cannot catch them because MySQL treats NULLs as distinct -- and
        // blocking every default left those duplicates permanently unremovable through the UI.
        if ($variant->isDefault() && !$this->hasAnotherDefault($variant)) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\BadRequestException('Cannot delete the only default variant');
        }

        // Soft delete via SoftDeletableTrait
        $this->getRepo()->delete($variant);

        $this->result->addMessage('Variant deleted');

        return $this->result;
    }

    /**
     * Whether the variant's section still has another live default variant besides this one.
     *
     * @param VariantEntity $variant
     * @return bool
     */
    private function hasAnotherDefault(VariantEntity $variant): bool
    {
        $section = $variant->getLetterSection();

        if ($section === null) {
            return false;
        }

        foreach ($section->getVariants() as $sibling) {
            if ($sibling->getId() === $variant->getId() || $sibling->isDeleted()) {
                continue;
            }

            if ($sibling->isDefault()) {
                return true;
            }
        }

        return false;
    }
}
