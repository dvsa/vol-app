<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\ConditionUndertaking;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get a ConditionUndertaking
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Get extends AbstractQueryHandler
{
    protected $repoServiceName = 'ConditionUndertaking';

    public function handleQuery(QueryInterface $query)
    {
        /* @var $query \Dvsa\Olcs\Transfer\Query\ConditionUndertaking\Get */

        $conditionUndertaking = $this->getRepo()->fetchUsingId($query);

        return $this->result(
            $conditionUndertaking,
            [
                'application' => ['licence'],
                'licence',
                'operatingCentre',
            ]
        );
    }
}
