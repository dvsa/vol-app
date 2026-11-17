<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Config;

use PHPUnit\Framework\TestCase;

/**
 * Runs config/development.config.php.dist, which nothing else does: each developer copies it to
 * development.config.php, which Git ignores.
 */
class DevelopmentConfigDistTest extends TestCase
{
    public function testTheDistFileBuildsTheDevelopmentConfiguration(): void
    {
        $configDirectory = dirname(__DIR__, 5) . '/config';

        $config = require $configDirectory . '/development.config.php.dist';

        // The API has no developer toolbar, and tests run on the command line, which doesn't cache the
        // configuration
        $this->assertSame([], $config['modules']);
        $this->assertFalse($config['module_listener_options']['config_cache_enabled']);
        $this->assertSame(
            [realpath($configDirectory) . '/autoload/{,*.}{global,local}-development.php'],
            $config['module_listener_options']['config_glob_paths']
        );
    }
}
