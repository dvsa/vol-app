<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\Resolution;

use Dvsa\Olcs\Api\Entity\Letter\LetterSection;
use Dvsa\Olcs\Api\Entity\Letter\LetterType;
use Dvsa\Olcs\Api\Entity\Letter\LetterTypeSection;

/**
 * One section put forward for inclusion in a letter, with the two facts the resolver
 * needs about it beyond the section itself: where it sits and whether its absence is
 * an error. Deliberately NOT a LetterTypeSection, so a composition that has not been
 * saved (the Letter Type Builder) can be resolved without minting Doctrine entities.
 */
final readonly class SectionCandidate
{
    public function __construct(
        public LetterSection $section,
        public int $displayOrder,
        public bool $isRequired
    ) {
    }

    public static function fromLetterTypeSection(LetterTypeSection $typeSection): self
    {
        return new self(
            $typeSection->getLetterSection(),
            (int) $typeSection->getDisplayOrder(),
            (bool) $typeSection->getIsRequired()
        );
    }

    /**
     * @return list<self>
     */
    public static function listFromLetterType(LetterType $letterType): array
    {
        $candidates = [];
        foreach ($letterType->getLetterTypeSections() ?? [] as $typeSection) {
            $candidates[] = self::fromLetterTypeSection($typeSection);
        }
        return $candidates;
    }

    /**
     * Build candidates from a proposed, unsaved ordering. Display order is taken from
     * array position — identical to how LetterType\Update assigns it on save — so what
     * the builder previews is what saving would produce.
     *
     * @param list<LetterSection> $sectionsInProposedOrder
     * @param list<int> $requiredSectionIds
     * @return list<self>
     */
    public static function listFromProposedOrder(array $sectionsInProposedOrder, array $requiredSectionIds = []): array
    {
        $candidates = [];
        $displayOrder = 0;
        foreach ($sectionsInProposedOrder as $section) {
            $candidates[] = new self(
                $section,
                $displayOrder++,
                in_array($section->getId(), $requiredSectionIds)
            );
        }
        return $candidates;
    }
}
