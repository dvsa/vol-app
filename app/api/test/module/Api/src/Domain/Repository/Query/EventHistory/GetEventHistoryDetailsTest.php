<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\EventHistory;

use Dvsa\Olcs\Api\Domain\Repository\Query\EventHistory\GetEventHistoryDetails;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;

/**
 * Get event history details test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GetEventHistoryDetailsTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [];
    protected $columnNameMap = [];

    /**
     * Param provider
     *
     * @return array
     */
    public static function paramProvider(): array
    {
        return [
            [
                [1, [1, 2]],
                ['version' => \Doctrine\DBAL\Connection::PARAM_INT_ARRAY],
                [1, [1, 2]],
                ['version' => \Doctrine\DBAL\Connection::PARAM_INT_ARRAY]
            ]
        ];
    }

    /**
     * Get sut
     *
     * @return mixed
     */
    protected function getSut(): mixed
    {
        $sut = new GetEventHistoryDetails();
        $sut->setHistoryTable('application_hist');
        return $sut;
    }

    /**
     * Get expected query
     *
     * @return string
     */
    protected function getExpectedQuery(): string
    {
        return 'SELECT * FROM application_hist WHERE id = :id AND version IN (:version) ORDER BY version DESC LIMIT 2';
    }
}
