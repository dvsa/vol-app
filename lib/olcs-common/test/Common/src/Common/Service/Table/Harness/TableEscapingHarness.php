<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Harness;

use Common\Module;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\FormatterPluginManager;
use Common\Service\Table\TableBuilder;
use Laminas\ServiceManager\ServiceManager;
use Common\Rbac\Service\Permission;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

/**
 * Renders every table definition against a hostile row and reports which columns emit it raw.
 *
 * This is an invariant test, not a golden master: it asserts the property "no table emits an
 * unescaped payload" rather than pinning exact output. That means it needs no updating when the UI
 * changes, has nothing to rubber-stamp, and — because it globs the table directory — covers new
 * tables automatically.
 *
 * Coverage is partial by design. The probe cannot satisfy definitions that parse dates, call
 * array_map on a row value, or otherwise require real data shapes; those are reported as skipped
 * with the reason rather than silently passing. A skip is "not covered here", never "safe".
 */
final class TableEscapingHarness
{
    public const MARKER = '<script>xss-probe</script>';

    /**
     * The row value used for snapshot rendering.
     *
     * Contains an ampersand deliberately. A value of plain alphanumerics would be unchanged by
     * escaping, so escaping it twice would be invisible and the snapshot would not catch it. "&"
     * becomes "&amp;" once and "&amp;amp;" twice, so both show up as drift. No angle brackets: this
     * is not a payload, and the snapshot should stay readable as ordinary output.
     */
    public const BENIGN = 'Ampersand & Co';

    /**
     * Inspect every *.table.php under the given directories.
     *
     * @param string[] $directories
     * @return array{leaking: array<string, string[]>, skipped: array<string, string>, rendered: string[]}
     */
    public function inspect(array $directories): array
    {
        $leaking = [];
        $skipped = [];
        $rendered = [];

        $tableBuilder = $this->tableBuilder($directories);

        foreach ($this->tableFiles($directories) as $name => $path) {
            try {
                $html = $this->render($tableBuilder, $name, $path, self::MARKER);
            } catch (\Throwable $e) {
                $skipped[$name] = $e::class . ': ' . $e->getMessage();
                continue;
            }

            $rendered[] = $name;

            $leaks = $this->findLeaks($html);
            if ($leaks !== []) {
                $leaking[$name] = $leaks;
            }
        }

        ksort($leaking);
        ksort($skipped);
        sort($rendered);

        return ['leaking' => $leaking, 'skipped' => $skipped, 'rendered' => $rendered];
    }

    /**
     * Render every table against benign data and return a digest of each result.
     *
     * The companion to inspect(). inspect() catches "a row value reached the output raw"; this
     * catches the opposite mistake — developer-authored markup that got escaped, or a row value
     * escaped twice. Those are invisible to inspect() and, unlike a leak, they are visible to
     * users as literal &lt;b&gt; on the page.
     *
     * Digests rather than the rendered HTML, because 185 tables of markup would be unreviewable in
     * a diff and nobody would read it. When one changes, re-render locally and compare against the
     * previous commit to see what moved.
     *
     * @param string[] $directories
     * @return array{digests: array<string, string>, skipped: array<string, string>}
     */
    public function snapshot(array $directories): array
    {
        $digests = [];
        $skipped = [];

        $tableBuilder = $this->tableBuilder($directories);

        // Pin the timezone for the duration of the run. Six tests in the olcs-common suite call
        // date_default_timezone_set() and never restore it — some to UTC, some to Europe/London.
        // That is not a global variable, so backupGlobals does not undo it, and with
        // executionOrder="random" whichever ran last decides how Formatter\DateTime renders. Three
        // tables drifted intermittently because of it. UTC matches the date.timezone ini that every
        // phpunit.xml.dist already declares, so this restores the intended environment rather than
        // inventing one.
        $timezone = date_default_timezone_get();
        date_default_timezone_set('UTC');

        try {
            foreach ($this->tableFiles($directories) as $name => $path) {
                try {
                    $html = $this->render($tableBuilder, $name, $path, self::BENIGN);
                } catch (\Throwable $e) {
                    $skipped[$name] = $e::class . ': ' . $e->getMessage();
                    continue;
                }

                $digests[$name] = hash('sha256', $this->normalise($html));
            }
        } finally {
            date_default_timezone_set($timezone);
        }

        ksort($digests);
        ksort($skipped);

        return ['digests' => $digests, 'skipped' => $skipped];
    }

