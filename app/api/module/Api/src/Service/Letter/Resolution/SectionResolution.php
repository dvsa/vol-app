<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\Resolution;

/**
 * Outcome of resolving a proposed section composition against a variant context.
 */
final readonly class SectionResolution
{
    /**
     * @param list<ResolvedSection> $resolved   in composition order
     * @param list<UnresolvedSection> $unresolved in composition order
     */
    public function __construct(
        public array $resolved,
        public array $unresolved
    ) {
    }

    /**
     * @return list<UnresolvedSection>
     */
    public function getUnresolvedRequired(): array
    {
        return array_values(array_filter($this->unresolved, static fn(UnresolvedSection $u): bool => $u->isRequired));
    }

    /**
     * @return list<UnresolvedSection>
     */
    public function getUnresolvedOptional(): array
    {
        return array_values(array_filter($this->unresolved, static fn(UnresolvedSection $u): bool => !$u->isRequired));
    }

    public function hasUnresolved(): bool
    {
        return $this->unresolved !== [];
    }
}
