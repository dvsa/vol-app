<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Dvsa\Olcs\Api\Domain\QueryPartial\WithUser;
use Dvsa\Olcs\Api\Domain\QueryPartial\With;
use Mockery as m;

/**
 * WithUserTest
 */
final class WithUserTest extends QueryPartialTestCase
{
    public function setUp(): void
    {
        // Cannot mock With as it is Final
        $with = new With();
        $this->sut = new WithUser($with);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testModifyQuery(mixed $expectedDql, mixed $arguments): void
    {
        $this->sut->modifyQuery($this->qb, $arguments);
        $this->assertSame(
            $expectedDql,
            $this->qb->getDQL()
        );
    }

    public static function dataProvider(): \Iterator
    {
        yield [
            'SELECT a, u, cd, p FROM foo a LEFT JOIN a.user u LEFT JOIN u.contactDetails cd ' .
                'LEFT JOIN cd.person p',
            []
        ];
        yield [
            'SELECT a, u, cd, p FROM foo a LEFT JOIN a.user u LEFT JOIN u.contactDetails cd ' .
                'LEFT JOIN cd.person p',
            ['ENTITY']
        ];
        yield [
            'SELECT a, u, cd, p FROM foo a LEFT JOIN ALIAS.user u LEFT JOIN u.contactDetails cd ' .
                'LEFT JOIN cd.person p',
            ['ENTITY', 'ALIAS']
        ];
    }
}
