<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\Continuations;

use Dvsa\Olcs\Api\Domain\Repository\Query\Continuations\CreateContinuationDetails;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\BaseAbstractDbQueryTestCase;

/**
 * Create continuation details test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateContinuationDetailsTest extends BaseAbstractDbQueryTestCase
{
    protected $tableNameMap = [
        ContinuationDetail::class => 'continuation_detail'
    ];

    protected $columnNameMap = [
        ContinuationDetail::class => [
            'licence' => [
                'isAssociation' => true,
                'column' => 'licence_id'
            ],
            'received' => [
                'column' => 'received'
            ],
            'status' => [
                'column' => 'status'
            ],
            'continuation' => [
                'isAssociation' => true,
                'column' => 'continuation_id'
            ],
            'createdOn' => [
                'column' => 'created_on'
            ],
            'createdBy' => [
                'column' => 'created_by'
            ],
        ],
    ];

    protected function getSut(): CreateContinuationDetails
    {
        return new CreateContinuationDetails();
    }

    protected function getExpectedQuery(): string
    {
        return '';
    }

    public function testExecuteInsert(): void
    {
        $this->connection->shouldReceive('quote')
            ->times(8)
            ->andReturnUsing(
                function ($arg) {
                    $quoted = [
                        0 => "'0'",
                        1 => "'1'",
                        2 => "'2'",
                        'status' => "'status'"
                    ];
                    return $quoted[$arg];
                }
            );

        $this->connection->shouldReceive('executeUpdate')
            ->with(
                'INSERT INTO continuation_detail '
                . '(licence_id, received, status, continuation_id, created_on, created_by) '
                . 'VALUES (\'1\', \'0\', \'status\', \'2\', NOW(), 1), '
                . '(\'1\', \'0\', \'status\', \'2\', NOW(), 1)'
            )->once()
            ->andReturn(1);
        $this->assertEquals(1, $this->sut->executeInsert([1, 1], false, 'status', 2));
    }

    public function testExecuteInsertException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\RuntimeException::class);

        $this->connection->shouldReceive('quote')
            ->times(4)
            ->andReturnUsing(
                function ($arg) {
                    $quoted = [
                        0 => "'0'",
                        1 => "'1'",
                        2 => "'2'",
                        'status' => "'status'"
                    ];
                    return $quoted[$arg];
                }
            );

        $this->connection->shouldReceive('executeUpdate')
            ->with(
                'INSERT INTO continuation_detail '
                . '(licence_id, received, status, continuation_id, created_on, created_by) '
                . 'VALUES (\'1\', \'0\', \'status\', \'2\', NOW(), 1)'
            )->once()
            ->andThrow(new \Exception());

        $this->sut->executeInsert([1], false, 'status', 2);
    }
}
