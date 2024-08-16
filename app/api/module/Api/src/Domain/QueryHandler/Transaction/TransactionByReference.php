<?php

/**
 * Transaction
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Transaction;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Transaction
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransactionByReference extends AbstractQueryHandler
{
    protected $repoServiceName = 'Transaction';

    public function handleQuery(QueryInterface $query)
    {
        $transaction = $this->getRepo()->fetchByReference($query->getReference());

        return $this->result(
            $transaction,
            [
                'feeTransactions' => [
                    'fee' => [
                        'application',
                        'licence' => [
                            'organisation',
                        ],
                        'feeType' => [
                            'feeType'
                        ]
                    ],
                ]
            ]
        );
    }
}
