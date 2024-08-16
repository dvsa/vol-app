<?php

/**
 * Disc Sequence - get discs prefixes
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\DiscSequence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Disc Prefix
 */
class DiscPrefixes extends AbstractQueryHandler
{
    protected $repoServiceName = 'DiscSequence';

    public function handleQuery(QueryInterface $query)
    {
        if ((($query->getNiFlag() === 'N' && !$query->getOperatorType()) || !$query->getLicenceType())) {
            return ['result' => [], 'count' => 0];
        }
        $discPrefixes = $this->getRepo()->fetchDiscPrefixes($query->getNiFlag(), $query->getOperatorType());
        $result = $this->getDiscPrefixes($discPrefixes, $query->getLicenceType());
        return [
            'result' => $result,
            'count'  => count($result)
        ];
    }

    public function getDiscPrefixes($discPrefixes, $licenceType)
    {
        $result = [];
        foreach ($discPrefixes as $entity) {
            $result[$entity->getId()] = $entity->getDiscPrefix($licenceType);
        }
        return $result;
    }
}
