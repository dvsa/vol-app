<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Diagnostics;

use Doctrine\ORM\EntityManager;
use Dvsa\Olcs\Api\Domain\Query\Diagnostics\GenerateCheckFkIntegritySql as GenerateCheckFkIntegritySqlCmd;
use Dvsa\Olcs\Api\Domain\QueryHandler\Diagnostics\GenerateCheckFkIntegritySql;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use PDO;
use PDOStatement;
use RuntimeException;

class GenerateCheckFkIntegritySqlTest extends QueryHandlerTestCase
{
    /** @var PDO|m\MockInterface */
    private $mockPdo;

    public function setUp(): void
    {
        $this->sut = new GenerateCheckFkIntegritySql();
        $mockDoctrineEntityManager = m::mock(EntityManager::class);
        $this->mockedSmServices['doctrine.entitymanager.orm_default'] = $mockDoctrineEntityManager;

        $mockDoctrineEntityManager
            ->shouldReceive('getConnection->getParams')
            ->andReturn(['dbname' => 'DUMMY-DB-NAME']);

        $this->mockPdo = m::mock(PDO::class);
        $mockDoctrineEntityManager
            ->shouldReceive('getConnection->getNativeConnection')
            ->andReturn($this->mockPdo);

        $this->mockPdo->shouldReceive('quote')->andReturnUsing(
            fn($str) => var_export("[[$str]]", true)
        );

        parent::setUp();
    }

    public function testQuery()
    {
        $this->expectConstraintsQuery(
            [
                [
                    'TABLE_SCHEMA' => 'DB_0',
                    'TABLE_NAME' => 'TBL_0',
                    'COLUMN_NAME' => 'COL_0',
                    'REFERENCED_TABLE_SCHEMA' => 'DB_1',
                    'REFERENCED_TABLE_NAME' => 'TBL_1',
                    'REFERENCED_COLUMN_NAME' => 'COL_1',
                    'CONSTRAINT_NAME' => 'DUMMY_CONSTRAINT_NAME_A'
                ],
                [
                    'TABLE_SCHEMA' => 'DB_0',
                    'TABLE_NAME' => 'TBL_0',
                    'COLUMN_NAME' => 'COL_2',
                    'REFERENCED_TABLE_SCHEMA' => 'DB_1',
                    'REFERENCED_TABLE_NAME' => 'TBL_1',
                    'REFERENCED_COLUMN_NAME' => 'COL_1',
                    'CONSTRAINT_NAME' => 'DUMMY_CONSTRAINT_NAME_B'
                ],
            ]
        );

        $result = $this->sut->handleQuery(GenerateCheckFkIntegritySqlCmd::create([]));
        $this->assertArrayHasKey('queries', $result);
        $this->assertSameQueries(
            [
                "
                DROP TEMPORARY TABLE IF EXISTS fk_integrity_violations_tmp;
                CREATE TEMPORARY TABLE fk_integrity_violations_tmp (
                  fk_description VARCHAR(255) PRIMARY KEY,
                  violations INT(11) NOT NULL
                ) ENGINE MEMORY;
                ",
                "
                INSERT INTO fk_integrity_violations_tmp
                SELECT
                  '[[DB_0.TBL_0.COL_0 -> DB_1.TBL_1.COL_1 (DUMMY_CONSTRAINT_NAME_A)]]',
                  COUNT(DISTINCT t0.`COL_0`)
                FROM
                  `DB_0`.`TBL_0` t0
                  LEFT JOIN `DB_1`.`TBL_1` t1 ON t1.`COL_1` = t0.`COL_0`
                WHERE
                  t0.`COL_0` IS NOT NULL
                  AND t1.`COL_1` IS NULL;
                ",
                "
                INSERT INTO fk_integrity_violations_tmp
                SELECT
                  '[[DB_0.TBL_0.COL_2 -> DB_1.TBL_1.COL_1 (DUMMY_CONSTRAINT_NAME_B)]]',
                  COUNT(DISTINCT t0.`COL_2`)
                FROM
                  `DB_0`.`TBL_0` t0
                  LEFT JOIN `DB_1`.`TBL_1` t1 ON t1.`COL_1` = t0.`COL_2`
                WHERE
                  t0.`COL_2` IS NOT NULL
                  AND t1.`COL_1` IS NULL;
                ",
            ],
            $result['queries']
        );
    }

