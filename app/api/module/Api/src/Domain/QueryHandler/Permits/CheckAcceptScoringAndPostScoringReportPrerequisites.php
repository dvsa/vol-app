<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\Query\Permits\CheckAcceptScoringAndPostScoringReportPrerequisites
    as CheckAcceptScoringAndPostScoringReportPrerequisitesQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Check accept scoring and post scoring report prerequisites
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CheckAcceptScoringAndPostScoringReportPrerequisites extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitRange';

    protected $extraRepos = ['IrhpPermit', 'IrhpApplication'];

    /**
     * Handle query
     *
     * @param QueryInterface|CheckAcceptScoringAndPostScoringReportPrerequisitesQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $stockId = $query->getId();

        $emissionsCategories = [
            RefData::EMISSIONS_CATEGORY_EURO6_REF => 'Euro 6',
            RefData::EMISSIONS_CATEGORY_EURO5_REF => 'Euro 5'
        ];

        $irhpApplicationRepo = $this->getRepo('IrhpApplication');

        if (!$irhpApplicationRepo->hasInScopeUnderConsiderationApplications($stockId)) {
            return $this->generateResponse(false, 'No under consideration applications currently in scope');
        }

        foreach ($emissionsCategories as $emissionsCategoryId => $emissionsCategoryCaption) {
            $permitsRequired = $irhpApplicationRepo->getSuccessfulCountInScope(
                $stockId,
                $emissionsCategoryId
            );

            if ($permitsRequired > 0) {
                $combinedRangeSize = $this->getRepo()->getCombinedRangeSize(
                    $stockId,
                    $emissionsCategoryId
                );

                if (is_null($combinedRangeSize)) {
                    return $this->generateResponse(
                        false,
                        sprintf(
                            '%d %s permits required but no %s ranges available',
                            $permitsRequired,
                            $emissionsCategoryCaption,
                            $emissionsCategoryCaption
                        )
                    );
                }

                $assignedPermits = $this->getRepo('IrhpPermit')->getPermitCount(
                    $stockId,
                    $emissionsCategoryId
                );

                $permitsAvailable = $combinedRangeSize - $assignedPermits;
                if ($permitsAvailable < $permitsRequired) {
                    return $this->generateResponse(
                        false,
                        sprintf(
                            'Insufficient %s permits available - %s available, %s required',
                            $emissionsCategoryCaption,
                            $permitsAvailable,
                            $permitsRequired
                        )
                    );
                }
            }
        }

        return $this->generateResponse(true, 'Prerequisites passed');
    }

    /**
     * Generate an array representing the query response
     *
     * @param bool $permitted
     * @param string $message
     *
     * @return array
     */
    private function generateResponse($permitted, $message)
    {
        return [
            'result' => $permitted,
            'message' => $message
        ];
    }
}
