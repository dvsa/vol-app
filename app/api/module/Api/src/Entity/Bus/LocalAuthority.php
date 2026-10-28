<?php

namespace Dvsa\Olcs\Api\Entity\Bus;

use Doctrine\ORM\Mapping as ORM;

/**
 * LocalAuthority Entity
 */
#[ORM\Table(name: 'local_authority')]
#[ORM\Index(name: 'ix_local_authority_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_local_authority_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Index(name: 'ix_local_authority_traffic_area_id', columns: ['traffic_area_id'])]
#[ORM\Entity]
class LocalAuthority extends AbstractLocalAuthority
{
    public function update(string $description, ?string $emailAddress): LocalAuthority
    {
        $this->description = $description;
        $this->emailAddress = $emailAddress;
        return $this;
    }

    /**
     * Whether another authority has replaced this one (merged, renamed or split)
     */
    public function isSuperseded(): bool
    {
        return !$this->successors->isEmpty();
    }

    /**
     * The authorities that stand for this one now: itself if it hasn't been replaced, otherwise
     * its successors, followed to the end of the chain. A split authority has more than one.
     *
     * @return LocalAuthority[]
     */
    public function getCurrentAuthorities(): array
    {
        $current = [];
        $visited = [];
        $this->collectCurrentAuthorities($current, $visited);

        // Only a circular chain of successors leaves nothing; treat the authority as current
        return $current === [] ? [$this] : array_values($current);
    }

    /**
     * The authorities that stand now for those given, each once
     *
     * @param iterable<LocalAuthority> $authorities
     *
     * @return LocalAuthority[]
     */
    public static function currentAuthoritiesOf(iterable $authorities): array
    {
        $current = [];
        foreach ($authorities as $authority) {
            foreach ($authority->getCurrentAuthorities() as $currentAuthority) {
                $current[spl_object_id($currentAuthority)] = $currentAuthority;
            }
        }

        return array_values($current);
    }

    /**
     * This authority's id and the ids of every authority it replaced, however far back: the
     * records a user of this authority works with, since older registrations stay linked to the
     * authority they were made with
     *
     * @return int[]
     */
    public function getIdsIncludingPredecessors(): array
    {
        $ids = [];
        $this->collectIdsIncludingPredecessors($ids);

        return array_values($ids);
    }

    /**
     * Ids of the records this authority's users see: the authority that stands for it now and
     * every authority that one replaced. A user can still be attached to a council that has
     * been replaced; they work for the authority that replaced it.
     *
     * @return int[]
     */
    public function getAuthorityIdsVisibleToUsers(): array
    {
        $ids = [];
        foreach ($this->getCurrentAuthorities() as $currentAuthority) {
            $currentAuthority->collectIdsIncludingPredecessors($ids);
        }

        return array_values($ids);
    }

    /**
     * Users of this authority and of every authority it replaced, however far back
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User[]
     */
    public function getUsersIncludingPredecessors(): array
    {
        $users = [];
        $visited = [];
        $this->collectUsersIncludingPredecessors($users, $visited);

        return array_values($users);
    }

    /**
     * @param array<int, \Dvsa\Olcs\Api\Entity\User\User> $users
     * @param array<int, true> $visited
     */
    private function collectUsersIncludingPredecessors(array &$users, array &$visited): void
    {
        $key = spl_object_id($this);
        if (isset($visited[$key])) {
            return;
        }

        $visited[$key] = true;

        foreach ($this->users as $user) {
            $users[spl_object_id($user)] = $user;
        }

        /** @var LocalAuthority $predecessor */
        foreach ($this->predecessors as $predecessor) {
            $predecessor->collectUsersIncludingPredecessors($users, $visited);
        }
    }

    /**
     * @param array<int, int> $ids
     */
    private function collectIdsIncludingPredecessors(array &$ids): void
    {
        if (isset($ids[$this->id])) {
            return;
        }

        $ids[$this->id] = $this->id;

        /** @var LocalAuthority $predecessor */
        foreach ($this->predecessors as $predecessor) {
            $predecessor->collectIdsIncludingPredecessors($ids);
        }
    }

    /**
     * @param array<int, LocalAuthority> $current
     * @param array<int, true> $visited
     */
    private function collectCurrentAuthorities(array &$current, array &$visited): void
    {
        $key = spl_object_id($this);
        if (isset($visited[$key])) {
            return;
        }

        $visited[$key] = true;

        if (!$this->isSuperseded()) {
            $current[$key] = $this;
            return;
        }

        /** @var LocalAuthority $successor */
        foreach ($this->successors as $successor) {
            $successor->collectCurrentAuthorities($current, $visited);
        }
    }
}
