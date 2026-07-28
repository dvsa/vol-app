<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterType;
use Dvsa\Olcs\Api\Service\Letter\Resolution\ResolvedSection;
use Dvsa\Olcs\Api\Service\Letter\Resolution\SectionCandidate;
use Dvsa\Olcs\Api\Service\Letter\Resolution\SectionResolution;
use Dvsa\Olcs\Api\Service\Letter\Resolution\UnresolvedSection;

/**
 * Turns a proposed section composition into the concrete wording a letter would carry
 * for a given variant context.
 *
 * Extracted verbatim from LetterInstance\Generate so that anything which needs to know
 * what a letter WOULD contain (the Letter Type Builder preview) walks the same code as
 * the thing that decides what a letter DOES contain. A second implementation would drift.
 *
 * Pure: no repositories, no persistence, no entity mutation.
 */
class SectionVariantResolver
{
    /**
     * @param array $context Keys: goodsOrPsv, isVariation, isNi, organisationType, selectedChoiceIds
     */
    public function resolveForLetterType(LetterType $letterType, array $context): SectionResolution
    {
        return $this->resolve(SectionCandidate::listFromLetterType($letterType), $context);
    }

    /**
     * @param iterable<SectionCandidate> $candidates in composition order
     * @param array $context Keys: goodsOrPsv, isVariation, isNi, organisationType, selectedChoiceIds
     */
    public function resolve(iterable $candidates, array $context): SectionResolution
    {
        $resolved = [];
        $unresolved = [];

        foreach ($candidates as $candidate) {
            $section = $candidate->section;
            $variant = $section->getVariantForContext($context);

            if ($variant === null) {
                $unresolved[] = new UnresolvedSection(
                    $section,
                    $candidate->displayOrder,
                    $candidate->isRequired,
                    UnresolvedSection::REASON_NO_MATCHING_VARIANT
                );
                continue;
            }

            $version = $variant->getCurrentVersion();
            if ($version === null) {
                $unresolved[] = new UnresolvedSection(
                    $section,
                    $candidate->displayOrder,
                    $candidate->isRequired,
                    UnresolvedSection::REASON_NO_CURRENT_VERSION
                );
                continue;
            }

            $resolved[] = new ResolvedSection(
                $section,
                $variant,
                $version,
                $candidate->displayOrder,
                $candidate->isRequired
            );
        }

        return new SectionResolution($resolved, $unresolved);
    }
}
