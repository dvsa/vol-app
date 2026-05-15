<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Template\Template as Entity;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Doctrine\ORM\NoResultException;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Template
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class Template extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Attach filters to query
     *
     * @param QueryBuilder                               $qb    Query Builder
     * @param \Dvsa\Olcs\Transfer\Query\Template\AvailableTemplates $query Http query
     *
     * @return void
     */
    #[\Override]
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if (method_exists($query, 'getEmailTemplateCategory') && is_numeric($query->getEmailTemplateCategory())) {
            // Category ID can be a real category int, or 0 to indicate the dummy Header/Footer category for email templates.
            $categoryId = $query->getEmailTemplateCategory();
            if ((int) $categoryId === 0) {
                $qb->andWhere($qb->expr()->isNull('m.category'));
            } else {
                $qb->andWhere($qb->expr()->eq('m.category', ':categoryId'))
                    ->setParameter('categoryId', $categoryId);
            }
        }

        // VOL-7238: optional format filter (html / plain / md) from the admin list dropdown.
        // method_exists guard so older transfer DTOs without the property don't error.
        if (method_exists($query, 'getFormat') && $query->getFormat() !== '') {
            $qb->andWhere($qb->expr()->eq('m.format', ':format'))
                ->setParameter('format', $query->getFormat());
        }
    }

    /**
     * Fetch by locale, format and name
     *
     * @param string $locale
     * @param string $format
     * @param string $name
     *
     * @return Entity
     */
    public function fetchByLocaleFormatName($locale, $format, $name)
    {
        try {
            return $this->getEntityManager()
                ->createQueryBuilder()
                ->select('t')
                ->from(Entity::class, 't')
                ->where('t.locale = ?1')
                ->andWhere('t.format = ?2')
                ->andWhere('t.name = ?3')
                ->setParameter(1, $locale)
                ->setParameter(2, $format)
                ->setParameter(3, $name)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException) {
            throw new NotFoundException('Resource not found');
        }
    }

    /**
     * Fetch the rows that share the same `name` as the given template (i.e. its locale/format
     * siblings). Used by the admin edit modal so an internal user can jump between the HTML,
     * plain and Markdown versions of the same template (and the en_GB/cy_GB variants of each).
     *
     * @return array<int, array{id: int, locale: string, format: string}>
     */
    public function fetchSiblings(string $name, int $excludeId): array
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('t.id, t.locale, t.format')
            ->from(Entity::class, 't')
            ->where('t.name = :name')
            ->andWhere('t.id != :id')
            ->setParameter('name', $name)
            ->setParameter('id', $excludeId)
            ->orderBy('t.locale', 'ASC')
            ->addOrderBy('t.format', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * VOL-7238: group-by-name listing for the admin CMS index. Returns one row per `name`
     * with aggregated locales + formats + a `id` chosen for the Edit action.
     *
     * Two-pass to keep the query simple and avoid DQL GROUP_CONCAT extensions:
     *   1. Page through DISTINCT name (ordered by min(description) so admins see consistent
     *      sort across pages), applying the same category/format filters as applyListFilters.
     *   2. Fetch ALL variants for those names; group + aggregate in PHP.
     *
     * @return array<int, array{name:string, description:string, categoryName:?string,
     *                          locales:array<int, string>, formats:array<int, string>,
     *                          id:int}>
     */
    public function fetchTemplateGroups(QueryInterface $query): array
    {
        $names = $this->fetchPagedTemplateNames($query);
        if ($names === []) {
            return [];
        }

        $rows = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('t.id, t.name, t.locale, t.format, t.description, t.categoryName')
            ->from(Entity::class, 't')
            ->where('t.name IN (:names)')
            ->setParameter('names', $names)
            ->orderBy('t.name', 'ASC')
            ->addOrderBy('t.locale', 'ASC')
            ->addOrderBy('t.format', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $groups = [];
        foreach ($rows as $row) {
            $name = $row['name'];
            if (!isset($groups[$name])) {
                $groups[$name] = [
                    'name' => $name,
                    'description' => $row['description'],
                    'categoryName' => $row['categoryName'],
                    'locales' => [],
                    'formats' => [],
                    '_variants' => [], // working data for primary-variant resolution
                ];
            }
            if (!in_array($row['locale'], $groups[$name]['locales'], true)) {
                $groups[$name]['locales'][] = $row['locale'];
            }
            if (!in_array($row['format'], $groups[$name]['formats'], true)) {
                $groups[$name]['formats'][] = $row['format'];
            }
            $groups[$name]['_variants'][] = $row;
        }

        // Resolve primary variant id per group + use that variant's description for the
        // group's display description (so admins see the markdownified version when md is
        // the primary, instead of an arbitrary "template html" picked from the first row).
        foreach ($groups as $name => &$group) {
            $group['id'] = $this->resolvePrimaryVariantId($group['_variants']);
            foreach ($group['_variants'] as $v) {
                if ((int) $v['id'] === $group['id']) {
                    $group['description'] = $v['description'];
                    $group['categoryName'] = $v['categoryName'];
                    break;
                }
            }
            sort($group['locales']);
            sort($group['formats']);
            unset($group['_variants']);
        }
        unset($group);

        // Preserve the page's ordering (by name from $names). array_values keeps the
        // sequence and drops the name-keyed structure for the consuming handler.
        $ordered = [];
        foreach ($names as $name) {
            if (isset($groups[$name])) {
                $ordered[] = $groups[$name];
            }
        }
        return $ordered;
    }

    /**
     * Count of distinct `name` values matching the query's category/format filters.
     * Used by the paginator alongside `fetchTemplateGroups`. Custom because the default
     * AbstractRepository paginator assumes row-count == entity-count, which GROUP BY breaks.
     */
    public function countTemplateGroups(QueryInterface $query): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(DISTINCT m.name)')
            ->from(Entity::class, 'm');

        $this->applyTemplateGroupFilters($qb, $query);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Page 1 of the two-pass group fetch: paginated list of distinct names that match the
     * filters, ordered alphabetically by description (stable across pages).
     *
     * @return array<int, string>
     */
    private function fetchPagedTemplateNames(QueryInterface $query): array
    {
        $page = method_exists($query, 'getPage') ? (int) $query->getPage() : 1;
        $perPage = method_exists($query, 'getLimit') ? (int) $query->getLimit() : 25;
        if ($page < 1) {
            $page = 1;
        }
        if ($perPage < 1) {
            $perPage = 25;
        }

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('m.name AS name', 'MIN(m.description) AS description')
            ->from(Entity::class, 'm')
            ->groupBy('m.name')
            ->orderBy('description', 'ASC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $this->applyTemplateGroupFilters($qb, $query);

        $rows = $qb->getQuery()->getArrayResult();
        return array_map(static fn ($r) => (string) $r['name'], $rows);
    }

    /**
     * Apply group-aware filters: category (same semantics as applyListFilters) and
     * format-presence (EXISTS subquery — keeps rows whose `name` has ≥1 variant in the
     * requested format).
     */
    private function applyTemplateGroupFilters(QueryBuilder $qb, QueryInterface $query): void
    {
        if (method_exists($query, 'getEmailTemplateCategory') && is_numeric($query->getEmailTemplateCategory())) {
            $categoryId = $query->getEmailTemplateCategory();
            if ((int) $categoryId === 0) {
                $qb->andWhere($qb->expr()->isNull('m.category'));
            } else {
                $qb->andWhere($qb->expr()->eq('m.category', ':categoryId'))
                    ->setParameter('categoryId', $categoryId);
            }
        }

        if (method_exists($query, 'getFormat') && $query->getFormat() !== '') {
            // EXISTS subquery: keep names that have ≥1 variant in the requested format.
            // Different from per-row format filtering (applyListFilters) because here we
            // don't want to drop the en_GB/html row when filtering by md — we want to drop
            // the *entire* name (all its variants) only if it has zero md variants.
            $sub = $this->getEntityManager()->createQueryBuilder()
                ->select('1')
                ->from(Entity::class, 'fmt')
                ->where('fmt.name = m.name')
                ->andWhere('fmt.format = :format');
            $qb->andWhere($qb->expr()->exists($sub->getDQL()))
                ->setParameter('format', $query->getFormat());
        }
    }

    /**
     * Pick the "best" row to open by default when an admin clicks Edit on a group row.
     * Rule (VOL-7238):
     *   1. `en_GB / md` if present  (migration target — most likely thing to inspect)
     *   2. `en_GB / html` if present (legacy primary; fallback during in-flight conversion)
     *   3. Lowest id otherwise      (edge cases like en_CY-only rows)
     *
     * @param array<int, array{id:int, locale:string, format:string}> $variants
     */
    private function resolvePrimaryVariantId(array $variants): int
    {
        $byLocaleFormat = [];
        $lowestId = PHP_INT_MAX;
        foreach ($variants as $v) {
            $key = $v['locale'] . '/' . $v['format'];
            $byLocaleFormat[$key] = (int) $v['id'];
            if ((int) $v['id'] < $lowestId) {
                $lowestId = (int) $v['id'];
            }
        }
        if (isset($byLocaleFormat['en_GB/md'])) {
            return $byLocaleFormat['en_GB/md'];
        }
        if (isset($byLocaleFormat['en_GB/html'])) {
            return $byLocaleFormat['en_GB/html'];
        }
        return $lowestId;
    }

    /**
     * Fetch distinct categories from template rows.
     *
     * @return array
     */
    public function fetchDistinctCategories()
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('cat.description', 'cat.id')
            ->from(Entity::class, 't')
            ->distinct()
            ->innerJoin('t.category', 'cat')
            ->where('t.category IS NOT NULL')
            ->getQuery()->getResult();
    }
}
