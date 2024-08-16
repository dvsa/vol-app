<?php

namespace OlcsTest\Mvc\Controller\ParameterProvider;

use Olcs\Mvc\Controller\ParameterProvider\GenericList;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Laminas\Mvc\Controller\Plugin\Params;

/**
 * Class GenericListTest
 *
 * @package OlcsTest\Mvc\Controller\ParameterProvider
 */
class GenericListTest extends TestCase
{
    /** @var  Params | m\MockInterface */
    private $mockParams;

    public function setUp(): void
    {
        $this->mockParams = m::mock(Params::class)->makePartial();
    }

    public function testProvideParameters()
    {
        $expected = [
            'page' => 7,
            'sort' => 'test',
            'order' => 'DESC',
            'limit' => 50,
            'id' => 75,
            'otherOption' => 'other',
            'startDate' => '2016-4-1',
            'endDate' => '2016-4-30',
        ];

        $this->mockParams
            ->shouldReceive('fromQuery')->with('page')->andReturn(7)
            ->shouldReceive('fromQuery')->with('sort')->andReturn('')
            ->shouldReceive('fromQuery')->with('order')->andReturn(null)
            ->shouldReceive('fromQuery')->with('limit')->andReturn(50)
            ->shouldReceive('fromQuery')->with()->andReturn(
                [
                    'emptyOption' => '',
                    'otherOption' => 'other',
                    'startDate' => ['day' => 1, 'month' => 4, 'year' => 2016],
                    'endDate' => ['day' => 30, 'month' => 4, 'year' => 2016],
                ]
            )
            ->shouldReceive('fromRoute')->with('case')->andReturn(null)
            ->shouldReceive('fromRoute')->with('application')->andReturn(75);

        $sut = new GenericList(['case', 'id' => 'application'], 'test');
        $sut->setParams($this->mockParams);

        $this->assertEquals($expected, $sut->provideParameters());
    }

    public function testDefaults()
    {
        $sut = new GenericList(['case', 'id' => 'application'], 'unit_defSort', 'unit_DefOrder');
        $sut->setDefaultLimit(9999);

        $this->mockParams
            ->shouldReceive('fromQuery')->andReturn([])
            ->shouldReceive('fromRoute')->andReturnNull();

        $sut->setParams($this->mockParams);

        static::assertEquals(
            [
                'page' => 1,
                'sort' => 'unit_defSort',
                'order' => 'unit_DefOrder',
                'limit' => 9999,
            ],
            $sut->provideParameters()
        );
    }
}
