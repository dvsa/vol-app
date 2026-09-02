<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\System\LongText as Entity;

class LongText extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     *
     * @throws NotFoundException when no locale has content for the key
     */
    public function fetchByReferenceKey(string $referenceKey, string $locale): Entity
    {
        foreach (self::localeFallbacks($locale) as $candidate) {
            $result = $this->fetchOneByReferenceKeyAndLocale($referenceKey, $candidate);

            if ($result !== null) {
                return $result;
            }
        }

        throw new NotFoundException(sprintf('Long Text "%s" not found for locale %s', $referenceKey, $locale));
    }

    /**
     * @return list<string>
     */
    private static function localeFallbacks(string $locale): array
    {
        $candidates = [$locale, str_replace('_NI', '_GB', $locale), Entity::DEFAULT_LOCALE];

        return array_values(array_unique($candidates));
    }

    private function fetchOneByReferenceKeyAndLocale(string $referenceKey, string $locale): ?Entity
    {
        $qb = $this->getEntityManager()->getRepository($this->entity)->createQueryBuilder('m');

        $qb->andWhere($qb->expr()->eq('m.referenceKey', ':referenceKey'))
            ->andWhere($qb->expr()->eq('m.locale', ':locale'))
            ->setParameter('referenceKey', $referenceKey)
            ->setParameter('locale', $locale);

        return $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_OBJECT);
    }
}
