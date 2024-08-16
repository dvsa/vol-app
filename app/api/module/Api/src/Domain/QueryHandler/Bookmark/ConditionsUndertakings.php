<?php

/**
 * ConditionsUndertakings Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * ConditionsUndertakings Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionsUndertakings extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    public function handleQuery(QueryInterface $query)
    {
        /** @var Licence $licence */
        $licence = $this->getRepo()->fetchUsingId($query);

        $criteria = Criteria::create();
        $criteria->andWhere($criteria->expr()->eq('isDraft', 0));
        $criteria->andWhere($criteria->expr()->eq('isFulfilled', 0));
        $criteria->andWhere(
            $criteria->expr()->eq('attachedTo', $this->getRepo()->getRefdataReference($query->getAttachedTo()))
        );
        $criteria->andWhere(
            $criteria->expr()->eq('conditionType', $this->getRepo()->getRefdataReference($query->getConditionType()))
        );

        $conditionsUndertakings = $licence->getConditionUndertakings()->matching($criteria);

        $bundle = [
            'attachedTo',
            'conditionType'
        ];

        return $this->result(
            $licence,
            [],
            [
                'conditionUndertakings' => $this->resultList($conditionsUndertakings, $bundle)
            ]
        )->serialize();
    }
}
