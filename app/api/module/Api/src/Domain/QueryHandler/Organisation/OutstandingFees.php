<?php

/**
 * Organisation - Outstanding Fees
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Organisation;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Organisation - Outstanding Fees
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OutstandingFees extends AbstractQueryHandler
{
    protected $repoServiceName = 'Organisation';

    protected $extraRepos = ['Fee', 'SystemParameter'];

    public function handleQuery(QueryInterface $query)
    {
        $organisation = $this->getRepo()->fetchUsingId($query);

        $fees = $this->getRepo('Fee')->fetchOutstandingFeesByOrganisationId(
            $organisation->getId(),
            $query->getHideExpired(),
            true
        );

        return $this->result(
            $organisation,
            [],
            [
                'outstandingFees' => $this->resultList(
                    $fees,
                    [
                        'licence',
                        'feeTransactions' => [
                            'transaction'
                        ],
                        'feeType' => [
                            'feeType'
                        ]
                    ]
                ),
                'disableCardPayments' => $this->getRepo('SystemParameter')->getDisableSelfServeCardPayments(),
            ]
        );
    }
}
