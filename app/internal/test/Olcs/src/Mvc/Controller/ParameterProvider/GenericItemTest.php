<?php

namespace OlcsTest\Mvc\Controller\ParameterProvider;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Mockery as m;
use Laminas\Mvc\Controller\Plugin\Params;

/**
 * Class GenericItemTest
 * @package OlcsTest\Mvc\Controller\ParameterProvider
 */
class GenericItemTest extends TestCase
{
    public function testProvideParameters()
    {
        $expected = [
            'case' => 21,
            'id' => 75
        ];

        $mockParams = m::mock(Params::class);

        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn(21);
        $mockParams->shouldReceive('fromRoute')->with('application')->andReturn(75);

        $sut = new GenericItem(['case', 'id' => 'application']);
        $sut->setParams($mockParams);
        $data = $sut->provideParameters();

        $this->assertEquals($expected, $data);
    }
}
