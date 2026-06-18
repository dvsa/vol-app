<?php

namespace CommonTest\Helper;

use Common\Service\Helper\ResponseHelperService;
use Common\Service\Table\TableBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\Service\Helper\ResponseHelperService
 */
class ResponseHelperServiceTest extends MockeryTestCase
{
    /** @var  ResponseHelperService */
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new ResponseHelperService();
    }

    public function testTableToCsv(): void
    {
        $body = 'unit_body';

        $dummyColumns = [
            [
                'name' => 'action',
            ],
            [
                'name' => 'action2',
                'type' => 'ActionLinks',
            ],
        ];

        /** @var TableBuilder $table */
        $table = m::mock(TableBuilder::class)
            ->shouldReceive('getColumns')->once()->andReturn($dummyColumns)
            ->shouldReceive('removeColumn')->once()->with('action')
            ->shouldReceive('removeColumn')->once()->with('action2')
            ->shouldReceive('setContentType')->once()->with(TableBuilder::CONTENT_TYPE_CSV)
            ->shouldReceive('render')->once()->andReturn($body)
            ->getMock();

        $mockHeaders = m::mock()
            ->shouldReceive('addHeaderLine')->with('Content-Type', 'text/csv')->andReturnSelf()
            ->shouldReceive('addHeaderLine')
            ->once()
            ->with('Content-Disposition', 'attachment; filename="foo.csv"')
            ->andReturnSelf()
            ->shouldReceive('addHeaderLine')->with('Content-Length', 9)->andReturnSelf()
            ->getMock();

        /** @var \Laminas\Http\Response|m\MockInterface $response */
        $response = m::mock(\Laminas\Http\Response::class)
            ->shouldReceive('getHeaders')->once()->andReturn($mockHeaders)
            ->shouldReceive('setContent')->with($body)
            ->getMock();

        $result = $this->sut->tableToCsv($response, $table, 'foo');

        static::assertSame($response, $result);
    }
}
