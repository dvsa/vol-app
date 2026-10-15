<?php

declare(strict_types=1);

namespace CommonTest\Common\Form\Snapshot;

use Common\Service\FormAnnotationBuilderFactory;
use Laminas\Filter\FilterPluginManager;
use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;

/**
 * Snapshot of the form specification read from every class that declares a form or fieldset with annotations.
 *
 * Each class goes through the builder behind the FormAnnotationBuilder service, which FormHelperService::createForm()
 * and every other form consumer use. The specification it returns is compared with the JSON file committed for the
 * class. The specification records the form's name, type, attributes and options, its elements and fieldsets in
 * order (composed fieldsets expanded in place) and its input filter. A change to a form's annotations, or to the code
 * that reads them, fails here and shows up as a diff in review.
 *
 * Forms live in olcs-auth, olcs-common, internal and selfserve, so each has a FormSpecificationSnapshotTest that
 * extends this and points it at its own source and snapshot directories.
 *
 * After an intended change, regenerate the snapshots from the package's directory and review the diff:
 *   UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --filter FormSpecificationSnapshotTest
 *
 * See docs/app/testing.md ("Form specification snapshot") for reading a snapshot and handling failures.
 */
abstract class FormSpecificationSnapshotTestCase extends TestCase
{
    private const string UPDATE_COMMAND
        = 'UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --filter FormSpecificationSnapshotTest';

    private const string LEGACY_ARGUMENTS_DEPRECATION
        = '^Passing a single array to the constructor of Laminas\\\\Form\\\\Annotation\\\\\w+ is deprecated';

    /**
     * A file is a form model if it refers to the annotation namespace for anything other than a builder. That holds
     * for docblock annotations and PHP attributes alike, so it finds the same classes before and after a conversion.
     */
    private const string FORM_MODEL_PATTERN = '/Laminas\\\\Form\\\\Annotation(?![\\\\\w]*Builder)\b/';

    /**
     * Directories searched, recursively, for form model classes.
     *
     * @return list<string>
     */
    abstract protected static function sourceDirectories(): array;

    abstract protected static function snapshotDirectory(): string;

    /**
     * Laminas deprecated passing an annotation's arguments as a single array, which hundreds of forms still do. Only
     * that deprecation is ignored; any other still fails the test.
     *
     * @param class-string $formClass
     */
    #[DataProvider('formProvider')]
    #[IgnoreDeprecations(self::LEGACY_ARGUMENTS_DEPRECATION)]
    public function testFormSpecificationMatchesSnapshot(string $formClass): void
    {
        $actual = self::encode(self::specificationOf($formClass));
        $file = self::snapshotFile($formClass);

        if (self::isUpdating()) {
            // Directories are shared between forms, and paratest may be creating the same one in another process
            if (!is_dir(dirname($file)) && !@mkdir(dirname($file), 0777, true) && !is_dir(dirname($file))) {
                throw new \RuntimeException(sprintf('Cannot create %s', dirname($file)));
            }

            file_put_contents($file, $actual);
        }

        $this->assertFileExists(
            $file,
            sprintf(
                '%s has no snapshot. Create it with `%s` and review the new file.',
                $formClass,
                self::UPDATE_COMMAND
            )
        );

        // Re-encode the stored file so formatting differences cannot fail the comparison
        $expected = self::encode(json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR));

