<?php

/**
 * InterimConditionsUndertakings Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * InterimConditionsUndertakings Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class InterimConditionsUndertakings extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    public function handleQuery(QueryInterface $query)
    {
        /** @var Application $licence */
        $application = $this->getRepo()->fetchUsingId($query);

        $criteria = Criteria::create();
        $criteria->andWhere($criteria->expr()->eq('isDraft', 0));
        $criteria->andWhere($criteria->expr()->eq('isFulfilled', 0));
        $criteria->andWhere(
            $criteria->expr()->eq('attachedTo', $this->getRepo()->getRefdataReference($query->getAttachedTo()))
        );
        $criteria->andWhere(
            $criteria->expr()->eq('conditionType', $this->getRepo()->getRefdataReference($query->getConditionType()))
        );

        return $this->result(
            $application,
            [
                'conditionUndertakings' => [
                    'attachedTo',
                    'conditionType',
                    'licConditionVariation'
                ],
                'licence' => [
                    /**
                     * We have the luxury of being able to filter the C&Us against the
                     * licence since if they're not in a relevant state we aren't interested
                     * and if they HAVE been updated via an app delta, we'll get that in the
                     * application's child bundle instead
                     */
                    'conditionUndertakings' => [
                        'criteria' => $criteria,
                        'attachedTo',
                        'conditionType',
                        'licConditionVariation'
                    ]
                ],
            ]
        )->serialize();
    }
}
