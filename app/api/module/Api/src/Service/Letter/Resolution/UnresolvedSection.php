<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\Resolution;

use Dvsa\Olcs\Api\Entity\Letter\LetterSection;

/**
 * A section that could not be given wording for the given context. Reported either way —
 * a section silently vanishing from a letter is indistinguishable, to the caseworker,
 * from one that was never configured.
 */
final readonly class UnresolvedSection
{
    public const string REASON_NO_MATCHING_VARIANT = 'noMatchingVariant';
    public const string REASON_NO_CURRENT_VERSION = 'noCurrentVersion';

    public function __construct(
        public LetterSection $section,
        public int $displayOrder,
        public bool $isRequired,
        public string $reason,
        public ?VariantResolution $variantResolution = null
    ) {
    }

    public function getSectionName(): string
    {
        return (string) ($this->section->getName() ?? $this->section->getSectionKey());
    }
}
