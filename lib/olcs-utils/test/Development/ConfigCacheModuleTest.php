<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Development;

use Dvsa\Olcs\Utils\Development\ConfigCacheKey;
use FilesystemIterator;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerInterface;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ConfigCacheModuleTest extends TestCase
{
    private string $appRoot;

    protected function setUp(): void
    {
        $this->appRoot = realpath(sys_get_temp_dir()) . '/config-cache-module-' . bin2hex(random_bytes(6));

        mkdir($this->appRoot . '/data/cache', 0777, true);
        mkdir($this->appRoot . '/config', 0777, true);
        mkdir($this->appRoot . '/vendor/olcs/olcs-common/Common/src', 0777, true);
        file_put_contents($this->appRoot . '/config/application.config.php', '<?php return [];');
        file_put_contents($this->appRoot . '/vendor/olcs/olcs-common/Common/src/SectionConfig.php', '<?php return [];');
    }

    protected function tearDown(): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->appRoot, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($this->appRoot);
    }

    public function testMergingTheConfigurationRecordsTheFilesItLoadedAndNamesTheCacheAfterTheNewKey(): void
    {
        $cacheKey = ConfigCacheKey::forApplication($this->appRoot, PHP_INT_MAX, 'container-1');
        $keyBeforeMerging = $cacheKey->key();
        $events = new EventManager();
        $cacheKey->module()->init($this->moduleManager($events));
        $options = $this->listenerOptions();

        // A class the configuration uses, loaded while it's merged
        require $this->appRoot . '/vendor/olcs/olcs-common/Common/src/SectionConfig.php';
        $events->triggerEvent(new Event('mergeConfig', null, ['configListener' => $this->configListener($options)]));

        $keyAfterMerging = $cacheKey->key();
        $this->assertNotSame($keyBeforeMerging, $keyAfterMerging, 'The key now covers the loaded class');
        $this->assertSame($keyAfterMerging, $options->configCacheKey);
        $this->assertSame($keyAfterMerging, $options->moduleMapCacheKey);

        touch($this->appRoot . '/vendor/olcs/olcs-common/Common/src/SectionConfig.php', time() + 10);
        $this->assertNotSame($keyAfterMerging, $cacheKey->key(), 'Editing the loaded class changes the key');
    }

    public function testOtherEventsAreIgnored(): void
    {
        $cacheKey = ConfigCacheKey::forApplication($this->appRoot, PHP_INT_MAX, 'container-1');
        $events = new EventManager();
        $cacheKey->module()->init($this->moduleManager($events));
        $options = $this->listenerOptions();

        $events->triggerEvent(new Event('loadModules', null, ['configListener' => $this->configListener($options)]));

        $this->assertNull($options->configCacheKey);
    }

    private function moduleManager(EventManagerInterface $events): EventManagerAwareInterface
    {
        return new class ($events) implements EventManagerAwareInterface {
            public function __construct(private EventManagerInterface $events)
            {
            }

            public function setEventManager(EventManagerInterface $eventManager): void
            {
                $this->events = $eventManager;
            }

            public function getEventManager(): EventManagerInterface
            {
                return $this->events;
            }
        };
    }

    /**
     * Stands in for Laminas\ModuleManager\Listener\ListenerOptions
     */
    private function listenerOptions(): object
    {
        return new class {
            public ?string $configCacheKey = null;
            public ?string $moduleMapCacheKey = null;

            public function setConfigCacheKey(string $key): self
            {
                $this->configCacheKey = $key;

                return $this;
            }

            public function setModuleMapCacheKey(string $key): self
            {
                $this->moduleMapCacheKey = $key;

                return $this;
            }
        };
    }

    /**
     * Stands in for Laminas\ModuleManager\Listener\ConfigListener
     */
    private function configListener(object $options): object
    {
        return new class ($options) {
            public function __construct(private readonly object $options)
            {
            }

            public function getOptions(): object
            {
                return $this->options;
            }
        };
    }
}
