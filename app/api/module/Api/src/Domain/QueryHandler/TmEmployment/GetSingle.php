<?php

/**
 * TmEmployment
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TmEmployment;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * TmEmployment
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetSingle extends AbstractQueryHandler
{
    protected $repoServiceName = 'TmEmployment';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query),
            [
                'contactDetails' => [
                    'address' => [
                        'countryCode',
                    ]
                ]
            ]
        );
    }
}
