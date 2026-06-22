<?php

namespace Dvsa\OlcsTest\Utils\View\Helper;

use Dvsa\Olcs\Utils\Enum\AssetPathCacheBustingStrategy;
use Dvsa\Olcs\Utils\View\Helper\AssetPath;
use PHPUnit\Framework\TestCase;

class AssetPathTest extends TestCase
{
    public function testCacheBustingStrategyNone()
    {
        $helper = new AssetPath([
            'assets' => [
                'base_url' => '/assets/',
                'cache_busting_strategy' => AssetPathCacheBustingStrategy::None,
            ]
        ]);
        $result = $helper('style.css');
        $this->assertSame('/assets/style.css', $result);
    }

    public function testCacheBustingStrategyRelease()
    {
        $helper = new AssetPath([
            'assets' => [
                'base_url' => '/assets/',
                'cache_busting_strategy' => AssetPathCacheBustingStrategy::Release,
            ],
            'version' => [
                'release' => '1.2.3',
            ],
        ]);

        $result = $helper('style.css');
        $this->assertSame('/assets/style.css?v=c47f5b18b8a4', $result);
    }

    public function testCacheBustingStrategyReleaseThrowsWhenNoReleaseConfigured()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Release version is required for cache busting strategy "release".');
        new AssetPath([
            'assets' => [
                'base_url' => '/assets/',
                'cache_busting_strategy' => AssetPathCacheBustingStrategy::Release,
            ]
        ]);
    }

    public function testCacheBustingStrategyUnixTimestamp()
    {
        $helper = new AssetPath([
            'assets' => [
                'base_url' => '/assets/',
                'cache_busting_strategy' => AssetPathCacheBustingStrategy::UnixTimestamp,
            ]
        ]);
        $result = $helper('style.css');
        $this->assertMatchesRegularExpression('/\/assets\/style\.css\?v=\d+/', $result);
    }

    public function testInvalidCacheBustingStrategyThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        new AssetPath([
            'assets' => [
                'base_url' => '/assets/',
                'cache_busting_strategy' => 'invalid_strategy',
            ]
        ]);
    }

    public function testAssetPathWithNoPath()
    {
        $helper = new AssetPath([
            'assets' => [
                'base_url' => '/assets/',
                'cache_busting_strategy' => AssetPathCacheBustingStrategy::None,
            ]
        ]);
        $result = $helper();
        $this->assertSame('/assets', $result);
    }

    public function testAssetPathWithEmptyPath()
    {
        $helper = new AssetPath([
            'assets' => [
                'base_url' => '/assets/',
                'cache_busting_strategy' => AssetPathCacheBustingStrategy::None,
            ]
        ]);
        $result = $helper('');
        $this->assertSame('/assets', $result);
    }

    public function testAssetPathWithOnDemandCacheBustingStrategy()
    {
        $helper = new AssetPath([
            'assets' => [
                'base_url' => '/assets/',
                'cache_busting_strategy' => AssetPathCacheBustingStrategy::UnixTimestamp,
            ]
        ]);
        $result = $helper('script.js', AssetPathCacheBustingStrategy::None);
        $this->assertSame('/assets/script.js', $result);
    }

    public function testAssetPathWithOnDemandCacheBustingStrategyWithEmptyPath()
    {
        $helper = new AssetPath([
            'assets' => [
                'base_url' => '/assets/',
                'cache_busting_strategy' => AssetPathCacheBustingStrategy::UnixTimestamp,
            ]
        ]);
        $result = $helper('', AssetPathCacheBustingStrategy::None);
        $this->assertSame('/assets', $result);
    }

    public function testAssetPathWithOnDemandCacheBustingStrategySetAsReleaseVerifiesVersionRelease()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Release version is required for cache busting strategy "release".');

        $helper = new AssetPath([
            'assets' => [
                'base_url' => '/assets/',
                'cache_busting_strategy' => AssetPathCacheBustingStrategy::None,
            ]
        ]);
        $helper('script.js', AssetPathCacheBustingStrategy::Release);
    }
}
