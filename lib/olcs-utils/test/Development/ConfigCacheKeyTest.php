<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Development;

use Closure;
use Dvsa\Olcs\Utils\Development\ConfigCacheKey;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ConfigCacheKeyTest extends TestCase
{
    private const int MTIME = 1_700_000_000;
    private const string SECTION_CONFIG = 'vendor/olcs/olcs-common/Common/src/Common/Service/Data/SectionConfig.php';

    private string $appRoot;

    protected function setUp(): void
    {
        // get_included_files() reports real paths, and the temporary directory can be a symlink
        $this->appRoot = realpath(sys_get_temp_dir()) . '/config-cache-key-' . bin2hex(random_bytes(6));

        $this->writeFile('data/cache/.gitignore', '*');
        $this->writeFile('config/application.config.php');
        $this->writeFile('config/autoload/global.php');
        $this->writeFile('config/version', '1.0.0');
        $this->writeFile('module/Olcs/config/module.config.php');
        $this->writeFile('module/Olcs/config/routes/licence.php');
        $this->writeFile('module/Olcs/config/language/partials/en_GB/markup.phtml', '<p>Snapshot</p>');
        $this->writeFile('module/Olcs/src/Module.php');
        $this->writeFile('vendor/olcs/olcs-transfer/config/backend-routes/letter.php');
        $this->writeFile('vendor/olcs/olcs-common/Common/config/module.config.php');
        $this->writeFile('vendor/olcs/olcs-common/Common/config/release.json', '{"version": "1.0.0"}');
        $this->writeFile(self::SECTION_CONFIG);
        $this->writeFile('vendor/laminas/laminas-form/src/Form.php');
        $this->writeFile('vendor/composer/installed.php');
    }

    protected function tearDown(): void
    {
        foreach ([$this->appRoot, $this->appRoot . '-lib'] as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $file) {
                $file->isDir() && !$file->isLink() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }

            rmdir($directory);
        }
    }

    public function testKeyStaysTheSameWhileNothingChanges(): void
    {
        $key = $this->key();

        $this->assertStringStartsWith('development-', $key);
        $this->assertSame($key, $this->key());
        $this->assertSame($key, $this->key(rescanAfterSeconds: 0));
    }

    public function testEditingAConfigFileChangesTheKeyStraightAway(): void
    {
        $key = $this->key();

        $this->writeFile('module/Olcs/config/routes/licence.php', mtime: self::MTIME + 1);

        $this->assertNotSame($key, $this->key());
    }

    public function testDeletingAConfigFileChangesTheKeyStraightAway(): void
    {
        $key = $this->key();

        unlink($this->appRoot . '/vendor/olcs/olcs-transfer/config/backend-routes/letter.php');

        $this->assertNotSame($key, $this->key());
    }

    public function testANewConfigFileIsFoundByTheNextScan(): void
    {
        $key = $this->key();

        $this->writeFile('vendor/olcs/olcs-transfer/config/backend-routes/fee.php');

        $this->assertSame($key, $this->key(), 'A new file is only found once a scan is due');
        $this->assertNotSame($key, $this->key(rescanAfterSeconds: 0));
    }

    public function testEditingAModuleClassChangesTheKey(): void
    {
        $key = $this->key();

        $this->writeFile('module/Olcs/src/Module.php', mtime: self::MTIME + 1);

        $this->assertNotSame($key, $this->key());
    }

    public function testComposerInstallChangesTheKey(): void
    {
        $key = $this->key();

        $this->writeFile('vendor/composer/installed.php', '<?php return ["changed"];');

        $this->assertNotSame($key, $this->key());
    }

    public function testEditingADataFileInAConfigDirectoryChangesTheKey(): void
    {
        $key = $this->key();

        $this->writeFile('vendor/olcs/olcs-common/Common/config/release.json', '{"version": "1.0.1"}', self::MTIME + 1);
        $keyAfterReleaseChange = $this->key();
        $this->assertNotSame($key, $keyAfterReleaseChange);

        $this->writeFile('config/version', '1.0.1', self::MTIME + 1);
        $this->assertNotSame($keyAfterReleaseChange, $this->key());
    }

    public function testTemplatesInAConfigDirectoryAreIgnored(): void
    {
        $key = $this->key();

        $this->writeFile('module/Olcs/config/language/partials/en_GB/markup.phtml', '<p>Changed</p>');
        $this->writeFile('module/Olcs/config/language/partials/en_GB/new.phtml', '<p>New</p>');

        $this->assertSame($key, $this->key(rescanAfterSeconds: 0));
    }

    public function testEditingAFileLoadedWhileMergingChangesTheKey(): void
    {
        $this->key();
        $recordedKey = $this->recordLoadedFiles([self::SECTION_CONFIG]);

        $this->assertSame($recordedKey, $this->key(), 'The next request works out the key the merge recorded');

        $this->writeFile(self::SECTION_CONFIG, mtime: self::MTIME + 1);

        $this->assertNotSame($recordedKey, $this->key());
    }

    public function testFilesLoadedWhileMergingAreStillWatchedAfterARescan(): void
    {
        $recordedKey = $this->recordLoadedFiles([self::SECTION_CONFIG]);

        $this->assertSame($recordedKey, $this->key(rescanAfterSeconds: 0));

        $this->writeFile(self::SECTION_CONFIG, mtime: self::MTIME + 1);

        $this->assertNotSame($recordedKey, $this->key(rescanAfterSeconds: 0));
    }

    public function testALibraryLinkedIntoVendorIsWatchedWhereItReallyIs(): void
    {
        // As in the repository: vendor/olcs/<library> links to lib/<library>, and the merge loads the
        // library's files from where the link points
        $library = $this->appRoot . '-lib/olcs-auth';
        mkdir($library . '/config', 0777, true);
        mkdir($library . '/src', 0777, true);
        mkdir($library . '/vendor/laminas', 0777, true);
        file_put_contents($library . '/config/module.config.php', '<?php return [];');
        file_put_contents($library . '/src/Thing.php', '<?php');
        file_put_contents($library . '/vendor/laminas/Other.php', '<?php');
        symlink($library, $this->appRoot . '/vendor/olcs/olcs-auth');

        $this->key(rescanAfterSeconds: 0);
        $recordedKey = $this->recordLoadedFiles(
            [],
            [$library . '/src/Thing.php', $library . '/vendor/laminas/Other.php']
        );

        touch($library . '/vendor/laminas/Other.php', self::MTIME + 1);
        $this->assertSame($recordedKey, $this->key(), "The library's own vendor directory isn't watched");

        touch($library . '/src/Thing.php', self::MTIME + 1);
        $keyAfterClassChange = $this->key();
        $this->assertNotSame($recordedKey, $keyAfterClassChange, 'A library class the merge loaded is watched');

        touch($library . '/config/module.config.php', self::MTIME + 1);
        $this->assertNotSame(
            $keyAfterClassChange,
            $this->key(),
            "The library's config, found through the link, is watched"
        );
    }

    public function testOnlyTheApplicationsOwnLoadedFilesAreRecorded(): void
    {
        $outside = $this->appRoot . '-elsewhere.php';
        file_put_contents($outside, '<?php');
        $this->writeFile('data/cache/module-config-cache.application.config.cache.php');

        $recordedKey = $this->recordLoadedFiles([
            'vendor/laminas/laminas-form/src/Form.php',
            'data/cache/module-config-cache.application.config.cache.php',
        ], [$outside]);

        $this->writeFile('vendor/laminas/laminas-form/src/Form.php', mtime: self::MTIME + 1);
        $this->writeFile('data/cache/module-config-cache.application.config.cache.php', mtime: self::MTIME + 1);
        touch($outside, self::MTIME + 1);
        unlink($outside);

        $this->assertSame($recordedKey, $this->key());
    }

    public function testAConfigFileChangedWhileTheConfigurationIsMergedChangesTheKeyAfterwards(): void
    {
        $cacheKey = ConfigCacheKey::forApplication($this->appRoot, PHP_INT_MAX, 'container-1');
        $cacheKey->key();

        // The merge reads the file, which then changes before the merge records what it loaded
        $this->writeFile('module/Olcs/config/module.config.php', mtime: self::MTIME + 1);
        $recordedKey = $cacheKey->recordLoadedFiles([$this->appRoot . '/module/Olcs/config/module.config.php']);

        $this->assertNotSame($recordedKey, $this->key(), 'The cache may hold the old contents, so it is rebuilt');
    }

    public function testAFileAnEarlierMergeLoadedChangedWhileTheConfigurationIsMergedChangesTheKeyAfterwards(): void
    {
        $this->recordLoadedFiles([self::SECTION_CONFIG]);

        $cacheKey = ConfigCacheKey::forApplication($this->appRoot, PHP_INT_MAX, 'container-1');
        $cacheKey->key();

        // The merge reads the file, which then changes before the merge records what it loaded
        $this->writeFile(self::SECTION_CONFIG, mtime: self::MTIME + 1);
        $recordedKey = $cacheKey->recordLoadedFiles([$this->appRoot . '/' . self::SECTION_CONFIG]);

        $this->assertNotSame($recordedKey, $this->key(), 'The cache may hold the old contents, so it is rebuilt');
    }

    public function testAFileAnEarlierMergeLoadedDeletedWhileTheConfigurationIsMergedChangesTheKeyAfterwards(): void
    {
        $this->recordLoadedFiles([self::SECTION_CONFIG]);

        $cacheKey = ConfigCacheKey::forApplication($this->appRoot, PHP_INT_MAX, 'container-1');
        $cacheKey->key();

        // The merge reads the file, which is then deleted before the merge records what it loaded
        unlink($this->appRoot . '/' . self::SECTION_CONFIG);
        $recordedKey = $cacheKey->recordLoadedFiles([$this->appRoot . '/' . self::SECTION_CONFIG]);

        $nextKey = $this->key();
        $this->assertNotSame($recordedKey, $nextKey, 'The cache holds what the deleted file built, so it is rebuilt');
        $this->assertSame($nextKey, $this->key(rescanAfterSeconds: 0), 'The key then stays the same');
    }

    public function testAFileDeletedBeforeTheConfigurationIsMergedIsNoLongerWatched(): void
    {
        $this->recordLoadedFiles([self::SECTION_CONFIG]);
        unlink($this->appRoot . '/' . self::SECTION_CONFIG);

        // The merge can't load the file, so it isn't among the files the merge records
        $cacheKey = ConfigCacheKey::forApplication($this->appRoot, PHP_INT_MAX, 'container-1');
        $cacheKey->key();
        $recordedKey = $cacheKey->recordLoadedFiles([]);

        $this->assertSame($recordedKey, $this->key(), 'The next request works out the key the merge recorded');
        $this->assertSame($recordedKey, $this->key(rescanAfterSeconds: 0));
    }

    public function testAFileAnEarlierMergeLoadedRewrittenAtTheSameSizeAndSecondWhileMergingChangesTheKey(): void
    {
        $now = self::MTIME + 100;
        $this->writeFile(self::SECTION_CONFIG, '<?php const A = 0;', $now);
        $this->recordLoadedFiles([self::SECTION_CONFIG], clock: $now);

        $cacheKey = ConfigCacheKey::forApplication($this->appRoot, PHP_INT_MAX, 'container-1', $this->clock($now));
        $cacheKey->key();

        // The merge reads the file, which is then saved again with a value of the same length before
        // the merge records what it loaded
        $this->writeFile(self::SECTION_CONFIG, '<?php const A = 1;', $now);
        $recordedKey = $cacheKey->recordLoadedFiles([$this->appRoot . '/' . self::SECTION_CONFIG]);

        $this->assertNotSame(
            $recordedKey,
            $this->key(clock: $now),
            'The cache may hold the old contents, so it is rebuilt'
        );
    }

    public function testAnotherRequestRescanningWhileTheConfigurationIsMergedDoesNotHideAChange(): void
    {
        $cacheKey = ConfigCacheKey::forApplication($this->appRoot, PHP_INT_MAX, 'container-1');
        $cacheKey->key();

        // The file changes after the merge has read it, and another request sees the change first
        $this->writeFile('module/Olcs/config/module.config.php', mtime: self::MTIME + 1);
        $this->key(rescanAfterSeconds: 0);
        $recordedKey = $cacheKey->recordLoadedFiles([$this->appRoot . '/module/Olcs/config/module.config.php']);

        $this->assertNotSame($recordedKey, $this->key());
    }

    public function testAnEditThatKeepsARecentlyChangedFilesSizeAndSecondChangesTheKey(): void
    {
        // Saved in the current second, then saved again with a value of the same length
        $now = self::MTIME + 100;
        $this->writeFile('module/Olcs/config/routes/licence.php', '<?php return ["enabled" => 0];', $now);
        $key = $this->key(clock: $now);

        $this->writeFile('module/Olcs/config/routes/licence.php', '<?php return ["enabled" => 1];', $now);

        $this->assertNotSame($key, $this->key(clock: $now));
    }

    public function testAnEditThatKeepsTheSizeAndSecondOfARecentlyChangedFileLoadedWhileMergingChangesTheKey(): void
    {
        $now = self::MTIME + 100;
        $this->writeFile(self::SECTION_CONFIG, '<?php const A = 0;', $now);
        $key = $this->recordLoadedFiles([self::SECTION_CONFIG], clock: $now);

        $this->writeFile(self::SECTION_CONFIG, '<?php const A = 1;', $now);

        $this->assertNotSame($key, $this->key(clock: $now));
    }

    public function testAnOlderMergeThatFinishesLastCannotReplaceANewerCacheAfterAnEditThatKeepsTheSizeAndSecond(): void
    {
        $now = self::MTIME + 100;
        $clock = static fn (): int => $now;
        $this->writeFile('module/Olcs/config/routes/licence.php', '<?php return ["enabled" => 0];', $now);

        // Request A works out the key and starts merging
        $requestA = ConfigCacheKey::forApplication($this->appRoot, PHP_INT_MAX, 'container-1', $clock);
        $requestA->key();

        // The file is saved again with a value of the same length, and request B starts merging
        $this->writeFile('module/Olcs/config/routes/licence.php', '<?php return ["enabled" => 1];', $now);
        $requestB = ConfigCacheKey::forApplication($this->appRoot, PHP_INT_MAX, 'container-1', $clock);
        $requestB->key();

        // Both record what they loaded, then B writes its cache, and A, which merged the old contents,
        // writes its cache last
        $keyA = $requestA->recordLoadedFiles([]);
        $keyB = $requestB->recordLoadedFiles([]);
        $this->writeFile("data/cache/module-config-cache.$keyB.php", 'new contents');
        $this->writeFile("data/cache/module-config-cache.$keyA.php", 'old contents');

        $nextKey = $this->key(clock: $now);

        $this->assertSame($keyB, $nextKey);
        $this->assertStringEqualsFile($this->appRoot . "/data/cache/module-config-cache.$nextKey.php", 'new contents');
    }

    public function testOnceARecentlyChangedFileIsOlderTheKeyChangesOnceMoreThenStaysTheSame(): void
    {
        $now = self::MTIME + 100;
        $this->writeFile('module/Olcs/config/routes/licence.php', mtime: $now);
        $key = $this->key(clock: $now);

        $this->assertSame(
            $key,
            $this->key(rescanAfterSeconds: 0, clock: $now),
            'The key is stable while the file is recent'
        );

        $olderKey = $this->key(rescanAfterSeconds: 0, clock: $now + 60);

        $this->assertNotSame($key, $olderKey, "The contents' hash leaves the key once the file is older");
        $this->assertSame($olderKey, $this->key(rescanAfterSeconds: 0, clock: $now + 120));
    }

    public function testOnlyTheContentsOfRecentlyChangedFilesAreChecked(): void
    {
        // An older file can't change without its modification time changing, short of a tool setting it
        // back, so reading it would be wasted
        $this->writeFile('module/Olcs/config/routes/licence.php', '<?php return ["enabled" => 0];');
        $key = $this->key(clock: self::MTIME + 100);
        $this->writeFile("data/cache/module-config-cache.$key.php");

        $this->writeFile('module/Olcs/config/routes/licence.php', '<?php return ["enabled" => 1];');

        $this->assertSame($key, $this->key(rescanAfterSeconds: 0, clock: self::MTIME + 100));
        $this->assertFileExists($this->appRoot . "/data/cache/module-config-cache.$key.php");
    }

    public function testReplacingTheContainerChangesTheKey(): void
    {
        $key = $this->key(container: 'container-1');

        $this->assertNotSame($key, $this->key(container: 'container-2'));
        $this->assertSame($key, $this->key(container: 'container-1'));
    }

    public function testCacheFilesFromAReplacedContainerAreDeleted(): void
    {
        $oldKey = $this->key(container: 'container-1');
        $this->writeFile("data/cache/module-config-cache.$oldKey.php");

        $this->key(container: 'container-2');

        $this->assertFileDoesNotExist($this->appRoot . "/data/cache/module-config-cache.$oldKey.php");
    }

    public function testCacheFilesForOtherKeysAreDeletedWhenTheKeyChanges(): void
    {
        $oldKey = $this->key();
        $this->writeFile("data/cache/module-config-cache.$oldKey.php");
        $this->writeFile("data/cache/module-classmap-cache.$oldKey.php");
        $this->writeFile('data/cache/module-config-cache.application.config.cache.php');

        $this->writeFile('config/autoload/global.php', mtime: self::MTIME + 1);
        $newKey = $this->key();
        $this->writeFile("data/cache/module-config-cache.$newKey.php");

        $this->assertFileDoesNotExist($this->appRoot . "/data/cache/module-config-cache.$oldKey.php");
        $this->assertFileDoesNotExist($this->appRoot . "/data/cache/module-classmap-cache.$oldKey.php");
        $this->assertFileExists($this->appRoot . "/data/cache/module-config-cache.$newKey.php");
        $this->assertFileExists($this->appRoot . '/data/cache/module-config-cache.application.config.cache.php');
        $this->assertFileExists($this->appRoot . '/data/cache/.gitignore');
    }

    public function testCacheFilesForTheCurrentKeyAreKeptWhenTheKeyIsUnchanged(): void
    {
        $key = $this->key();
        $this->writeFile("data/cache/module-config-cache.$key.php");

        $this->key(rescanAfterSeconds: 0);

        $this->assertFileExists($this->appRoot . "/data/cache/module-config-cache.$key.php");
    }

    public function testADirectoryThatCannotBeReadIsLeftOutOfTheScan(): void
    {
        $this->writeFile('module/Olcs/config/private/routes.php');
        $unreadable = $this->appRoot . '/module/Olcs/config/private';
        chmod($unreadable, 0000);

        try {
            // As root the directory can still be read, and the scan must work then too
            $key = $this->key(rescanAfterSeconds: 0);

            $this->writeFile('module/Olcs/config/routes/licence.php', mtime: self::MTIME + 1);

            $this->assertNotSame(
                $key,
                $this->key(rescanAfterSeconds: 0),
                'The files that can be read are still watched'
            );
        } finally {
            chmod($unreadable, 0755);
        }
    }

    public function testAConfigDirectoryThatCannotBeReadIsLeftOutOfTheScan(): void
    {
        $unreadable = $this->appRoot . '/vendor/olcs/olcs-transfer/config';
        chmod($unreadable, 0000);

        try {
            $key = $this->key(rescanAfterSeconds: 0);

            $this->writeFile('config/autoload/global.php', mtime: self::MTIME + 1);

            $this->assertNotSame(
                $key,
                $this->key(rescanAfterSeconds: 0),
                'The files that can be read are still watched'
            );
        } finally {
            chmod($unreadable, 0755);
        }
    }

    public function testTheCachesCanBeWrittenToACacheDirectoryThatCanBeWrittenTo(): void
    {
        $cacheKey = ConfigCacheKey::forApplication($this->appRoot, PHP_INT_MAX, 'container-1');

        $this->assertTrue($cacheKey->canWriteCachesFor($cacheKey->key()));
        $this->assertSame(
            ['.gitignore', 'development-config-sources.json'],
            array_values(array_diff(scandir($this->appRoot . '/data/cache') ?: [], ['.', '..'])),
            'Trying the directory leaves nothing behind'
        );
    }

    public function testTheCachesCannotBeWrittenWhenThereIsNoCacheDirectory(): void
    {
        unlink($this->appRoot . '/data/cache/.gitignore');
        rmdir($this->appRoot . '/data/cache');
        $cacheKey = ConfigCacheKey::forApplication($this->appRoot, PHP_INT_MAX, 'container-1');

        $this->assertFalse($cacheKey->canWriteCachesFor($cacheKey->key()));
    }

    public function testTheCachesCannotBeWrittenToACacheDirectoryThatCannotBeWrittenTo(): void
    {
        $cacheDirectory = $this->appRoot . '/data/cache';
        $cacheKey = ConfigCacheKey::forApplication($this->appRoot, PHP_INT_MAX, 'container-1');
        $key = $cacheKey->key();
        chmod($cacheDirectory, 0555);

        try {
            // Root can write to it whatever its permissions
            $this->assertSame(is_writable($cacheDirectory), $cacheKey->canWriteCachesFor($key));
        } finally {
            chmod($cacheDirectory, 0755);
        }
    }

    public function testADeletedCacheFileIsDroppedFromOpcache(): void
    {
        // OPcache is off for the command line, so a child process includes a cache file with it on,
        // then works out a key the file isn't named after, which deletes the file
        $cacheFile = $this->appRoot . '/data/cache/module-config-cache.development-0000000000000000.php';
        $this->writeFile('data/cache/module-config-cache.development-0000000000000000.php');

        $script = <<<'PHP'
            require $argv[1];
            $hasOpcache = function_exists('opcache_get_status') && is_array(opcache_get_status(false));
            $isCached = static fn (): ?bool => $hasOpcache ? opcache_is_script_cached($argv[3]) : null;
            include $argv[3];
            $before = $isCached();
            Dvsa\Olcs\Utils\Development\ConfigCacheKey::forApplication($argv[2], PHP_INT_MAX, 'container-1')->key();
            echo json_encode(['before' => $before, 'after' => $isCached(), 'exists' => file_exists($argv[3])]);
            PHP;

        $process = proc_open(
            [
                PHP_BINARY,
                '-d', 'opcache.enable=1',
                '-d', 'opcache.enable_cli=1',
                // Without this, a file that no longer exists counts as not cached whether or not
                // OPcache was told to drop it
                '-d', 'opcache.validate_timestamps=0',
                '-d', 'opcache.file_update_protection=0',
                '-r', $script,
                '--',
                dirname(__DIR__, 2) . '/vendor/autoload.php',
                $this->appRoot,
                $cacheFile,
            ],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        $this->assertIsResource($process);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        proc_close($process);

        $result = json_decode((string) $output, true);
        $this->assertIsArray($result, "The child process printed: $output $errors");
        $this->assertFalse($result['exists'], 'The cache file is deleted');

        // Where PHP has no OPcache there's nothing to drop the file from
        if ($result['before'] !== null) {
            $this->assertTrue($result['before'], 'OPcache held the cache file once it was included');
            $this->assertFalse($result['after'], 'OPcache still holds the deleted cache file');
        }
    }

    public function testAnUnreadableManifestIsRebuilt(): void
    {
        $key = $this->key();

        $this->writeFile('data/cache/development-config-sources.json', '{"sources": 1');

        $this->assertSame($key, $this->key());
        $manifest = json_decode(
            (string) file_get_contents($this->appRoot . '/data/cache/development-config-sources.json'),
            true
        );
        $this->assertIsArray($manifest);
        $this->assertIsString($manifest['sources']);
    }

    /**
     * By default a scan is never due, so only a change to an already-known file can change the key
     *
     * @param int|null $clock the current Unix time, by default the real one
     */
    private function key(
        int $rescanAfterSeconds = PHP_INT_MAX,
        string $container = 'container-1',
        ?int $clock = null,
    ): string {
        return ConfigCacheKey::forApplication($this->appRoot, $rescanAfterSeconds, $container, $this->clock($clock))
            ->key();
    }

    /**
     * @param string[] $relativePaths within the application
     * @param string[] $absolutePaths anywhere
     * @param int|null $clock         the current Unix time, by default the real one
     */
    private function recordLoadedFiles(array $relativePaths, array $absolutePaths = [], ?int $clock = null): string
    {
        $paths = array_map(fn (string $path): string => $this->appRoot . '/' . $path, $relativePaths);

        return ConfigCacheKey::forApplication($this->appRoot, PHP_INT_MAX, 'container-1', $this->clock($clock))
            ->recordLoadedFiles([...$paths, ...$absolutePaths]);
    }

    /**
     * @return (Closure(): int)|null
     */
    private function clock(?int $time): ?Closure
    {
        return $time === null ? null : static fn (): int => $time;
    }

    private function writeFile(
        string $relativePath,
        string $contents = '<?php return [];',
        int $mtime = self::MTIME,
    ): void {
        $path = $this->appRoot . '/' . $relativePath;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, $contents);
        touch($path, $mtime);
    }
}
