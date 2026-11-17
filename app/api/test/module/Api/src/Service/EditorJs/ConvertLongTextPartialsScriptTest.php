<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\EditorJs;

use PHPUnit\Framework\TestCase;

final class ConvertLongTextPartialsScriptTest extends TestCase
{
    private string $fixtureDirectory;

    #[\Override]
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
    }

    #[\Override]
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
        [$exitCode, $sql] = $this->runScript($this->fixtureDirectory);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('SET NAMES utf8mb4;', $sql);
        self::assertStringContainsString(
            "VALUES ('markup-application-declaration-goods-gb', 'New application – goods – GB', 'editorjs', 1, NOW());",
            $sql
        );
        self::assertStringContainsString("SELECT l.id, tk.id, '<p class=\"govuk-body\">Declaration wording</p>'", $sql);
        self::assertStringContainsString('"text":"Declaration wording"', $sql);
        self::assertStringContainsString(
            "WHERE tk.translation_key = 'markup-application-declaration-goods-gb' AND l.iso_code = 'en_GB' AND tk.format = 'editorjs';",
            $sql
        );
        self::assertStringNotContainsString('unrelated', $sql);
    }

    public function testConvertsTheRealPartials(): void
    {
        [$exitCode, $sql] = $this->runScript();

        self::assertSame(0, $exitCode, $sql);
        self::assertStringStartsWith('SET NAMES utf8mb4;', $sql);
        self::assertStringContainsString(
            "WHERE tk.translation_key = 'markup-continuation-full-declaration-goods-gb-standard' AND l.iso_code = 'cy_GB'",
            $sql
        );
        self::assertStringNotContainsString("'markup-review-text", $sql);
        self::assertStringNotContainsString("'markup-continuation-declaration-review'", $sql);
        self::assertStringContainsString("WHERE tk.translation_key = 'markup-tma-tm_declaration' AND l.iso_code = 'en_NI'", $sql);
        self::assertStringContainsString('Licence - administration', $sql);
        self::assertStringNotContainsString('<?php', $sql);
    }

    /** @return array{int, string} */
    private function runScript(string ...$directories): array
    {
        $script = dirname(__DIR__, 6) . '/data/db/convert-long-text-partials.php';
        $command = implode(' ', array_map('escapeshellarg', [PHP_BINARY, $script, ...$directories])) . ' 2>&1';

        exec($command, $output, $exitCode);

        return [$exitCode, implode("\n", $output)];
    }
}
