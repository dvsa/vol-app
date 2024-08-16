<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitStock;

use DateTime;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Available bilateral stocks
 */
class AvailableBilateral extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitStock';

    protected $extraRepos = ['ApplicationPathGroup'];

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handleQuery(QueryInterface $query)
    {
        $stocks = $this->getRepo()->fetchOpenBilateralStocksByCountry($query->getCountry(), new DateTime());

        foreach ($stocks as $key => $stock) {
            $applicationPathGroup = $this->getRepo('ApplicationPathGroup')->fetchById(
                $stock['application_path_group_id']
            );

            $slug = $applicationPathGroup->getActiveApplicationPath()
                ->getApplicationSteps()
                ->first()
                ->getQuestion()
                ->getSlug();

            $stocks[$key]['first_step_slug'] = $slug;
        }

        return $stocks;
    }
}
