<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\Role;
use Dvsa\Olcs\Transfer\Query\User\RoleList as Qry;
use Mockery as m;

/**
 * Class RoleService
 * Provides list options for user types
 *
 * @package Olcs\Service
 */
final class RoleTest extends AbstractDataServiceTestCase
{
    /** @var Role */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->sut = new Role($this->abstractDataServiceServices);
    }

    /**
     * @param $input
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFetchListOptions')]
    public function testFetchListOptions($input, $expected): void
    {
        $this->sut->setData('Role', $input);

        $this->assertEquals($expected, $this->sut->fetchListOptions(''));
    }

    /**
     * @return \Iterator<(int | string), array<array<mixed>>>
     *
     * @psalm-return list{list{array, array}, list{array<never, never>, array<never, never>}}
     */
    public static function provideFetchListOptions(): \Iterator
    {
        yield [self::getSingleSource(), self::getSingleExpected()];
        yield [[], []];
    }

    /**
     * @return array
     */
    protected static function getSingleExpected()
    {
        return [
            'role1' => 'role1desc',
            'role2' => 'role2desc'
        ];
    }

    /**
     * @return array
     */
    protected static function getSingleSource()
    {
        return [
            ['id' => 1, 'role' => 'role1', 'description' => 'role1desc'],
            ['id' => 2, 'role' => 'role2', 'description' => 'role2desc']
        ];
    }

    public function testFetchListData(): void
    {
        $results = ['results' => 'results'];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->twice()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($results['results'], $this->sut->fetchListData());
    }

    public function testFetchListDataWithException(): void
    {
        $this->expectException(DataServiceException::class);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->fetchListData();
    }
}
