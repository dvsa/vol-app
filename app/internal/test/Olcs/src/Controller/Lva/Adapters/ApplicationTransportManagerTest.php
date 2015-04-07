<?php

/**
 * Application Transport Manager Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\ApplicationTransportManagerAdapter;

/**
 * Application Transport Manager Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ApplicationTransportManagerTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sut = new ApplicationTransportManagerAdapter();
        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testGetTable()
    {
        $mockTable = m::mock('StdClass');
        $mockTable->shouldReceive('getColumn')->once()->with('name')->andReturn([]);
        $mockTable->shouldReceive('setColumn')->once()->with('name', ['formatter' => 'TransportManagerNameInternal']);

        $settings = [
            'crud' => [
                'actions' => [
                    'add' => 'foo' ,
                    'edit' => 'bar',
                ]
            ]
        ];
        $mockTable->shouldReceive('getSettings')->once()->andReturn($settings);
        $mockTable->shouldReceive('setSettings')->once()->with(['crud' => ['actions' => []]]);

        $mockTableService = m::mock('StdClass');
        $mockTableService->shouldReceive('prepareTable')
            ->once()
            ->andReturn($mockTable);
        $this->sm->setService('Table', $mockTableService);

        $table = $this->sut->getTable();
        $this->assertEquals($mockTable, $table);
    }
}
