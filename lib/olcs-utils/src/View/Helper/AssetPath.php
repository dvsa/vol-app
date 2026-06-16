<?php

namespace Dvsa\Olcs\Utils\View\Helper;

use Dvsa\Olcs\Utils\Enum\AssetPathCacheBustingStrategy;
use Laminas\View\Helper\AbstractHelper;

/**
 * AssetPath helper
 *
 * This helper generates a path for assets, allowing for a base path to be set.
 * It supports different cache busting strategies, including:
 * - None: No cache busting, just returns the asset path.
 * - Release: Appends a release version to the asset path.
 * - Timestamp: Appends the current Unix timestamp to the asset path.
 *
 * The configuration for this helper should include:
 * - `assets.base_url`: The base URL for assets.
 * - `assets.cache_busting_strategy`: The strategy to use for cache busting, which can be one of the following:
 *   - `none`: No cache busting.
 *   - `release`: Use the release version from the configuration.
 *   - `timestamp`: Use the current Unix timestamp.
 * If the `release` strategy is used, the configuration must also include:
 * - `version.release`: The hashed release version string to append to the asset path.
 */
class AssetPath extends AbstractHelper
{
    private string $assetBasePath;
    private AssetPathCacheBustingStrategy $cacheBustingStrategy;
    private ?string $release;

    public function __construct(array $config)
    {
        $this->parseConfig($config);
    }

    private function parseConfig(array $config = []): void
    {
        $this->assetBasePath = $config['assets']['base_url'] ?? '';
        $configuredCacheBustStrategy = $config['assets']['cache_busting_strategy'] ?? AssetPathCacheBustingStrategy::None;
        $this->cacheBustingStrategy = $this->normalizeCacheBustingStrategy($configuredCacheBustStrategy);
        $this->release = $config['version']['release'] ?? null;
        if ($this->cacheBustingStrategy === AssetPathCacheBustingStrategy::Release) {
            $this->verifyReleaseVersionIsSet();
        }
    }

    /**
     * Normalize the cache busting strategy to an enum instance
     *
     * @param string|AssetPathCacheBustingStrategy $strategy The cache busting strategy to normalize
     * @return AssetPathCacheBustingStrategy
     * @throws \InvalidArgumentException if the strategy is not valid
     */
    private function normalizeCacheBustingStrategy(string|AssetPathCacheBustingStrategy $strategy): AssetPathCacheBustingStrategy
    {
        if ($strategy instanceof AssetPathCacheBustingStrategy) {
            return $strategy;
        }
        $enum = AssetPathCacheBustingStrategy::tryFrom($strategy);
        if ($enum === null) {
            throw new \InvalidArgumentException("Invalid cache busting strategy: {$strategy}");
        }
        return $enum;
    }

    /**
     * Verify version.release is set when using Release cache busting strategy
     *
     * @param AssetPathCacheBustingStrategy|null $strategy The cache busting strategy to verify, if not provided the default will be used
     * @throws \InvalidArgumentException if the release version is not set when using the Release strategy
     */
    private function verifyReleaseVersionIsSet(AssetPathCacheBustingStrategy $strategy = null): void
    {
        if ($strategy === null) {
            $strategy = $this->cacheBustingStrategy;
        }

        if ($strategy === AssetPathCacheBustingStrategy::Release && empty($this->release)) {
            throw new \InvalidArgumentException('Release version is required for cache busting strategy "release".');
        }
    }

    /**
     * Render asset path with optional cache busting
     *
     * @param string $path The asset path to append to the base URL
     * @param string|AssetPathCacheBustingStrategy|null $onDemandCacheBustingStrategy The cache busting strategy to use, if not provided the default will be used
     * @return string
     */
    public function __invoke(string $path = '', string|AssetPathCacheBustingStrategy $onDemandCacheBustingStrategy = null): string
    {
        $strategy = $this->resolveCacheBustingStrategy($onDemandCacheBustingStrategy);

        $assetUrl = rtrim($this->assetBasePath, '/') . '/' . ltrim($path, '/');

        return match ($strategy) {
            AssetPathCacheBustingStrategy::None => rtrim($assetUrl, '/'),
            AssetPathCacheBustingStrategy::Release,
            AssetPathCacheBustingStrategy::UnixTimestamp => $this->appendCacheBustingQuery($assetUrl, $strategy),
        };
    }

    /**
     * Resolve the cache busting strategy, either from the provided parameter or the default
     *
     * @param string|AssetPathCacheBustingStrategy|null $strategy The cache busting strategy to resolve
     * @return AssetPathCacheBustingStrategy
     */
    private function resolveCacheBustingStrategy(string|AssetPathCacheBustingStrategy|null $strategy): AssetPathCacheBustingStrategy
    {
        $strategy = $strategy === null
            ? $this->cacheBustingStrategy
            : $this->normalizeCacheBustingStrategy($strategy);

        if ($strategy === AssetPathCacheBustingStrategy::Release) {
            $this->verifyReleaseVersionIsSet($strategy);
        }

        return $strategy;
    }

    /**
     * Append cache busting query to the asset URL
     *
     * @param string $assetUrl The asset URL to append the query to
     * @param AssetPathCacheBustingStrategy $cacheBustingStrategy The cache busting strategy to use
     * @return string The asset URL with the cache busting query appended
     */
    private function appendCacheBustingQuery(string $assetUrl, AssetPathCacheBustingStrategy $cacheBustingStrategy): string
    {
        $versionString = match ($cacheBustingStrategy) {
            AssetPathCacheBustingStrategy::UnixTimestamp => (string) time(),
            AssetPathCacheBustingStrategy::Release => substr(hash('sha256', $this->release), 0, 12),
            default => throw new \LogicException('Unhandled cache busting strategy'),
        };

        $query = http_build_query(['v' => $versionString]);
        $separator = (parse_url($assetUrl, PHP_URL_QUERY) === null) ? '?' : '&';

        return $assetUrl . $separator . $query;
    }
}
