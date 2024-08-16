<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\FeePerPermit as FeePerPermitQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Fee per permit
 */
class FeePerPermit extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpApplication';

    protected $extraRepos = ['FeeType'];

    /**
     * Handle query
     *
     * @param QueryInterface|FeePerPermitQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $irhpApplication = $this->getRepo()->fetchUsingId($query);

        $permittedPermitTypeIds = [
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL
        ];

        if (!in_array($irhpApplication->getIrhpPermitType()->getId(), $permittedPermitTypeIds)) {
            throw new ForbiddenException('FeePerPermit query only supports bilateral and multilateral types');
        }

        $feeTypeRepo = $this->getRepo('FeeType');

        $applicationFeeType = $feeTypeRepo->getLatestByProductReference(
            $irhpApplication->getApplicationFeeProductReference()
        );

        $irhpPermitApplications = $irhpApplication->getIrhpPermitApplications();
        $feesPerPermit = [];

        foreach ($irhpPermitApplications as $irhpPermitApplication) {
            $issueFeeType = $feeTypeRepo->getLatestByProductReference(
                $irhpPermitApplication->getIssueFeeProductReference()
            );

            $feesPerPermit[$irhpPermitApplication->getId()] = $irhpApplication->getFeePerPermit(
                $applicationFeeType,
                $issueFeeType
            );
        }

        return $feesPerPermit;
    }
}
