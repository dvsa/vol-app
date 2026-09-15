<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\ApplicationPathGroup;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ApplicationPathGroupList as Qry;
use Mockery as m;

/**
 * Class Application Path Group Test
 * @package CommonTest\Service
 */
final class ApplicationPathGroupTest extends AbstractDataServiceTestCase
{
    /** @var ApplicationPathGroup */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->sut = new ApplicationPathGroup($this->abstractDataServiceServices);
    }

    public function testFormatData(): void
    {
        $source = self::getSingleSource();
        $expected = self::getSingleExpected();

        $this->assertEquals($expected, $this->sut->formatData($source));
    }

    /**
     * @param $input
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFetchListOptions')]
    public function testFetchListOptions($input, $expected): void
    {
        $this->sut->setData('ApplicationPathGroup', $input);

        $this->assertEquals($expected, $this->sut->fetchListOptions(''));
    }

    /**
     * @return \Iterator<(int | string), array<(array<mixed> | false)>>
     *
     * @psalm-return list{list{array, array}, list{false, array<never, never>}}
     */
    public static function provideFetchListOptions(): \Iterator
    {
        yield [self::getSingleSource(), self::getSingleExpected()];
        yield [false, []];
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

    /**
     * @return array
     */
    protected static function getSingleExpected()
    {
        return [
            '1' => 'APG1',
            '2' => 'APG2',
            '3' => 'APG3',
        ];
    }

    /**
     * @return array
     */
    protected static function getSingleSource()
    {
        return [
            ['id' => '1', 'name' => 'APG1'],
            ['id' => '2', 'name' => 'APG2'],
            ['id' => '3', 'name' => 'APG3'],
        ];
    }
}
