<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Development;

use Dvsa\Olcs\Utils\Development\ConfigCacheKey;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ConfigCacheKeyTest extends TestCase
{
    private const int MTIME = 1_700_000_000;

    private string $appRoot;

    protected function setUp(): void
    {
        $this->appRoot = sys_get_temp_dir() . '/config-cache-key-' . bin2hex(random_bytes(6));

        $this->writeFile('data/cache/.gitignore', '*');
        $this->writeFile('config/application.config.php');
        $this->writeFile('config/autoload/global.php');
        $this->writeFile('module/Olcs/config/module.config.php');
        $this->writeFile('module/Olcs/config/routes/licence.php');
        $this->writeFile('module/Olcs/src/Module.php');
        $this->writeFile('vendor/olcs/olcs-transfer/config/backend-routes/letter.php');
        $this->writeFile('vendor/olcs/olcs-common/Common/config/module.config.php');
        $this->writeFile('vendor/composer/installed.php');
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

    public function testFilesOtherThanPhpInAConfigDirectoryAreIgnored(): void
    {
        $key = $this->key();

        $this->writeFile('module/Olcs/config/language/cy_GB.po', 'msgid ""');

        $this->assertSame($key, $this->key(rescanAfterSeconds: 0));
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

    public function testAnUnreadableManifestIsRebuilt(): void
    {
        $key = $this->key();

        $this->writeFile('data/cache/development-config-sources.json', '{"key": 1');

        $this->assertSame($key, $this->key());
        $manifest = json_decode(
            (string) file_get_contents($this->appRoot . '/data/cache/development-config-sources.json'),
            true
        );
        $this->assertIsArray($manifest);
        $this->assertSame($key, $manifest['key']);
    }

    /**
     * By default a scan is never due, so only a change to an already-known file can change the key
     */
    private function key(int $rescanAfterSeconds = PHP_INT_MAX): string
    {
        return ConfigCacheKey::forApplication($this->appRoot, $rescanAfterSeconds)->key();
    }

    private function writeFile(string $relativePath, string $contents = '<?php return [];', int $mtime = self::MTIME): void
    {
        $path = $this->appRoot . '/' . $relativePath;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, $contents);
        touch($path, $mtime);
    }
}