    /**
     * Strip the parts of a render that legitimately differ between runs.
     *
     * TableBuilder mints a Laminas\Form\Element\Csrf named "security" on every renderTable(), so
     * its value differs every time and would make the digest of any table with a crud section
     * unstable.
     *
     * Matched per-element rather than with one attribute-order-sensitive pattern, because the
     * rendered form is `value="..." ... name="security"` — value first. A pattern anchored on
     * name="security" looking forwards for value= silently matches nothing, and the failure mode
     * is a snapshot that looks fine until it randomly fails.
     *
     * Today's date goes the same way, for a subtler reason. row() harvests its keys from the
     * *definition* source, so a key read only inside a formatter class is absent from the row —
     * Formatter\NoteUrl reads $row['createdOn'], which note.table.php never mentions. The lookup
     * yields null, the harness's error handler swallows the notice, and new \DateTime(null) means
     * "now". The digest then encodes the day it was recorded and every later run is red. That is a
     * property of the probe, not of the table, so it is normalised out rather than baselined.
     *
     * Only the current date is replaced, never dates in general: a table rendering a fixed date
     * still has it covered by the digest.
     */
    private function normalise(string $html): string
    {
        $html = (string)preg_replace_callback(
            '/<input\b[^>]*>/',
            static function (array $match): string {
                $tag = $match[0];

                if (!str_contains($tag, 'name="security"') && !str_contains($tag, 'js-csrf-token')) {
                    return $tag;
                }

                return (string)preg_replace('/value="[^"]*"/', 'value="CSRF"', $tag);
            },
            $html
        );

        return str_replace($this->todayInEveryRenderedFormat(), 'TODAY', $html);
    }

    /**
     * Today, in each format a table might render it in.
     *
     * Callers run under the UTC pin snapshot() applies, so "today" here is the same day the render
     * saw. Module::$dateFormat is what the application is configured to use and is therefore the
     * one that matters; the rest are cheap insurance against a formatter that hardcodes its own.
     *
     * @return string[]
     */
    private function todayInEveryRenderedFormat(): array
    {
        $now = new \DateTimeImmutable();

        $formats = array_unique([Module::$dateFormat, 'd/m/Y', 'Y-m-d', 'd M Y', 'j F Y']);

        return array_map(static fn(string $format): string => $now->format($format), $formats);
    }

