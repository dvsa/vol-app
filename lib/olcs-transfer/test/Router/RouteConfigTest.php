<?php

namespace Dvsa\OlcsTest\Transfer\Router;

use Dvsa\Olcs\Transfer\Router\RouteConfig;

/**
 * Route Config Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class RouteConfigTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSingleConfig()
    {
        $config = RouteConfig::getSingleConfig();

        $expected = [
            'type' => 'Segment',
            'options' => [
                'route' => ':id[/]',
                'defaults' => [
                    'id' => null
                ]
            ]
        ];

        $this->assertEquals($expected, $config);
    }

    public function testGetSingleWithChildrenConfig()
    {
        $config = RouteConfig::getSingleConfig(['foo' => 'bar']);

        $expected = [
            'type' => 'Segment',
            'options' => [
                'route' => ':id[/]',
                'defaults' => [
                    'id' => null
                ]
            ],
            'may_terminate' => false,
            'child_routes' =>  [
                'foo' => 'bar'
            ]
        ];

        $this->assertEquals($expected, $config);
    }

    public function testGetNamedSingleConfig()
    {
        $config = RouteConfig::getNamedSingleConfig('application');

        $expected = [
            'type' => 'Segment',
            'options' => [
                'route' => ':application[/]',
                'defaults' => [
                    'application' => null
                ]
            ]
        ];

        $this->assertEquals($expected, $config);
    }

    public function testGetNamedSingleWithChildrenConfig()
    {
        $config = RouteConfig::getNamedSingleConfig('application', ['foo' => 'bar']);

        $expected = [
            'type' => 'Segment',
            'options' => [
                'route' => ':application[/]',
                'defaults' => [
                    'application' => null
                ]
            ],
            'may_terminate' => false,
            'child_routes' => [
                'foo' => 'bar'
            ]
        ];

        $this->assertEquals($expected, $config);
    }
}
