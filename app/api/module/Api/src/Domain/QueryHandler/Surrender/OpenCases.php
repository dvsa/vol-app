<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Surrender;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class OpenCases extends AbstractQueryHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::BACKEND_SURRENDER];
    protected $repoServiceName = 'Cases';

    public function handleQuery(QueryInterface $query)
    {
        /**
         * @var \Dvsa\Olcs\Api\Domain\Repository\Cases
         */
        $cases = $this->getRepo()->fetchOpenCasesForSurrender($query);

            return [
                'count' => count($cases),
                'results' => $this->resultList($cases)
            ];
    }
}