    /**
     * @param string[] $directories
     * @return array<string, string> name => path
     */
    public function tableFiles(array $directories): array
    {
        $files = [];

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $file) {
                if (!$file->isFile() || !str_ends_with($file->getFilename(), '.table.php')) {
                    continue;
                }

                $files[$file->getFilename()] = $file->getPathname();
            }
        }

        ksort($files);

        return $files;
    }

    private function render(TableBuilder $tableBuilder, string $fileName, string $path, string $probeValue): string
    {
        // Passed by name, not as a loaded array. Definitions are include()d by TableBuilder and
        // several call $this->callFormatter(...) in inline closures, where $this is meant to be the
        // TableBuilder. Loading the file here instead would bind $this to the harness.
        $tableName = substr($fileName, 0, -strlen('.table.php'));

        // Some table partials echo directly rather than returning, so capture anything written to
        // stdout — both to keep it out of the test output and because a leak could appear there.
        //
        // The buffer level is restored explicitly: TableBuilder renders partials inside its own
        // ob_start()/ob_end_clean() pair, and a partial that throws leaves that buffer open.
        $bufferLevel = ob_get_level();

        // The probe is deliberately nonsense data, so undefined-key notices and null-argument
        // deprecations are expected noise from a formatter meeting a shape it was not written for.
        // They are not what is under test, and left unhandled they drown the signal.
        set_error_handler(static fn(): bool => true, E_WARNING | E_NOTICE | E_DEPRECATED);

        ob_start();
        try {
            $returned = (string)$tableBuilder->buildTable($tableName, [$this->row($path, $probeValue)], [], true);
        } finally {
            $echoed = '';
            while (ob_get_level() > $bufferLevel) {
                $echoed = (string)ob_get_clean() . $echoed;
            }

            restore_error_handler();
        }

        return $returned . $echoed;
    }

    /**
     * The row handed to the table.
     *
     * A bare RecursiveProbe would be neater, but several column types declare `array $data` and
     * PHP's array type rejects ArrayAccess. So the top level is a real array and every value is a
     * probe — nested access of any depth still resolves.
     *
     * The keys are harvested from the definition's source rather than by loading it, for the same
     * $this-binding reason as above: every ['literal'] subscript and every 'name' => 'x' column
     * declaration, which between them cover both plain columns and inline closures.
     *
     * @return array<string, RecursiveProbe>
     */
    private function row(string $path, string $probeValue): array
    {
        $probe = new RecursiveProbe($probeValue);
        $source = (string)file_get_contents($path);

        $keys = ['id', 'version', 'action'];

        if (preg_match_all('/\[\s*[\'"]([A-Za-z0-9_]+)[\'"]\s*\]/', $source, $matches)) {
            $keys = array_merge($keys, $matches[1]);
        }

        if (preg_match_all('/[\'"]name[\'"]\s*=>\s*[\'"]([A-Za-z0-9_>-]+)[\'"]/', $source, $matches)) {
            foreach ($matches[1] as $name) {
                // "licence->organisation->name" style references index the first segment.
                $keys[] = explode('->', $name)[0];
            }
        }

        return array_fill_keys(array_unique($keys), $probe);
    }

    /**
     * Every place the marker reached the output without being escaped.
     *
     * @return string[]
     */
    private function findLeaks(string $html): array
    {
        $leaks = [];

        if (str_contains($html, self::MARKER)) {
            $leaks[] = 'raw marker in cell content';
        }

        // A marker inside an attribute that still carries its quote characters means the value
        // could have closed the attribute.
        if (preg_match('/="[^"]*<script>xss-probe/', $html) === 1) {
            $leaks[] = 'raw marker inside an attribute';
        }

        return $leaks;
    }

    /**
     * @param string[] $directories
     */
    private function tableBuilder(array $directories): TableBuilder
    {
        $container = $this->container();

        $tableBuilder = new TableBuilder(
            $container,
            $container->get(Permission::class),
            $container->get('translator'),
            $container->get('Helper\Url'),
            [
                'tables' => [
                    'config' => $this->configLocations($directories),
                    'partials' => [
                        'html' => __DIR__ . '/../../../../../../../Common/view/table/',
                        'csv' => __DIR__ . '/../../../../../../../Common/view/table/csv',
                    ],
                ],
                'csrf' => ['timeout' => 9999],
            ],
            $container->get(FormatterPluginManager::class),
        );

        // Some formatters resolve 'TableBuilder' to render nested tables; give them the real one.
        $container->setService('TableBuilder', $tableBuilder);

        return $tableBuilder;
    }

    /**
     * Every directory holding a definition, so TableBuilder can resolve a table by name. Includes
     * nested directories, since definitions live in subfolders such as Tables/Bus.
     *
     * @param string[] $directories
     * @return string[]
     */
    private function configLocations(array $directories): array
    {
        $locations = [];

        foreach ($this->tableFiles($directories) as $path) {
            $locations[dirname($path) . '/'] = true;
        }

        return array_keys($locations);
    }

    /**
     * The services the real formatter factories ask for. Shared with FormatterEscapingHarness.
     */
    private function container(): ServiceManager
    {
        return HarnessContainer::create();
    }
}
