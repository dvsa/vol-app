<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\EditorJs;

use PHPUnit\Framework\TestCase;

final class ConvertLongTextPartialsScriptTest extends TestCase
{
    private string $fixtureDirectory;

    protected function setUp(): void
    {
        $this->fixtureDirectory = sys_get_temp_dir() . '/long-text-partials-' . uniqid();
        mkdir($this->fixtureDirectory . '/en_GB', 0777, true);

        file_put_contents(
            $this->fixtureDirectory . '/en_GB/markup-application_undertakings_GV79.phtml',
            '<p>Declaration wording</p>%s'
        );
        file_put_contents(
            $this->fixtureDirectory . '/en_GB/markup-unrelated-page.phtml',
            '<p>Unrelated wording</p>'
        );
        file_put_contents(
            $this->fixtureDirectory . '/en_GB/markup-continuation-declaration-psv-standard.phtml',
            '<p>Unused continuation wording</p>'
        );
    }

    protected function tearDown(): void
    {
        foreach (glob($this->fixtureDirectory . '/en_GB/*') ?: [] as $file) {
            unlink($file);
        }

        rmdir($this->fixtureDirectory . '/en_GB');
        rmdir($this->fixtureDirectory);
    }

    public function testOnlyConvertsPartialsUsedByTheDeclarationPages(): void
    {
        $script = dirname(__DIR__, 6) . '/data/db/convert-long-text-partials.php';
        $command = sprintf(
            '%s %s %s',
            escapeshellarg(PHP_BINARY),
            escapeshellarg($script),
            escapeshellarg($this->fixtureDirectory)
        );

        exec($command, $output, $exitCode);
        $sql = implode("\n", $output);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString("'application-declaration-goods-gb'", $sql);
        self::assertStringContainsString('Declaration wording', $sql);
        self::assertStringNotContainsString("'unrelated-page'", $sql);
        self::assertStringNotContainsString("'continuation-declaration-psv-standard'", $sql);
        self::assertStringNotContainsString('ON DUPLICATE KEY UPDATE', $sql);
    }
}
