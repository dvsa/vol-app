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
}
