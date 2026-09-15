<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\Resolution;

use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant;

/**
 * Why a section resolved to the variant it did.
 *
 * getVariantForContext() answers only "which one won", which is all letter generation needs. An
 * admin looking at a preview needs to know why the others lost, so this carries the reasoning
 * alongside the answer.
 */
final readonly class VariantResolution
{
    /**
     * @param LetterSectionVariant|null $chosen             The variant that will supply the wording
     * @param bool                      $wasDefaultFallback True when a conditioned variant was wanted but none matched
     * @param int                       $conditionedCount   Live conditioned variants on this section
     * @param LetterSectionVariant[]    $liveDefaults       Non-deleted default variants; more than one is a config fault
     * @param LetterSectionVariant[]    $deleted            Soft-deleted variants, excluded from matching
     * @param array<int, array{variant: LetterSectionVariant, failed: string[]}> $rejections
     *        Each rejected variant with the dimensions that blocked it, keyed by spl_object_id
     */
    public function __construct(
        public ?LetterSectionVariant $chosen,
        public bool $wasDefaultFallback,
        public int $conditionedCount,
        public array $liveDefaults,
        public array $deleted,
        public array $rejections
    ) {
    }

    /**
     * The section is configured with specific wording that the current context cannot reach.
     *
     * This is the condition behind letters quietly carrying generic wording: the conditioned
     * variants an admin wrote are all unreachable, so the catch-all is sent instead.
     */
    public function fellBackDespiteConditionedVariants(): bool
    {
        return $this->wasDefaultFallback && $this->conditionedCount > 0;
    }

    /**
     * Duplicate defaults are possible because MySQL unique keys treat NULLs as distinct, so the
     * condition columns cannot catch them. Only the first is ever used.
     */
    public function hasDuplicateDefaults(): bool
    {
        return count($this->liveDefaults) > 1;
    }

    /**
     * The rejected variant nearest to matching: fewest failing dimensions, first wins a tie.
     *
     * This is what turns "none match" into an instruction -- its pinned dimensions ARE the
     * context an admin needs to set to see it.
     *
     * @return array{variant: LetterSectionVariant, failed: string[]}|null
     */
    public function closestRejection(): ?array
    {
        $closest = null;

        foreach ($this->rejections as $rejection) {
            if ($closest === null || count($rejection['failed']) < count($closest['failed'])) {
                $closest = $rejection;
            }
        }

        return $closest;
    }

    /**
     * Nothing to send: every variant is either deleted or unmatched.
     */
    public function hasNoUsableVariant(): bool
    {
        return $this->chosen === null;
    }
}
