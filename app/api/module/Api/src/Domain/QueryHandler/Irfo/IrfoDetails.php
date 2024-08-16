<?php

/**
 * IrfoDetails
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Irfo;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * IrfoDetails
 */
class IrfoDetails extends AbstractQueryHandler
{
    protected $repoServiceName = 'Organisation';

    public function handleQuery(QueryInterface $query)
    {
        // get only trading names which are not linked to a licence
        $irfoOnly = Criteria::create();
        $irfoOnly->andWhere(
            $irfoOnly->expr()->isNull('licence')
        );

        return $this->result(
            $this->getRepo()->fetchIrfoDetailsUsingId($query),
            [
                'tradingNames' => [
                    'criteria' => $irfoOnly
                ],
                'irfoNationality',
                'irfoPartners',
                'irfoContactDetails' => [
                    'address' => [
                        'countryCode'
                    ],
                    'phoneContacts'
                ]
            ]
        );
    }
}
