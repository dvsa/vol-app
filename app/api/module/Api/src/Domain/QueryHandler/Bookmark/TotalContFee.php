<?php

/**
 * TotalContFee Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * TotalContFee Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TotalContFee extends AbstractQueryHandler
{
    protected $repoServiceName = 'FeeType';

    public function handleQuery(QueryInterface $query)
    {
        $feeType = $this->getRepo()->fetchLatest(
            $this->getRepo()->getRefdataReference(FeeType::FEE_TYPE_CONT),
            $this->getRepo()->getRefdataReference($query->getGoodsOrPsv()),
            $this->getRepo()->getRefdataReference($query->getLicenceType()),
            new \DateTime($query->getEffectiveFrom()),
            $query->getTrafficArea()
        );

        return $this->result($feeType)->serialize();
    }
}
