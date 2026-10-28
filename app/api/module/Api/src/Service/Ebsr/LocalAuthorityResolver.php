<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Ebsr;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority;

/**
 * Works out which local authorities an EBSR registration goes to.
 *
 * Operators name authorities with whichever TransXChange name they know, old or new, so the
 * records those names match are replaced by the authorities that stand for them now. An old
 * name for an authority that has split is narrowed to the side(s) the route's stops are in.
 */
class LocalAuthorityResolver
{
    /**
     * @param iterable<LocalAuthority> $circulated records matched by the file's CirculatedAuthorities
     * @param iterable<LocalAuthority> $fromStops records matched by the ATCO codes of the file's stops
     *
     * @return ArrayCollection<int, LocalAuthority> current authorities, each once
     */
    public function resolveCirculated(iterable $circulated, iterable $fromStops): ArrayCollection
    {
        $resolved = new ArrayCollection();
        $stopAuthorities = null;

        foreach ($circulated as $authority) {
            $current = $authority->getCurrentAuthorities();

            if (count($current) > 1) {
                $stopAuthorities ??= LocalAuthority::currentAuthoritiesOf($fromStops);
                $withStops = array_filter(
                    $current,
                    static fn (LocalAuthority $successor) => in_array($successor, $stopAuthorities, true)
                );
                $current = $withStops === [] ? $current : array_values($withStops);
            }

            foreach ($current as $currentAuthority) {
                if (!$resolved->contains($currentAuthority)) {
                    $resolved->add($currentAuthority);
                }
            }
        }

        return $resolved;
    }

    /**
     * The local authorities whose traffic areas a registration takes. A combined authority has one
     * traffic area but can cover councils in several, so a council the file names gives its own,
     * and an authority the file names that is current gives those of the councils its stops are
     * in, or its own when none of the stops is.
     *
     * @param iterable<LocalAuthority> $circulated records matched by the file's CirculatedAuthorities
     * @param iterable<LocalAuthority> $fromStops records matched by the ATCO codes of the file's stops
     *
     * @return LocalAuthority[]
     */
    public function trafficAreaAuthorities(iterable $circulated, iterable $fromStops): array
    {
        $stopAuthorities = [];
        foreach ($fromStops as $stopAuthority) {
            $stopAuthorities[] = [$stopAuthority, $stopAuthority->getCurrentAuthorities()];
        }

        $authorities = [];
        foreach ($circulated as $authority) {
            if ($authority->isSuperseded()) {
                $authorities[spl_object_id($authority)] = $authority;
                continue;
            }

            $inStops = false;
            foreach ($stopAuthorities as [$stopAuthority, $current]) {
                if (in_array($authority, $current, true)) {
                    $authorities[spl_object_id($stopAuthority)] = $stopAuthority;
                    $inStops = true;
                }
            }

            if (!$inStops) {
                $authorities[spl_object_id($authority)] = $authority;
            }
        }

        return array_values($authorities);
    }

    /**
     * Tidies the authority names read from a file before they are looked up: TransXChange
     * names are tokens, but files do carry stray whitespace (" Leicester" in a DfT example)
     *
     * @param array<int, mixed> $names
     *
     * @return string[]
     */
    public static function normaliseNames(array $names): array
    {
        $names = array_map(static fn (mixed $name) => trim((string) $name), $names);

        return array_values(array_unique(array_filter($names, static fn (string $name) => $name !== '')));
    }
}
