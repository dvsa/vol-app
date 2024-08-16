<?php

/**
 * Unlicensed Operator Business Details
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Operator;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Unlicensed Operator Business Details
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UnlicensedBusinessDetails extends AbstractQueryHandler
{
    protected $repoServiceName = 'Organisation';

    public function handleQuery(QueryInterface $query)
    {
        $organisation = $this->getRepo()->fetchUsingId($query);
        return $this->result(
            $organisation,
            [
                'licences' => [
                    'correspondenceCd' => [
                        'address' => [
                            'countryCode',
                        ],
                        'phoneContacts' => [
                            'phoneContactType',
                        ],
                    ],
                    'goodsOrPsv',
                    'trafficArea',
                ],
            ]
        );
    }
}
