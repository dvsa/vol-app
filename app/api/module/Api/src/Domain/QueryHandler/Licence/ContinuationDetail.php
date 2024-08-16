<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * ContinuationDetail
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationDetail extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';
    protected $extraRepos = ['ContinuationDetail', 'Fee'];

    public function handleQuery(QueryInterface $query)
    {
        /* @var $licence \Dvsa\Olcs\Api\Entity\Licence\Licence */
        $licence = $this->getRepo()->fetchUsingId($query);

        $continuationDetail = $this->getContinuationDetail($licence);
        $continuationDetailResponse = ($continuationDetail) ?
            $this->result($continuationDetail, ['continuation', 'licence'])->serialize() :
            null;

        $outstandingContinuationFees = $this->getRepo('Fee')
            ->fetchOutstandingContinuationFeesByLicenceId($licence->getId());

        return $this->result(
            $licence,
            [],
            [
                'continuationDetail' => $continuationDetailResponse,
                'numNotCeasedDiscs' => $licence->getPsvDiscsNotCeased()->count(),
                'hasOutstandingContinuationFee' => count($outstandingContinuationFees) > 0,
            ]
        );
    }

    /**
     * Get a Continuation Detail for the marker
     *
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail|null
     */
    private function getContinuationDetail(\Dvsa\Olcs\Api\Entity\Licence\Licence $licence)
    {
        $continuationDetails = $this->getRepo('ContinuationDetail')->fetchForLicence($licence->getId());
        if (count($continuationDetails) > 0) {
            return $continuationDetails[0];
        }

        return null;
    }
}
