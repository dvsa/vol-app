<?php

namespace Dvsa\OlcsTest\Transfer\Router;

use Dvsa\Olcs\Transfer\Router\CommandConfig;

/**
 * Command Config Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CommandConfigTest extends \PHPUnit\Framework\TestCase
{
    public function testGetDeleteConfig()
    {
        $config = CommandConfig::getDeleteConfig('Foo\Bar');

        $expected = [
            'type' => \Laminas\Router\Http\Method::class,
            'options' => [
                'verb' => 'DELETE',
                'defaults' => [
                    'dto' => 'Foo\Bar'
                ]
            ]
        ];

        $this->assertEquals($expected, $config);
    }

    public function testGetPutConfig()
    {
        $config = CommandConfig::getPutConfig('Foo\Bar');

        $expected = [
            'type' => \Laminas\Router\Http\Method::class,
            'options' => [
                'verb' => 'PUT',
                'defaults' => [
                    'dto' => 'Foo\Bar'
                ]
            ]
        ];

        $this->assertEquals($expected, $config);
    }

    public function testGetPostConfig()
    {
        $config = CommandConfig::getPostConfig('Foo\Bar');

        $expected = [
            'type' => \Laminas\Router\Http\Method::class,
            'options' => [
                'verb' => 'POST',
                'defaults' => [
                    'dto' => 'Foo\Bar'
                ]
            ]
        ];

        $this->assertEquals($expected, $config);
    }
}
