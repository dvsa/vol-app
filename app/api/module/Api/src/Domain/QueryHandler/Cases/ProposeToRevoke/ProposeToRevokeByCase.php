<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\ProposeToRevoke;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\ProposeToRevoke;
use Dvsa\Olcs\Api\Entity\System\SlaTargetDate;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * ProposeToRevokeByCase
 */
final class ProposeToRevokeByCase extends AbstractQueryHandler
{
    protected $repoServiceName = 'ProposeToRevoke';

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var ProposeToRevoke $repo */
        $repo = $this->getRepo();

        // retrieve reason even if deleted
        $repo->disableSoftDeleteable(
            [
                \Dvsa\Olcs\Api\Entity\Pi\Reason::class
            ]
        );

        $proposeToRevoke = $repo->fetchProposeToRevokeUsingCase($query);

        $slaValues = [];
        if ($proposeToRevoke) {
            /** @var SlaTargetDate $slaTargetDate */
            foreach ($proposeToRevoke->getSlaTargetDates() as $slaTargetDate) {
                $slaValues[$slaTargetDate->getSla()->getField() . 'Target'] = $slaTargetDate->getTargetDate();
            }
        }

        return $this->result(
            $proposeToRevoke,
            [
                'presidingTc',
                'reasons',
                'assignedCaseworker',
                'actionToBeTaken',
                'approvalSubmissionPresidingTc',
                'finalSubmissionPresidingTc'
            ],
            $slaValues
        );
    }
}
