<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\Discs;

use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository\Query\Discs\CreatePsvDiscs;
use Dvsa\Olcs\Api\Entity\Licence\PsvDisc;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\BaseAbstractDbQueryTestCase;

/**
 * CreatePsvDiscsTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreatePsvDiscsTest extends BaseAbstractDbQueryTestCase
{
    protected $tableNameMap = [
        PsvDisc::class => 'psv_disc'
    ];

    protected $columnNameMap = [
        PsvDisc::class => [
            'licence' => [
                'isAssociation' => true,
                'column' => 'licence_id'
            ],
            'isCopy' => [
                'column' => 'is_copy'
            ],
            'createdOn' => [
                'column' => 'created_on'
            ],
            'createdBy' => [
                'column' => 'created_by'
            ],
            'lastModifiedOn' => [
                'column' => 'last_modified_on'
            ],
            'lastModifiedBy' => [
                'column' => 'last_modified_by'
            ],
        ],
    ];

    protected function getSut(): CreatePsvDiscs
    {
        return new CreatePsvDiscs();
    }

    protected function getExpectedQuery(): string
    {
        return '';
    }

    public function testExecuteInsert(): void
    {
        $this->connection->shouldReceive('quote')->with(1102)->times(4)->andReturn("'1102'");
        $this->connection->shouldReceive('quote')->with(0)->times(4)->andReturn("'0'");

        $sql =
            'INSERT INTO psv_disc (licence_id, is_copy, created_on, created_by, last_modified_on, last_modified_by) ' .
            'VALUES (\'1102\', \'0\', NOW(), 1, NOW(), 1), (\'1102\', \'0\', NOW(), 1, NOW(), 1), ' .
            '(\'1102\', \'0\', NOW(), 1, NOW(), 1), (\'1102\', \'0\', NOW(), 1, NOW(), 1)';

        $this->connection->shouldReceive('executeUpdate')
            ->with($sql)
            ->once()
            ->andReturn(100);

        $this->assertEquals(100, $this->sut->executeInsert(1102, 4, false));
    }

    public function testExecuteZeroDiscs(): void
    {
        $this->assertEquals(0, $this->sut->executeInsert(1102, 0, false));
    }

    public function testExecuteInsertException(): void
    {
        $this->expectException(RuntimeException::class);

        $this->connection->shouldReceive('quote')->with(1102)->times(4)->andReturn("'1102'");
        $this->connection->shouldReceive('quote')->with(0)->times(4)->andReturn("'0'");

        $sql =
            'INSERT INTO psv_disc (licence_id, is_copy, created_on, created_by, last_modified_on, last_modified_by) ' .
            'VALUES (\'1102\', \'0\', NOW(), 1, NOW(), 1), (\'1102\', \'0\', NOW(), 1, NOW(), 1), ' .
            '(\'1102\', \'0\', NOW(), 1, NOW(), 1), (\'1102\', \'0\', NOW(), 1, NOW(), 1)';

        $this->connection->shouldReceive('executeUpdate')
            ->with($sql)
            ->once()
            ->andThrow(new \Exception());

        $this->assertEquals('result', $this->sut->executeInsert(1102, 4, false));
    }
}
