<?php

/**
 * Get discs numbering information
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\DiscSequence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\DiscSequence as DiscSequenceEntity;

/**
 * Get discs numbering information
 */
class DiscsNumbering extends AbstractQueryHandler
{
    protected $repoServiceName = 'DiscSequence';

    protected $extraRepos = ['GoodsDisc', 'PsvDisc'];

    /**
     * @param QueryInterface|\Dvsa\Olcs\Transfer\Query\DiscSequence\DiscsNumbering $query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        $result = [];
        if (
            !$query->getNiFLag() ||
            ($query->getNiFlag() === 'N' && !$query->getOperatorType()) ||
            !$query->getLicenceType() ||
            !$query->getDiscSequence()
        ) {
            return ['result' => $result, 'count' => 0];
        }

        // calculate start and end numbers, number of pages

        $discSequence = $this->getRepo()->fetchById($query->getDiscSequence());
        $result['startNumber'] = $discSequence->getDiscNumber($query->getLicenceType());

        if ($query->getOperatorType() === LicenceEntity::LICENCE_CATEGORY_PSV) {
            $result['discsToPrint'] = count(
                $this->getRepo('PsvDisc')->fetchDiscsToPrintMin($query->getLicenceType())
            );
        } else {
            $result['discsToPrint'] = count(
                $this->getRepo('GoodsDisc')->fetchDiscsToPrintMin($query->getNiFlag(), $query->getLicenceType())
            );
        }
        if ($query->getMaxPages()) {
            $result['discsToPrint'] = min(
                $result['discsToPrint'],
                $query->getMaxPages() * DiscSequenceEntity::DISCS_ON_PAGE
            );
        }

        $result['endNumber'] = (int)($result['discsToPrint'] ?
            $result['startNumber'] + $result['discsToPrint'] - 1 : 0);
        $result['originalEndNumber'] = $result['endNumber'];
        $originalStartNumber = $result['startNumber'];

        // if we received start number this means that user changed this value and we need to validate it
        // do not allow to decrease start number
        if ($query->getStartNumberEntered() && $query->getStartNumberEntered() < $result['startNumber']) {
            $result['error'] = 'Decreasing the start number is not permitted';
        } elseif ($query->getStartNumberEntered() && $query->getStartNumberEntered() > $result['startNumber']) {
            // increasing start and end numbers
            $delta = $query->getStartNumberEntered() - $result['startNumber'];
            $result['startNumber'] = $query->getStartNumberEntered();
            $result['endNumber'] += $delta;
        }
        /*
         * we have two end numbers, one original, which calculated based on start number entered by user
         * and another one calculated by rounding up to nearest integer divided by 6. that's because
         * there are numbers already printed on the discs pages, 6 discs pere page, and even we need to print
         * only one disc, other numbers will be used voided.
         */
        $result['endNumberIncreased'] = $result['endNumber'];
        if ($result['endNumber']) {
            $result['endNumber'] =
                $result['startNumber'] +
                $result['discsToPrint'] +
                ((6 - $result['discsToPrint'] % DiscSequenceEntity::DISCS_ON_PAGE) % 6) - 1;
        }
        $result['totalPages'] = (int)$result['discsToPrint'] ?
            (ceil(($result['endNumber'] - $originalStartNumber) / DiscSequenceEntity::DISCS_ON_PAGE)) -
            (floor(($result['startNumber'] - $originalStartNumber) / DiscSequenceEntity::DISCS_ON_PAGE)) : 0;

        return [
            'result' => $result,
            'count' => count($result)
        ];
    }
}
