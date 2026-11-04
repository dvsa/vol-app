<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework;

use Dvsa\OlcsTest\Api\Framework\Support\ApiApplication;
use Dvsa\OlcsTest\Api\Framework\Support\Entrypoint;
use Dvsa\OlcsTest\Api\Framework\Support\FrameworkTestCase;
use Dvsa\OlcsTest\Api\Framework\Support\JsonSnapshot;
use Dvsa\OlcsTest\Api\Framework\Support\PortableValues;

/**
 * The configuration the api runs on, after every module's config and config/autoload/*.global.php are merged.
 *
 * Today laminas-modulemanager does the merging, driven by Laminas\Mvc\Application::init(): modules in
 * application.config.php order, then the autoload globs, then config-cloud-parameters resolving the
 * %placeholders% on the merge event. The replacement bootstrap (laminas-config-aggregator, or Mezzio's
 * config providers) merges differently unless set up to match: provider order, which keys it reads, and how
 * list values combine. This snapshot is where any difference shows, before it reaches an environment.
 *
 * Watch for keys that change meaning, not just value. Some keys are only read under laminas-mvc conventions
 * and some only under Mezzio's, so a key that does nothing today can start working silently. For example
 * module/Cli/config/module.config.php registers the doctrine-migrations DependencyFactory under
 * `dependencies`, Mezzio's name for the container config: the laminas-mvc ServiceManager reads only
 * `service_manager`, so that factory is not registered today (BootstrapTest shows the migrations commands
 * being built without it). Under a config-aggregator bootstrap that reads `dependencies`, it would be.
 *
 * One file per top-level key for the web entrypoint (config/container.php). For the command line
 * (config/container-cli.php) only what differs from the web, so its files read as "what the Cli module
 * changes": one file per top-level key that differs, holding just the entries that differ, nested as far as
 * both sides are maps; a list is compared whole, and an entry only the web has is "(absent)". Key order is
 * kept, not sorted: some config is order-sensitive (list priority, section order), and a reordering is
 * worth seeing.
 *
 * Values are as merged, with the stubbed '1' for every %placeholder% (no AWS) and no local config. Paths
 * and the temp directory are made portable (see PortableValues).
 *
 * `router` and `api_router` (2.2MB of route tree) are left out: RouteSnapshotTest pins what they do — the
 * URL each route assembles and the route each request matches — which is what has to survive.
 *
 * Regenerate with UPDATE_SNAPSHOTS=1 only for an intended change, and review the diff.
 */
final class MergedConfigTest extends FrameworkTestCase
{
    private const string SNAPSHOT_DIR = __DIR__ . '/snapshots/config';
    private const string UPDATE_COMMAND = 'UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --filter MergedConfigTest';
    private const array EXCLUDED_KEYS = ['router', 'api_router'];

    /**
     * Stands in for a key the command line does not have but the web does.
     */
    private const string ABSENT = '(absent)';

    public function testWebConfigMatchesSnapshot(): void
    {
        $this->assertKeysMatchSnapshots(Entrypoint::Web, self::portableConfig(Entrypoint::Web));
    }

    public function testCommandLineConfigDiffersFromTheWebOnlyAsBefore(): void
    {
        $differences = self::difference(self::portableConfig(Entrypoint::Web), self::portableConfig(Entrypoint::Cli));

        $this->assertNotEmpty($differences, 'The command line adds the Cli module, so its config must differ');
        $this->assertKeysMatchSnapshots(Entrypoint::Cli, $differences);
    }

    /**
     * The entries of $cli that are not the same in $web, recursing while both sides are maps.
     *
     * @param array<array-key, mixed> $web
     * @param array<array-key, mixed> $cli
     *
     * @return array<array-key, mixed>
     */
    private static function difference(array $web, array $cli): array
    {
        $differences = [];

        foreach (array_keys($cli + $web) as $key) {
            if (!array_key_exists($key, $cli)) {
                $differences[$key] = self::ABSENT;
            } elseif (!array_key_exists($key, $web)) {
                $differences[$key] = $cli[$key];
            } elseif ($web[$key] !== $cli[$key]) {
                $differences[$key] = self::isMap($web[$key]) && self::isMap($cli[$key])
                    ? self::difference($web[$key], $cli[$key])
                    : $cli[$key];
            }
        }

        return $differences;
    }

    /**
     * @psalm-assert-if-true array<array-key, mixed> $value
     */
    private static function isMap(mixed $value): bool
    {
        return is_array($value) && $value !== [] && !array_is_list($value);
    }

    /**
     * Compares every key before failing, so one run lists every key that changed.
     *
     * @param array<string, mixed> $config
     */
    private function assertKeysMatchSnapshots(Entrypoint $entrypoint, array $config): void
    {
        $files = [];
        $expected = '';
        $actual = '';
        $changed = [];

        foreach ($config as $key => $value) {
            $file = self::snapshotFile($entrypoint, (string) $key);
            $this->assertArrayNotHasKey($file, $files, sprintf('Config keys "%s" and "%s" share a snapshot file', $files[$file] ?? '', $key));
            $files[$file] = (string) $key;

            $encoded = JsonSnapshot::encode($value);

            if (JsonSnapshot::isUpdating()) {
                if (!is_dir(dirname($file))) {
                    mkdir(dirname($file), 0777, true);
                }

                file_put_contents($file, $encoded);
            }

            $stored = is_file($file)
                ? JsonSnapshot::encode(json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR))
                : "(no snapshot)\n";

            if ($stored !== $encoded) {
                $changed[] = (string) $key;
                $expected .= sprintf("=== %s ===\n%s", $key, $stored);
                $actual .= sprintf("=== %s ===\n%s", $key, $encoded);
            }
        }

        $this->assertSame(
            $expected,
            $actual,
            sprintf(
                'The %s config no longer matches its snapshot for: %s. If the change is intended, run `%s` and review the diff.',
                $entrypoint->value,
                implode(', ', $changed),
                self::UPDATE_COMMAND
            )
        );

        JsonSnapshot::assertNoOrphans(self::SNAPSHOT_DIR . '/' . $entrypoint->value, array_keys($files), self::UPDATE_COMMAND);
    }

    /**
     * @return array<string, mixed>
     */
    private static function portableConfig(Entrypoint $entrypoint): array
    {
        $config = ApiApplication::loadModules($entrypoint, false)->get('config');
        assert(is_array($config));

        $portable = PortableValues::of(array_diff_key($config, array_flip(self::EXCLUDED_KEYS)));
        assert(is_array($portable));

        return $portable;
    }

    private static function snapshotFile(Entrypoint $entrypoint, string $key): string
    {
        return sprintf(
            '%s/%s/%s.json',
            self::SNAPSHOT_DIR,
            $entrypoint->value,
            preg_replace('/[^A-Za-z0-9_.-]/', '_', $key)
        );
    }
}
