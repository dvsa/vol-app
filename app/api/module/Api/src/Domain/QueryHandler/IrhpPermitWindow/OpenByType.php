<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitWindow;

use DateTime;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Open Windows by Permit Type
 *
 * @author Andy Newton
 */
class OpenByType extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitWindow';
    protected $bundle = ['irhpPermitStock' => ['irhpPermitType' => ['name'], 'country']];

    public function handleQuery(QueryInterface $query)
    {
        // fetch the list of all open windows for selected countries
        $openWindows = $this->getRepo('IrhpPermitWindow')->fetchOpenWindowsByType(
            $query->getIrhpPermitType(),
            new DateTime()
        );

        return [
            'result' => $this->resultList(
                $openWindows,
                $this->bundle
            )
        ];
    }
}
