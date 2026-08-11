<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter;

use Dvsa\Olcs\Api\Service\Letter\Resolution\SectionResolution;

/**
 * Explains what a proposed letter type composition would produce.
 *
 * Letter types are configured blind: nothing tells an admin what their composition does until
 * somebody generates a real letter and reads it. Every warning here is drawn from information the
 * resolver already has, so this only surfaces what resolution already knew and discarded.
 *
 * Pure: no repositories, no persistence, no rendering.
 */
class CompositionDiagnostics
{
    public const SEVERITY_BLOCKING = 'blocking';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_INFO = 'info';

    /**
     * Placeholders written in a syntax the letters engine has never supported. The engine only
     * recognises [[UPPER_SNAKE]]; anything in braces renders to the operator verbatim.
     */
    private const UNSUPPORTED_PLACEHOLDER = '/\{[A-Za-z][A-Za-z0-9 _-]{2,40}\}/';

    /**
     * @param SectionResolution $resolution
     * @param string            $renderedHtml Rendered letter, searched for placeholders that survived
     * @return array<int, array{code:string, severity:string, section:?string, message:string, detail:array}>
     */
    public function forResolution(SectionResolution $resolution, string $renderedHtml = ''): array
    {
        return [
            ...$this->unresolvedSections($resolution),
            ...$this->variantWarnings($resolution),
            ...$this->unsupportedPlaceholders($renderedHtml),
        ];
    }

    /**
     * A section that resolved to nothing is simply absent from the letter. Whether that is fatal or
     * merely notable is the letter type's call, which is what is_required records.
     */
    private function unresolvedSections(SectionResolution $resolution): array
    {
        $diagnostics = [];

        foreach ($resolution->unresolved as $unresolved) {
            $diagnostics[] = [
                'code' => 'sectionUnresolved',
                'severity' => $unresolved->isRequired ? self::SEVERITY_BLOCKING : self::SEVERITY_WARNING,
                'section' => $unresolved->getSectionName(),
                'message' => sprintf(
                    '%s section "%s" is not in this letter: %s.',
                    $unresolved->isRequired ? 'Required' : 'Optional',
                    $unresolved->getSectionName(),
                    $unresolved->reason === $unresolved::REASON_NO_CURRENT_VERSION
                        ? 'a variant matched but has no published version'
                        : 'no variant matches this context'
                ),
                'detail' => ['reason' => $unresolved->reason],
            ];
        }

        return $diagnostics;
    }

    private function variantWarnings(SectionResolution $resolution): array
    {
        $diagnostics = [];

        foreach ($resolution->resolved as $resolved) {
            $variantResolution = $resolved->variantResolution;
            if ($variantResolution === null) {
                continue;
            }

            $sectionName = $resolved->section->getName() ?? $resolved->section->getSectionKey();

            // The section carries wording written for specific circumstances, and this context
            // reaches none of it, so the catch-all goes out instead. This is how a letter quietly
            // carries generic wording nobody intended to send.
            if ($variantResolution->fellBackDespiteConditionedVariants()) {
                $diagnostics[] = [
                    'code' => 'defaultFallback',
                    'severity' => self::SEVERITY_WARNING,
                    'section' => $sectionName,
                    'message' => sprintf(
                        '"%s" is using its default wording. %d specific %s configured, but none match this context.',
                        $sectionName,
                        $variantResolution->conditionedCount,
                        $variantResolution->conditionedCount === 1 ? 'variant is' : 'variants are'
                    ),
                    'detail' => [
                        'conditionedCount' => $variantResolution->conditionedCount,
                        'rejectedOn' => $this->summariseRejections($variantResolution->rejections),
                    ],
                ];
            }

            // MySQL unique keys treat NULLs as distinct, so the condition columns cannot stop a
            // second all-null variant being created. Only the first is ever used.
            if ($variantResolution->hasDuplicateDefaults()) {
                $diagnostics[] = [
                    'code' => 'duplicateDefaults',
                    'severity' => self::SEVERITY_WARNING,
                    'section' => $sectionName,
                    'message' => sprintf(
                        '"%s" has %d default variants. Only the first is ever used.',
                        $sectionName,
                        count($variantResolution->liveDefaults)
                    ),
                    'detail' => ['count' => count($variantResolution->liveDefaults)],
                ];
            }

            if ($variantResolution->deleted !== []) {
                $diagnostics[] = [
                    'code' => 'deletedVariants',
                    'severity' => self::SEVERITY_INFO,
                    'section' => $sectionName,
                    'message' => sprintf(
                        '"%s" has %d deleted variant(s), which are excluded from letters.',
                        $sectionName,
                        count($variantResolution->deleted)
                    ),
                    'detail' => ['count' => count($variantResolution->deleted)],
                ];
            }
        }

        return $diagnostics;
    }

    /**
     * Placeholders that reached the rendered letter. A resolvable bookmark has already been
     * substituted by this point, so anything still in braces will print to the operator as-is.
     */
    private function unsupportedPlaceholders(string $renderedHtml): array
    {
        if ($renderedHtml === '' || !preg_match_all(self::UNSUPPORTED_PLACEHOLDER, $renderedHtml, $matches)) {
            return [];
        }

        $tokens = array_values(array_unique($matches[0]));

        return [[
            'code' => 'unsupportedPlaceholder',
            'severity' => self::SEVERITY_BLOCKING,
            'section' => null,
            'message' => sprintf(
                'This letter contains %d placeholder(s) the engine cannot resolve, which will print to the operator as written: %s.',
                count($tokens),
                implode(', ', $tokens)
            ),
            'detail' => ['tokens' => $tokens],
        ]];
    }

    /**
     * @param array<int, string[]> $rejections
     * @return string[] Dimension names that blocked variants, most obstructive first
     */
    private function summariseRejections(array $rejections): array
    {
        $counts = [];

        foreach ($rejections as $failedDimensions) {
            foreach ($failedDimensions as $dimension) {
                $counts[$dimension] = ($counts[$dimension] ?? 0) + 1;
            }
        }

        arsort($counts);

        return array_keys($counts);
    }
}
