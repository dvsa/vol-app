<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Snapshot;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Snapshot of how every command and query is routed, filtered and validated.
 *
 * Each DTO is built through the real AnnotationBuilder, and the resulting container (route, HTTP method
 * and the full input filter) is compared with the JSON file committed for it under dto/. A change to a
 * DTO's rules, or to the code that reads them, fails here and shows up as a diff in review.
 *
 * After an intended change, regenerate the snapshots and review the diff:
 *   UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --filter DtoSnapshotTest
 *
 * Partials (Command\Partial) are not built on their own: each one is captured inside the snapshot of
 * every DTO that uses it.
 *
 * See docs/app/testing.md ("API request validation snapshot") for reading a snapshot and handling failures.
 */
#[CoversClass(AnnotationBuilder::class)]
final class DtoSnapshotTest extends TestCase
{
    private const string SRC_DIR = __DIR__ . '/../../src';
    private const string SNAPSHOT_DIR = __DIR__ . '/dto';
    private const string TRANSFER_NAMESPACE = 'Dvsa\\Olcs\\Transfer\\';
    private const string PARTIAL_NAMESPACE = 'Dvsa\\Olcs\\Transfer\\Command\\Partial\\';
    private const string UPDATE_COMMAND = 'UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --filter DtoSnapshotTest';

    /**
     * @param class-string<CommandInterface|QueryInterface> $dtoClass
     */
    #[DataProvider('dtoProvider')]
    public function testDtoMatchesSnapshot(string $dtoClass): void
    {
        $actual = self::encode($this->build($dtoClass));
        $file = self::snapshotFile($dtoClass);

        if (self::isUpdating()) {
            if (!is_dir(dirname($file))) {
                mkdir(dirname($file), 0777, true);
            }

            file_put_contents($file, $actual);
        }

        $this->assertFileExists(
            $file,
            sprintf('%s has no snapshot. Create it with `%s` and review the new file.', $dtoClass, self::UPDATE_COMMAND)
        );

        // Re-encode the stored file so formatting differences cannot fail the comparison
        $expected = self::encode(json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR));

        $this->assertSame(
            $expected,
            $actual,
            sprintf(
                '%s no longer matches its snapshot. If the change is intended, run `%s` and review the diff.',
                $dtoClass,
                self::UPDATE_COMMAND
            )
        );
    }

    public function testEverySnapshotBelongsToADto(): void
    {
        $expected = array_map(self::snapshotFile(...), self::dtoClasses());

        $orphans = [];
        $files = is_dir(self::SNAPSHOT_DIR)
            ? new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(self::SNAPSHOT_DIR, \FilesystemIterator::SKIP_DOTS)
            )
            : [];

        foreach ($files as $file) {
            if (!in_array($file->getPathname(), $expected, true)) {
                $orphans[] = $file->getPathname();
            }
        }

        if (self::isUpdating()) {
            array_map(unlink(...), $orphans);
            $orphans = [];
        }

        sort($orphans);

        $this->assertSame(
            [],
            $orphans,
            sprintf('These snapshots have no matching DTO. Delete them, or run `%s`.', self::UPDATE_COMMAND)
        );
    }

    /**
     * @return array<class-string, array{class-string<CommandInterface|QueryInterface>}>
     */
    public static function dtoProvider(): array
    {
        $cases = [];

        foreach (self::dtoClasses() as $dtoClass) {
            $cases[$dtoClass] = [$dtoClass];
        }

        return $cases;
    }

    /**
     * Every concrete command and query under src/Command and src/Query, sorted by name.
     *
     * A file that does not autoload is an error rather than a skip, so a misnamed DTO cannot quietly
     * drop out of the snapshot.
     *
     * @return list<class-string<CommandInterface|QueryInterface>>
     */
    private static function dtoClasses(): array
    {
        $classes = [];

        foreach (['Command', 'Query'] as $type) {
            $root = self::SRC_DIR . '/' . $type;
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $relative = substr($file->getPathname(), strlen($root) + 1, -strlen('.php'));
                $class = self::TRANSFER_NAMESPACE . $type . '\\' . str_replace('/', '\\', $relative);

                if (str_starts_with($class, self::PARTIAL_NAMESPACE)) {
                    continue;
                }

                if (!class_exists($class) && !interface_exists($class) && !trait_exists($class)) {
                    throw new \LogicException(sprintf('%s does not autoload as %s', $file->getPathname(), $class));
                }

                $reflection = new \ReflectionClass($class);

                if (
                    $reflection->isInstantiable()
                    && (
                        $reflection->implementsInterface(CommandInterface::class)
                        || $reflection->implementsInterface(QueryInterface::class)
                    )
                ) {
                    /** @var class-string<CommandInterface|QueryInterface> $class */
                    $classes[] = $class;
                }
            }
        }

        sort($classes);

        return $classes;
    }

    /**
     * @param class-string<CommandInterface|QueryInterface> $dtoClass
     *
     * @return array<string, mixed>
     */
    private function build(string $dtoClass): array
    {
        $builder = new AnnotationBuilder();
        $builder->setFilterManager(new FilterPluginManager(new ServiceManager()));
        $builder->setValidatorManager(new ValidatorPluginManager(new ServiceManager()));

        $dto = $dtoClass::create([]);
        $describer = new InputFilterDescriber();

        if ($dto instanceof CommandInterface) {
            $container = $builder->createCommand($dto);

            return [
                'routeName' => $container->getRouteName(),
                'method' => $container->getMethod(),
                'inputFilter' => $describer->describe($container->getInputFilter()),
            ];
        }

        $container = $builder->createQuery($dto);

        return [
            'routeName' => $container->getRouteName(),
            'inputFilter' => $describer->describe($container->getInputFilter()),
        ];
    }

    private static function snapshotFile(string $dtoClass): string
    {
        $relative = str_replace('\\', '/', substr($dtoClass, strlen(self::TRANSFER_NAMESPACE)));

        return self::SNAPSHOT_DIR . '/' . $relative . '.json';
    }

    private static function encode(mixed $data): string
    {
        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
            | JSON_THROW_ON_ERROR
        );

        // Two-space indentation, per .editorconfig. JSON strings cannot contain raw newlines, so every
        // run of leading spaces is indentation.
        return preg_replace_callback(
            '/^(?: {4})+/m',
            static fn (array $match): string => str_repeat('  ', intdiv(strlen($match[0]), 4)),
            $json
        ) . "\n";
    }

    private static function isUpdating(): bool
    {
        return getenv('UPDATE_SNAPSHOTS') === '1';
    }
}
