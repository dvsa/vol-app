<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits\Report;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Service\PermitsReportService;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Permits Report List
 */
class ReportList extends AbstractQueryHandler
{
    /**
     * @inheritdoc
     */
    public function handleQuery(QueryInterface $query)
    {
        return [
            'result' => $types = PermitsReportService::REPORT_TYPES,
            'count' => count($types),
        ];
    }
}
