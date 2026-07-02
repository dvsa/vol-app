<?php

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
class RoleTest extends AbstractDataServiceTestCase
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
     * @dataProvider provideFetchListOptions
     * @param $input
     * @param $expected
     */
    public function testFetchListOptions($input, $expected): void
    {
        $this->sut->setData('Role', $input);

        $this->assertEquals($expected, $this->sut->fetchListOptions(''));
    }

    /**
     * @return array[][]
     *
     * @psalm-return list{list{array, array}, list{array<never, never>, array<never, never>}}
     */
    public function provideFetchListOptions(): array
    {
        return [
            [$this->getSingleSource(), $this->getSingleExpected()],
            [[], []]
        ];
    }

    /**
     * @return array
     */
    protected function getSingleExpected()
    {
        return [
            'role1' => 'role1desc',
            'role2' => 'role2desc'
        ];
    }

    /**
     * @return array
     */
    protected function getSingleSource()
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
