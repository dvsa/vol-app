<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Dvsa\Olcs\Api\Domain\QueryPartial\Paginate;

/**
 * @covers Dvsa\Olcs\Api\Domain\QueryPartial\Paginate
 */
class PaginateTest extends QueryPartialTestCase
{
    public function setUp(): void
    {
        $this->sut = new Paginate();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestModifyQuery')]
    public function testModifyQuery(mixed $page, mixed $limit, mixed $expect): void
    {
        foreach ($expect as $method => $value) {
            $this->qb->shouldReceive($method)->once()->with($value);
        }

        $this->sut->modifyQuery($this->qb, [$page, $limit]);
    }

    public static function dpTestModifyQuery(): array
    {
        return [
            [
                'page' => '0',
                'limit' => '123',
                'expect' => [
                    'setFirstResult' => 0,
                    'setMaxResults' => 123,
                ],
            ],
            [
                'page' => 'a',
                'limit' => 100,
                'expect' => [
                    'setFirstResult' => 0,
                    'setMaxResults' => 100,
                ],
            ],
            [
                'page' => 3,
                'limit' => 100,
                'expect' => [
                    'setFirstResult' => 200,
                    'setMaxResults' => 100,
                ],
            ],
            [
                'page' => null,
                'limit' => 33,
                'expect' => [
                    'setFirstResult' => 0,
                    'setMaxResults' => 33,
                ],
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestModifyQueryEmptyExpect')]
    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testModifyQueryEmptyExpect(mixed $page, mixed $limit, mixed $expect): void
    {
        $this->sut->modifyQuery($this->qb, [$page, $limit]);
    }

    public static function dpTestModifyQueryEmptyExpect(): array
    {
        return [
            [
                'page' => -1,
                'limit' => 'aaaa',
                'expect' => [
                    // 'setFirstResult' not call
                    //  setMaxResults not call,
                ],
            ],
            [
                'page' => null,
                'limit' => null,
                'expect' => [],
            ],
            [
                'page' => 1,
                'limit' => null,
                'expect' => [],
            ],
        ];
    }
}
