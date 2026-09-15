<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter;

use Doctrine\ORM\EntityManagerInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection;

/**
 * Suggests real records that would exercise a composition's specific variants.
 *
 * A composition branches on a small set of context dimensions. Rather than making an admin
 * hunt for licences that happen to hit each branch, this derives the distinct combinations the
 * variants actually pin and finds one example application per combination. "None found" is a
 * result too: it tells the admin that branch can only be previewed with manual overrides.
 *
 * Only the record-derived dimensions are searched. Letter choices are picked by the caseworker
 * at generation time, not carried by a record, so they play no part here.
 */
class PreviewRecordSuggester
{
    private const RECORD_DIMENSIONS = ['goodsOrPsv', 'isVariation', 'isNi', 'organisationType'];

    /**
     * Never suggested. Their data still exists, but offering a withdrawn or refused
     * application as "a record to preview with" reads as a mistake to a caseworker,
     * and terminal records are the most likely to carry odd or incomplete data.
     */
    private const EXCLUDED_STATUSES = [
        'apsts_cancelled',
        'apsts_ntu',
        'apsts_refused',
        'apsts_withdrawn',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @param LetterSection[] $sections the sections of the composition being previewed
     * @return array{dimensions: array, record: array|null}[]
     */
    public function suggest(array $sections): array
    {
        $suggestions = [];

        foreach ($this->deriveCombinations($sections) as $combination) {
            $suggestions[] = [
                'dimensions' => $combination,
                'record' => $this->findExampleFor($combination),
            ];
        }

        return $suggestions;
    }

    /**
     * The distinct pinned-dimension vectors across every live variant of every section.
     *
     * Each vector is a context that would resolve at least one specific variant; the resolver
     * picks the most specific match per section, so a record matching a fuller vector also
     * exercises the compatible less-specific variants of other sections.
     *
     * @param LetterSection[] $sections
     * @return array<string, array> keyed by a stable signature, values are dimension arrays
     */
    public function deriveCombinations(array $sections): array
    {
        $combinations = [];

        foreach ($sections as $section) {
            foreach ($section->getVariants() as $variant) {
                if ($variant->isDeleted()) {
                    continue;
                }

                $vector = array_filter([
                    'goodsOrPsv' => $variant->getGoodsOrPsv()?->getId(),
                    'isVariation' => $variant->getIsVariation(),
                    'isNi' => $variant->getIsNi(),
                    'organisationType' => $variant->getOrganisationType()?->getId(),
                ], static fn($v) => $v !== null);

                if ($vector === []) {
                    // A default variant, or one pinned only on a letter choice --
                    // any record satisfies it, so there is nothing to suggest
                    continue;
                }

                $signature = implode('|', array_map(
                    static fn(string $dim) => $dim . '=' . var_export($vector[$dim] ?? null, true),
                    self::RECORD_DIMENSIONS
                ));

                $combinations[$signature] = $vector;
            }
        }

        ksort($combinations);

        return $combinations;
    }

    /**
     * One example application matching the combination, newest first, or null.
     *
     * @return array{applicationId: int, licenceId: int, licNo: string|null, status: string|null,
     *               isVariation: bool}|null
     */
    protected function findExampleFor(array $combination): ?array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select(
                'a.id AS applicationId',
                'a.isVariation AS isVariation',
                'l.id AS licenceId',
                'l.licNo AS licNo',
                's.description AS status'
            )
            ->from(Application::class, 'a')
            ->join('a.licence', 'l')
            ->join('l.organisation', 'o')
            ->join('a.status', 's')
            ->andWhere('IDENTITY(a.status) NOT IN (:excludedStatuses)')
            ->setParameter('excludedStatuses', self::EXCLUDED_STATUSES)
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(1);

        if (isset($combination['goodsOrPsv'])) {
            // The context derivation prefers the application's own vehicle type and falls
            // back to the licence's, so the search must apply the same rule
            $qb->andWhere('COALESCE(IDENTITY(a.goodsOrPsv), IDENTITY(l.goodsOrPsv)) = :goodsOrPsv')
                ->setParameter('goodsOrPsv', $combination['goodsOrPsv']);
        }

        if (isset($combination['isVariation'])) {
            $qb->andWhere('a.isVariation = :isVariation')
                ->setParameter('isVariation', $combination['isVariation']);
        }

        if (isset($combination['isNi'])) {
            // NI status is derived from the traffic area (Licence::getNiFlag()), with
            // no traffic area counting as GB; the licence ni_flag column is unmapped
            $qb->leftJoin('l.trafficArea', 'ta')
                ->andWhere('COALESCE(ta.isNi, FALSE) = :isNi')
                ->setParameter('isNi', $combination['isNi']);
        }

        if (isset($combination['organisationType'])) {
            $qb->andWhere('IDENTITY(o.type) = :organisationType')
                ->setParameter('organisationType', $combination['organisationType']);
        }

        $rows = $qb->getQuery()->getArrayResult();

        if ($rows === []) {
            return null;
        }

        $row = $rows[0];
        $row['isVariation'] = (bool) $row['isVariation'];

        return $row;
    }
}
