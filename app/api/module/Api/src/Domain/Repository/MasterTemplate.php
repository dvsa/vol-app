<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\MasterTemplate as Entity;

/**
 * MasterTemplate Repository
 */
class MasterTemplate extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Find a MasterTemplate by its (name, locale) pair — used by MasterTemplateResolver
     * to pivot between chrome variants (e.g. en_GB ↔ en_NI sibling rows sharing the same
     * family name) at letter-generation time (VOL-7305).
     *
     * @param string $name
     * @param string $locale
     * @return Entity|null
     */
    public function findByNameAndLocale(string $name, string $locale): ?Entity
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->eq($this->alias . '.name', ':name'))
            ->andWhere($qb->expr()->eq($this->alias . '.locale', ':locale'))
            ->setParameter('name', $name)
            ->setParameter('locale', $locale)
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult();
        return $result[0] ?? null;
    }

    /**
     * Fetch the default master template — the chrome used when a LetterType has no
     * template of its own. Prefers the en_GB row; tolerates a NULL locale so
     * pre-VOL-7305 default rows keep working until re-saved.
     *
     * @return Entity|null
     */
    public function fetchDefault(): ?Entity
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->eq($this->alias . '.isDefault', ':isDefault'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq($this->alias . '.locale', ':locale'),
                $qb->expr()->isNull($this->alias . '.locale')
            ))
            ->setParameter('isDefault', true)
            ->setParameter('locale', Entity::LOCALE_EN_GB)
            ->orderBy($this->alias . '.locale', 'DESC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult();
        return $result[0] ?? null;
    }
}