    public function testThatCompoundForeignKeysThrowAnError()
    {
        $this->expectConstraintsQuery(
            [
                [
                    'TABLE_SCHEMA' => 'DB_0',
                    'TABLE_NAME' => 'TBL_0',
                    'COLUMN_NAME' => 'COL_0',
                    'REFERENCED_TABLE_SCHEMA' => 'DB_1',
                    'REFERENCED_TABLE_NAME' => 'TBL_1',
                    'REFERENCED_COLUMN_NAME' => 'COL_1',
                    'CONSTRAINT_NAME' => 'DUMMY_CONSTRAINT_NAME_A'
                ],
                [
                    'TABLE_SCHEMA' => 'DB_0',
                    'TABLE_NAME' => 'TBL_0',
                    'COLUMN_NAME' => 'COL_2',
                    'REFERENCED_TABLE_SCHEMA' => 'DB_1',
                    'REFERENCED_TABLE_NAME' => 'TBL_1',
                    'REFERENCED_COLUMN_NAME' => 'COL_3',
                    'CONSTRAINT_NAME' => 'DUMMY_CONSTRAINT_NAME_A'
                ],
            ]
        );
        $this->expectException(RuntimeException::class);
        $this->sut->handleQuery(GenerateCheckFkIntegritySqlCmd::create([]));
    }

    /**
     * @dataProvider provideOddCharactersForConstraintsQuery
     *
     * @param $constraints
     */
    public function testThatOddCharactersCauseExceptions($constraints)
    {
        $this->expectConstraintsQuery($constraints);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/BAD`CHAR/');
        $this->sut->handleQuery(GenerateCheckFkIntegritySqlCmd::create([]));
    }

    public function provideOddCharactersForConstraintsQuery()
    {
        $identifierKeys = [
            'TABLE_SCHEMA',
            'TABLE_NAME',
            'COLUMN_NAME',
            'REFERENCED_TABLE_SCHEMA',
            'REFERENCED_TABLE_NAME',
            'REFERENCED_COLUMN_NAME',
        ];
        $constraint = [
            'TABLE_SCHEMA' => 'DB_0',
            'TABLE_NAME' => 'TBL_0',
            'COLUMN_NAME' => 'COL_0',
            'REFERENCED_TABLE_SCHEMA' => 'DB_1',
            'REFERENCED_TABLE_NAME' => 'TBL_1',
            'REFERENCED_COLUMN_NAME' => 'COL_0',
            'CONSTRAINT_NAME' => 'DUMMY_CONSTRAINT_NAME_A'
        ];
        foreach ($identifierKeys as $identifierKey) {
            yield [[array_merge($constraint, [$identifierKey => 'BAD`CHAR'])]];
        }
    }

    private function assertSameQueries($expectedQueries, $actualQueries)
    {
        $this->assertSame(count($expectedQueries), count($actualQueries), "Incorrect number of queries");
        foreach (array_map(null, $expectedQueries, $actualQueries) as [$expectedQuery, $actualQuery]) {
            $this->assertSameQuery($expectedQuery, $actualQuery);
        }
    }

    private function assertSameQuery($expectedQuery, $actualQuery)
    {
        $this->assertSame(
            $this->normalize($expectedQuery),
            $this->normalize($actualQuery),
            sprintf("expected:\n%s\nbut got: \n%s\n", $expectedQuery, $actualQuery)
        );
    }

    private function normalize($query)
    {
        return trim(preg_replace('/(^ *| *$| *\n *)/', ' ', $query));
    }

    /**
     * @param $constraints
     */
    protected function expectConstraintsQuery($constraints)
    {
        $mockStatement = m::mock(PDOStatement::class);
        $this->mockPdo->shouldReceive('prepare')
            ->with(
                "
            SELECT
              `TABLE_SCHEMA`,
              `TABLE_NAME`,
              `COLUMN_NAME`,
              `CONSTRAINT_NAME`,
              `REFERENCED_TABLE_SCHEMA`,
              `REFERENCED_TABLE_NAME`,
              `REFERENCED_COLUMN_NAME`
            FROM
              information_schema.KEY_COLUMN_USAGE
            WHERE
              (`CONSTRAINT_SCHEMA` LIKE :schema
               OR TABLE_SCHEMA LIKE :schema
               OR REFERENCED_TABLE_SCHEMA LIKE :schema
              )
              AND `REFERENCED_TABLE_SCHEMA` IS NOT NULL
            "
            )
            ->andReturn($mockStatement);

        $mockStatement
            ->shouldReceive('execute')
            ->with(['schema' => 'DUMMY-DB-NAME'])
            ->ordered();

        $mockStatement
            ->shouldReceive('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->andReturn(
                $constraints
            )
            ->ordered();

        $mockStatement
            ->shouldReceive('closeCursor')
            ->once()
            ->ordered();
    }
}
