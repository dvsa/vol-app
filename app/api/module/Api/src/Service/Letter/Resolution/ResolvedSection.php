<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\Resolution;

use Dvsa\Olcs\Api\Entity\Letter\LetterSection;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion;

/**
 * A section that resolved to a concrete piece of wording for the given context.
 */
final readonly class ResolvedSection
{
    public function __construct(
        public LetterSection $section,
        public LetterSectionVariant $variant,
        public LetterSectionVersion $version,
        public int $displayOrder,
        public bool $isRequired
    ) {
    }
}
