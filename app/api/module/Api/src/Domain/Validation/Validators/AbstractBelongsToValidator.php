<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareInterface;
use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareTrait;

/**
 * Abstract Belongs To Validator
 *
 * Confirms that a child entity is attached to a given parent record (e.g. a fee
 * to its licence/application/bus reg, or a serious infringement to its case).
 *
 * A concrete validator declares the repository used to fetch the child by id
 * ($repo) and implements getRelatedEntity() with an explicit getter call on the
 * child. Keeping that call explicit (rather than a configured method name) means
 * it stays visible to "find usages", rename refactoring and static analysis.
 *
 * Unlike the CanAccess* validators this deliberately does not grant
 * internal/system users a free pass: it is a relationship-integrity check used
 * to catch a child id being processed against a mismatched parent id from the
 * URL (e.g. /case/{caseId}/serious-infringement/{siId} where the serious
 * infringement actually belongs to a different case).
 */
abstract class AbstractBelongsToValidator extends AbstractValidator implements RepositoryManagerAwareInterface
{
    use RepositoryManagerAwareTrait;

    /**
     * Repository used to fetch the child entity when an id (rather than an
     * entity) is supplied.
     *
     * @var string
     */
    protected $repo;

    /**
     * Confirm the child entity (or its id) is attached to the given parent
     * (entity or id).
     */
    public function isValid(object|int|string $entity, object|int|string|null $parent): bool
    {
        $entity = $this->resolveEntity($entity);
        $related = $this->getRelatedEntity($entity);

        if ($related === null || $parent === null) {
            return false;
        }

        return $this->resolveId($related) === $this->resolveId($parent);
    }

    /**
     * Get the parent entity the child should be attached to, e.g.
     * `return $fee->getLicence();`.
     */
    abstract protected function getRelatedEntity(object $entity): ?object;

    protected function resolveEntity(object|int|string $entity): object
    {
        if (is_object($entity)) {
            return $entity;
        }

        return $this->getRepo($this->repo)->fetchById($entity);
    }

    /**
     * Normalise an entity or scalar identifier to an integer id.
     */
    private function resolveId(object|int|string|null $value): ?int
    {
        if (is_object($value)) {
            $value = $value->getId();
        }

        return $value === null ? null : (int) $value;
    }
}
