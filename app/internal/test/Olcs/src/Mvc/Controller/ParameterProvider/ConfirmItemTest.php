<?php

namespace OlcsTest\Mvc\Controller\ParameterProvider;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Olcs\Mvc\Controller\ParameterProvider\ConfirmItem;
use Laminas\Mvc\Controller\Plugin\Params;

/**
 * Class ConfirmItemTest
 * @package OlcsTest\Mvc\Controller\ParameterProvider
 */
class ConfirmItemTest extends TestCase
{
    public function testProvideParameters()
    {
        $expected = [
            'case' => 21,
            'ids' => [75, 34]
        ];

        $mockParams = m::mock(Params::class);

        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn(21);
        $mockParams->shouldReceive('fromRoute')->with('application')->andReturn("75,34");

        $sut = new ConfirmItem(['case', 'ids' => 'application']);
        $sut->setParams($mockParams);
        $data = $sut->provideParameters();

        $this->assertEquals($expected, $data);
    }
}
