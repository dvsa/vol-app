<?php

/**
 * Cpms Report List
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cpms;

use Dvsa\Olcs\Api\Domain\CpmsAwareInterface;
use Dvsa\Olcs\Api\Domain\CpmsAwareTrait;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Cpms Report List
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ReportList extends AbstractQueryHandler implements CpmsAwareInterface
{
    use CpmsAwareTrait;

    /**
     * @inheritdoc
     */
    public function handleQuery(QueryInterface $query)
    {
        $result = [];
        $count = 0;

        $data = $this->getCpmsService()->getReportList();
        if (isset($data['items'])) {
            $result = $data['items'];
            $count = count($result);
        }
        return [
            'result' => $result,
            'count' => $count,
        ];
    }
}
