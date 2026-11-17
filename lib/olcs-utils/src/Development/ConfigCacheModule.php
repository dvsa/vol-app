<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Utils\Development;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerAwareInterface;

/**
 * A Laminas module that records which of the application's files were loaded while the
 * configuration was merged, so the configuration cache key covers them, and names the cache that
 * the merge writes after the resulting key.
 *
 * Laminas only merges the configuration when there's no cache for it, so on requests that use the
 * cache this does nothing.
 */
final class ConfigCacheModule
{
    // ModuleEvent::EVENT_MERGE_CONFIG. olcs-utils doesn't depend on laminas-modulemanager (the
    // applications get it through laminas-mvc), so its classes aren't referenced here.
    private const string MERGE_CONFIG_EVENT = 'mergeConfig';

    // After every other listener has changed the merged configuration, and before Laminas writes the
    // cache
    private const int PRIORITY = -10000;

    public function __construct(private readonly ConfigCacheKey $cacheKey)
    {
    }

    /**
     * Called by Laminas with the module manager when the module is loaded
     */
    public function init(EventManagerAwareInterface $moduleManager): void
    {
        $moduleManager->getEventManager()->attach(self::MERGE_CONFIG_EVENT, $this->onMergeConfig(...), self::PRIORITY);
    }

    public function onMergeConfig(EventInterface $event): void
    {
        $key = $this->cacheKey->recordLoadedFiles(get_included_files());

        // Write the cache under the key the next request will work out, now that the key covers these
        // files
        $configListener = $event->getParam('configListener');
        $options = is_object($configListener) && method_exists($configListener, 'getOptions')
            ? $configListener->getOptions()
            : null;

        if (
            is_object($options)
            && method_exists($options, 'setConfigCacheKey')
            && method_exists($options, 'setModuleMapCacheKey')
        ) {
            $options->setConfigCacheKey($key);
            $options->setModuleMapCacheKey($key);
        }
    }
}
