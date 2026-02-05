<?php

declare(strict_types=1);

namespace OlcsTest\TestHelpers;

use Mockery as m;

class ControllerPluginManagerHelper
{
    /**
     * @param $class
     *
     * @return m\MockInterface
     *
     * @psalm-param 'Params'|'Url'|'handleQuery' $class
     */
    public function getMockPlugin(string $class): m\MockInterface
    {
        if (!str_contains($class, '\\')) {
            $class = 'Laminas\Mvc\Controller\Plugin\\' . $class;
        }

        $mockPlugin = m::mock($class);
        $mockPlugin->shouldReceive('__invoke')->andReturnSelf();
        return $mockPlugin;
    }

    /**
     * @param string[] $plugins
     *
     * @return m\MockInterface|\Laminas\Mvc\Controller\PluginManager
     *
     * @psalm-param array{handleQuery: 'handleQuery', url: 'Url', params: 'Params'} $plugins
     */
    public function getMockPluginManager(array $plugins): m\MockInterface
    {
        $mockPluginManager = m::mock(\Laminas\Mvc\Controller\PluginManager::class);
        $mockPluginManager->shouldReceive('setController');

        foreach ($plugins as $name => $class) {
            $mockPlugin = $this->getMockPlugin($class);
            $mockPluginManager->shouldReceive('get')->with($name, '')->andReturn($mockPlugin);
            $mockPluginManager->shouldReceive('get')->with($name)->andReturn($mockPlugin);
        }

        return $mockPluginManager;
    }
}
