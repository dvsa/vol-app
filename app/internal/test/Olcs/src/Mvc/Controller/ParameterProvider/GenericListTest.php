<?php


namespace OlcsTest\Mvc\Controller\ParameterProvider;

use Olcs\Mvc\Controller\ParameterProvider\GenericList;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Zend\Mvc\Controller\Plugin\Params;

/**
 * Class GenericListTest
 * @package OlcsTest\Mvc\Controller\ParameterProvider
 */
class GenericListTest extends TestCase
{
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

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('fromQuery')->with('page')->andReturn(7);
        $mockParams->shouldReceive('fromQuery')->with('sort')->andReturn('');
        $mockParams->shouldReceive('fromQuery')->with('order')->andReturn(null);
        $mockParams->shouldReceive('fromQuery')->with('limit')->andReturn(50);

        $mockParams->shouldReceive('fromQuery')->with()->andReturn(
            [
                'emptyOption' => '',
                'otherOption' => 'other',
                'startDate' => ['day' => 1, 'month' => 4, 'year' => 2016],
                'endDate' => ['day' => 30, 'month' => 4, 'year' => 2016],
            ]
        );

        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn(null);
        $mockParams->shouldReceive('fromRoute')->with('application')->andReturn(75);

        $sut = new GenericList(['case', 'id' => 'application'], 'test');
        $sut->setParams($mockParams);
        $data = $sut->provideParameters();

        $this->assertEquals($expected, $data);
    }
}