        $this->assertSame(
            $expected,
            $actual,
            sprintf(
                '%s no longer matches its snapshot. If the change is intended, run `%s` and review the diff.',
                $formClass,
                self::UPDATE_COMMAND
            )
        );
    }

    public function testEverySnapshotBelongsToAForm(): void
    {
        $expected = array_map(self::snapshotFile(...), self::formClasses());

        $orphans = [];
        $files = is_dir(static::snapshotDirectory())
            ? new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(static::snapshotDirectory(), \FilesystemIterator::SKIP_DOTS)
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
            sprintf('These snapshots have no matching form. Delete them, or run `%s`.', self::UPDATE_COMMAND)
        );
    }

    /**
     * The builder adds a form's elements in the order reflection lists its properties, and that order changed in
     * PHP 8.5: a class's trait properties now come before its inherited ones rather than after. A form class with
     * both would show its fields in a different order on each side of that change, and its snapshot would depend on
     * the PHP version running it. CI does not run every package on every PHP version, so it is checked here.
     */
    public function testFieldOrderDoesNotDependOnThePhpVersion(): void
    {
        $affected = [];

        foreach (self::formClasses() as $formClass) {
            for ($class = new \ReflectionClass($formClass); $class !== false; $class = $class->getParentClass()) {
                $parent = $class->getParentClass();

                if ($parent !== false && $parent->getProperties() !== [] && self::hasTraitProperties($class)) {
                    $affected[] = sprintf('%s (in %s)', $formClass, $class->getName());
                    break;
                }
            }
        }

        $this->assertSame(
            [],
            $affected,
            'These forms get properties both from a parent class and from a trait, so their fields appear in a '
            . 'different order before and after PHP 8.5. Declare the trait\'s properties in the class, or stop '
            . 'extending the parent.'
        );
    }

    private static function hasTraitProperties(\ReflectionClass $class): bool
    {
        foreach ($class->getTraits() as $trait) {
            // Includes the properties of any traits the trait itself uses
            if ($trait->getProperties() !== []) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<class-string, array{class-string}>
     */
    public static function formProvider(): array
    {
        $cases = [];

        foreach (self::formClasses() as $formClass) {
            $cases[$formClass] = [$formClass];
        }

        return $cases;
    }

    /**
     * Every concrete class declared in a form model file under the source directories, sorted by name.
     *
     * Traits and abstract classes are not built on their own: what they declare is captured in the snapshot of every
     * class that uses or extends them. A class that does not autoload from the file declaring it is an error rather
     * than a skip, so a misnamed form cannot quietly drop out of the snapshot.
     *
     * @return list<class-string>
     */
    private static function formClasses(): array
    {
        $classes = [];

        foreach (static::sourceDirectories() as $directory) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $path = (string) $file->getRealPath();
                $code = (string) file_get_contents($path);

                if (!preg_match(self::FORM_MODEL_PATTERN, $code)) {
                    continue;
                }

                foreach (self::classesDeclaredIn($code) as $class) {
                    if (!class_exists($class) && !trait_exists($class) && !interface_exists($class)) {
                        throw new \LogicException(sprintf('%s declares %s, which does not autoload', $path, $class));
                    }

                    $reflection = new \ReflectionClass($class);

                    if (realpath((string) $reflection->getFileName()) !== $path) {
                        throw new \LogicException(sprintf(
                            '%s declares %s, but it autoloads from %s',
                            $path,
                            $class,
                            $reflection->getFileName()
                        ));
                    }

                    if ($reflection->isInstantiable()) {
                        $classes[] = $class;
                    }
                }
            }
        }

        sort($classes);

        return $classes;
    }

    /**
     * The fully qualified names of the classes, traits, interfaces and enums a PHP file declares.
     *
     * @return list<string>
     */
    private static function classesDeclaredIn(string $code): array
    {
        $tokens = array_values(array_filter(
            \PhpToken::tokenize($code),
            static fn (\PhpToken $token): bool => !$token->isIgnorable()
        ));

        $namespace = '';
        $classes = [];

        foreach ($tokens as $i => $token) {
            if ($token->is(T_NAMESPACE) && $tokens[$i + 1]->is([T_NAME_QUALIFIED, T_STRING])) {
                $namespace = $tokens[$i + 1]->text . '\\';
            }

            // Skip Foo::class and anonymous classes
            if (
                $token->is([T_CLASS, T_TRAIT, T_INTERFACE, T_ENUM])
                && !($tokens[$i - 1] ?? null)?->is([T_DOUBLE_COLON, T_NEW])
                && $tokens[$i + 1]->is(T_STRING)
            ) {
                $classes[] = $namespace . $tokens[$i + 1]->text;
            }
        }

        return $classes;
    }

    /**
     * The form specification, as plain data.
     *
     * The builder comes from the factory registered as the FormAnnotationBuilder service. Its plugin managers are
     * plain ones: they are only used to build a form from the specification, never to read the specification.
     *
     * @param class-string $formClass
     */
    private static function specificationOf(string $formClass): mixed
    {
        $container = new ServiceManager();
        $container->setService('FormElementManager', new FormElementManager(new ServiceManager()));
        $container->setService('ValidatorManager', new ValidatorPluginManager(new ServiceManager()));
        $container->setService('FilterManager', new FilterPluginManager(new ServiceManager()));

        $builder = (new FormAnnotationBuilderFactory())($container, 'FormAnnotationBuilder');

        return self::toPlainData($builder->getFormSpecification($formClass), $formClass);
    }

    /**
     * The builder nests array objects inside the specification; the form factory reads them as arrays, so they are
     * recorded as arrays. Any other object would be encoded as its public properties only, so it is an error.
     */
    private static function toPlainData(mixed $value, string $formClass): mixed
    {
        if ($value instanceof \Traversable) {
            $value = iterator_to_array($value);
        }

        if (is_array($value)) {
            return array_map(static fn (mixed $item): mixed => self::toPlainData($item, $formClass), $value);
        }

        if (is_object($value) || is_resource($value)) {
            throw new \UnexpectedValueException(sprintf(
                'The specification of %s contains a %s, which this snapshot cannot record',
                $formClass,
                get_debug_type($value)
            ));
        }

        return $value;
    }

    private static function snapshotFile(string $formClass): string
    {
        return static::snapshotDirectory() . '/' . str_replace('\\', '/', $formClass) . '.json';
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
