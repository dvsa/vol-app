<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\System\TranslationKeyText as Entity;

/**
 * Translation Key Text
 */
class TranslationKeyText extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch by translation key and language
     *
     * @param $translationKeyId
     * @param $languageId
     * @return null|Entity
     */
    public function fetchByParentLanguage($translationKeyId, $languageId)
    {
        $qb = $this->createQueryBuilder();

        $qb->where($qb->expr()->eq($this->alias . '.translationKey', ':translationKey'))
            ->andWhere($qb->expr()->eq($this->alias . '.language', ':language'))
            ->setParameter('translationKey', $translationKeyId)
            ->setParameter('language', $languageId);

        return $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_OBJECT);
    }

    /**
     * Fetch all translation keys, with option to filter by locale
     *
     *
     * @return mixed
     */
    public function fetchAll(?string $locale, int $hydrationMode = Query::HYDRATE_OBJECT)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->with('translationKey', 't')
            ->with('language', 'l');

        if ($locale !== null) {
            $qb->andWhere($qb->expr()->eq('l.isoCode', ':locale'))->setParameter('locale', $locale);
        }

        return $qb->getQuery()->getResult($hydrationMode);
    }
}
