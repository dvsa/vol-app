<?php

namespace Dvsa\OlcsTest\Transfer\Util;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Transfer\Util\ChildRoutesGenerator;

class ChildRoutesGeneratorTest extends MockeryTestCase
{
    public function testGetUpdatedRoutes()
    {
        $stubDir = __DIR__ . '/child-routes-stub';
        $routes = [
            'api' => [
                'child_routes' => [
                    'test' => [
                        'child_routes' => [
                            'single' => []
                        ]
                    ]
                ]
            ]
        ];

        $expected = [
            'api' => [
                'child_routes' => [
                    'test' => [
                        'child_routes' => [
                            'single' => [
                                'child_routes' => include $stubDir . '/test/single/test_route.php'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $childRootGenerator = new ChildRoutesGenerator($routes, $stubDir);
        $updatedRoutes = $childRootGenerator->getUpdatedRoutes();
        $this->assertSame($expected, $updatedRoutes);
    }
}
