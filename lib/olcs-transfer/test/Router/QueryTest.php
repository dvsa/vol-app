<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Router;

use Dvsa\Olcs\Transfer\Router\Query;
use Laminas\Http\Request;
use Laminas\Uri\Http as HttpUri;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class QueryTest extends MockeryTestCase
{
    public function testAssembleNoParams(): void
    {
        $uri = m::mock(HttpUri::class);
        $uri->expects('setQuery')->never();
        $options = ['uri' => $uri];

        $sut = new Query(Request::METHOD_GET, []);
        $this->assertEquals('', $sut->assemble([], $options));
    }

    public function testAssembleWithParams(): void
    {
        $defaults = ['param1' => 'value1'];
        $params = ['param2' => 'value2'];
        $mergedParams = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        $uri = m::mock(HttpUri::class);
        $uri->expects('setQuery')->with($mergedParams);
        $options = ['uri' => $uri];

        $sut = new Query(Request::METHOD_GET, $defaults);
        $this->assertEquals('', $sut->assemble($params, $options));
    }
}
