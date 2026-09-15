<?php

declare(strict_types=1);

/**
 * WithPersonContactDetails Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Dvsa\Olcs\Api\Domain\QueryPartial\With;
use Dvsa\Olcs\Api\Domain\QueryPartial\WithPersonContactDetails;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryPartial\QueryPartialInterface;

/**
 * WithPersonContactDetails Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class WithPersonContactDetailsTest extends QueryPartialTestCase
{
    public function setUp(): void
    {
        // Cannot mock With as it is Final
        $with = new With();
        $this->sut = new WithPersonContactDetails($with);

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
            'SELECT a, c, p, a, ct, pc FROM foo a LEFT JOIN a.contactDetails c LEFT JOIN c.person p ' .
                'LEFT JOIN c.address a LEFT JOIN c.contactType ct LEFT JOIN c.phoneContacts pc',
            []
        ];
        yield [
            'SELECT a, c, p, a, ct, pc FROM foo a LEFT JOIN a.PROP c LEFT JOIN c.person p ' .
                'LEFT JOIN c.address a LEFT JOIN c.contactType ct LEFT JOIN c.phoneContacts pc',
            ['PROP']
        ];
        yield [
            'SELECT a, c, p, a, ct, pc FROM foo a LEFT JOIN ENTITY.PROP c LEFT JOIN c.person p ' .
                'LEFT JOIN c.address a LEFT JOIN c.contactType ct LEFT JOIN c.phoneContacts pc',
            ['ENTITY.PROP']
        ];
    }
}
