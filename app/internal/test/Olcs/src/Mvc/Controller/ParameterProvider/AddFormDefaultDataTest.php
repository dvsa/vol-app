<?php

namespace OlcsTest\Mvc\Controller\ParameterProvider;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Laminas\Mvc\Controller\Plugin\Params;

/**
 * Class AddFormDefaultDataTest
 * @package OlcsTest\Mvc\Controller\ParameterProvider
 */
class AddFormDefaultDataTest extends TestCase
{
    public function testProvideParameters()
    {
        $expected = [
            'case' => 21,
            'static' => 'value'
        ];

        $mockParams = m::mock(Params::class);

        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn(21);

        $sut = new AddFormDefaultData(['case' => AddFormDefaultData::FROM_ROUTE, 'static' => 'value']);
        $sut->setParams($mockParams);
        $data = $sut->provideParameters();

        $this->assertEquals($expected, $data);
    }
}
