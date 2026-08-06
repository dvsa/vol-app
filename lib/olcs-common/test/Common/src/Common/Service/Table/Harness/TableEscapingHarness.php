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
 * A definition that cannot be rendered at all is reported as skipped with the reason rather than
 * silently passing. A skip is "not covered here", never "safe".
 *
 * Individual values a type constraint pins down — a number_format() argument, a date that has to
 * parse — cannot carry the payload, so the ordinary render says nothing about them. Those do not
 * stay unknown either: isolate() puts each one back on its own and reports whether the constraint
 * is what keeps it off the page. See PayloadIsolation.
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
     * Definitions another file's basename made unreachable, as name => the name that won.
     *
     * Populated by tableFiles() on every call.
     *
     * @var array<string, string>
     */
    private array $shadowed = [];

    /**
     * The shadowed files' absolute paths, so they can be rendered with a builder of their own.
     *
     * @var array<string, string>
     */
    private array $shadowedPaths = [];

    /**
     * Directories holding table definitions, as handed to TableBuilder.
     *
     * Populated by tableFiles(), which has to walk the tree to find them anyway.
     *
     * @var string[]
     */
    private array $locations = [];

    /**
     * Learning a probe's required type from the failure that rejected it, shared with
     * FormatterEscapingHarness so both tests treat a constraint the same way.
     */
    private RowProbeAdaptation $adaptation;

    public function __construct()
    {
        $this->adaptation = new RowProbeAdaptation();
    }

    /**
     * Inspect every *.table.php under the given directories.
     *
     * @param string[] $directories
     * @return array{
     *     leaking: array<string, string[]>,
     *     skipped: array<string, string>,
     *     rendered: string[],
     *     unproven: array<string, array<string, string>>
     * }
     */
    public function inspect(array $directories): array
    {
        $leaking = [];
        $skipped = [];
        $rendered = [];
        $unproven = [];

        $tableBuilder = $this->tableBuilder($directories);

        $renders = [];

        foreach ($this->tableFiles($directories) as $name => $path) {
            $renders[$name] = ['path' => $path, 'builder' => $tableBuilder];
        }

        // Appended rather than merged, so a shadowed file cannot displace its winner: both are real
        // definitions and both are inspected, each through the builder that resolves it.
        foreach ($this->shadowedRenders($directories) as $name => $shadowed) {
            $renders[$name] = $shadowed;
        }

        foreach ($renders as $name => $render) {
            try {
                $result = $this->render($render['builder'], $name, $render['path'], self::MARKER);
            } catch (\Throwable $e) {
                $skipped[$name] = $e::class . ': ' . $e->getMessage();
                continue;
            }

            $rendered[] = $name;

            $leaks = $this->findLeaks($result['output']);

            // Every value the settled render could not probe is put back one at a time, so it ends
            // up either escaped or rejected by its type — both proofs — rather than unknown. A
            // payload that reaches the output only under isolation is a leak the shared row was
            // masking, and it joins the leaks the ordinary render found.
            if ($result['unprobed'] !== []) {
                $isolated = $this->isolate(
                    $render['builder'],
                    $name,
                    $render['path'],
                    $result['unprobed'],
                    $result,
                );

                if ($isolated['unproven'] !== []) {
                    $unproven[$name] = $isolated['unproven'];
                }

                $leaks = array_merge($leaks, $isolated['leaking']);
            }

            if ($leaks !== []) {
                $leaking[$name] = $leaks;
            }
        }

        ksort($leaking);
        ksort($skipped);
        ksort($unproven);
        sort($rendered);

        return [
            'leaking' => $leaking,
            'skipped' => $skipped,
            'rendered' => $rendered,
            'unproven' => $unproven,
        ];
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
     * Also reports outright double-escaping, which the digests alone cannot catch. A digest only
     * detects *change*, so anything already wrong when the baseline was first recorded is enshrined
     * as expected output — which is exactly what happened to admin-presiding-tcs and operator-users,
     * where a bulk transform wrapped a value that was already escaped at its assignment and the
     * snapshot then recorded the result as correct. The benign probe contains an ampersand, so one
     * escape gives "&amp;" and two give "&amp;amp;"; the latter can be asserted absolutely rather
     * than relative to a baseline.
     *
     * @param string[] $directories
     * @return array{
     *     digests: array<string, string>,
     *     skipped: array<string, string>,
     *     doubleEscaped: string[],
     *     htmlInCsv: string[]
     * }
     */
    public function snapshot(array $directories): array
    {
        $digests = [];
        $skipped = [];
        $doubleEscaped = [];
        $htmlInCsv = [];

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
                    $html = $this->render($tableBuilder, $name, $path, self::BENIGN)['output'];
                } catch (\Throwable $e) {
                    $skipped[$name] = $e::class . ': ' . $e->getMessage();
                    continue;
                }

                $digests[$name] = hash('sha256', $this->normalise($html));

                if (str_contains($html, htmlspecialchars(htmlspecialchars('&')))) {
                    $doubleEscaped[] = $name;
                }

                if ($this->emitsHtmlEntitiesAsCsv($directories, $name, $path)) {
                    $htmlInCsv[] = $name;
                }
            }
        } finally {
            date_default_timezone_set($timezone);
        }

        foreach ($this->shadowedRenders($directories) as $name => $shadowed) {
            try {
                $html = $this->render($shadowed['builder'], $name, $shadowed['path'], self::BENIGN)['output'];
            } catch (\Throwable $e) {
                $skipped[$name] = $e::class . ': ' . $e->getMessage();
                continue;
            }

            $digests[$name] = hash('sha256', $this->normalise($html));

            if (str_contains($html, htmlspecialchars(htmlspecialchars('&')))) {
                $doubleEscaped[] = $name;
            }
        }

        ksort($digests);
        ksort($skipped);
        sort($doubleEscaped);
        sort($htmlInCsv);

        return [
            'digests' => $digests,
            'skipped' => $skipped,
            'doubleEscaped' => $doubleEscaped,
            'htmlInCsv' => $htmlInCsv,
        ];
    }

    /**
     * Whether a table rendered as CSV still carries HTML entities.
     *
     * The pipeline escapes at the source — a formatter escapes the row value it interpolates —
     * which is right for HTML and wrong for every other output format. Six controllers export a
     * table as CSV, and nothing downstream of them decodes, so an escaped ampersand reaches the
     * spreadsheet as "&amp;". Three exports were doing exactly that before TableBuilder learned to
     * decode for CSV, and no test noticed because every test rendered HTML.
     *
     * Asserted absolutely rather than against a baseline: there is no legitimate reason for an
     * entity to appear in a CSV, so there is nothing to record.
     */
    private function emitsHtmlEntitiesAsCsv(array $directories, string $name, string $path): bool
    {
        $builder = $this->tableBuilder($directories);
        $builder->setContentType(TableBuilder::CONTENT_TYPE_CSV);

        try {
            $csv = $this->render($builder, $name, $path, self::BENIGN)['output'];
        } catch (\Throwable) {
            // Not every definition renders as CSV, and the ones that do not are not exported that
            // way either. A failure here says nothing about escaping.
            return false;
        }

        return str_contains($csv, htmlspecialchars('&'));
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
     * @return array<string, string> path relative to its scanned directory => absolute path
     */
    public function tableFiles(array $directories): array
    {
        $candidates = [];
        $locations = [];

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $root = rtrim($directory, DIRECTORY_SEPARATOR);

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $file) {
                if (!$file->isFile() || !str_ends_with($file->getFilename(), '.table.php')) {
                    continue;
                }

                $candidates[$file->getFilename()][] = [
                    'path' => $file->getPathname(),
                    'relative' => ltrim(substr($file->getPathname(), strlen($root)), DIRECTORY_SEPARATOR),
                ];

                $locations[dirname($file->getPathname()) . '/'] = true;
            }
        }

        $this->locations = array_keys($locations);

        // A definition is resolved by name against the configured locations, so two files sharing
        // a basename cannot both be reached — one wins and the other is unreachable here. Pick the
        // winner by TableBuilder's own rule (it reverses the list and takes the first hit) rather
        // than by discovery order, or the harness reports one file while rendering the other.
        $order = array_flip(array_reverse($this->locations));

        $files = [];
        $this->shadowed = [];
        $this->shadowedPaths = [];

        foreach ($candidates as $entries) {
            usort(
                $entries,
                static fn(array $a, array $b): int
                    => $order[dirname($a['path']) . '/'] <=> $order[dirname($b['path']) . '/'],
            );

            $winner = array_shift($entries);

            // Keyed by path relative to the scanned directory, not by basename: a shadowed file
            // and its winner would otherwise appear under one name, one as a digest and one as a
            // skip. The name TableBuilder resolves is still the basename — see render().
            $files[$winner['relative']] = $winner['path'];

            foreach ($entries as $loser) {
                $this->shadowed[$loser['relative']] = $winner['relative'];
                $this->shadowedPaths[$loser['relative']] = $loser['path'];
            }
        }

        ksort($files);
        ksort($this->shadowed);

        return $files;
    }

    /**
     * Render the table, adapting the row when a type constraint rejects the probe.
     *
     * @return array{
     *     output: string,
     *     unprobed: array<string, string>,
     *     adapted: array<string, string>,
     *     overrides: array<string, mixed>
     * }
     */
    private function render(TableBuilder $tableBuilder, string $fileName, string $path, string $probeValue): array
    {
        $tableName = $this->tableName($fileName);

        $adapted = [];
        $overrides = [];
        $row = $this->buildRow($path, $probeValue, $overrides, $adapted);

        for ($attempt = 0; $attempt <= RowProbeAdaptation::MAX_ADAPTATIONS; $attempt++) {
            $result = $this->attempt($tableBuilder, $tableName, $row);

            if ($result['thrown'] !== null) {
                $this->adapt($result['thrown'], $adapted, $overrides, $path, $row);

                $row = $this->buildRow($path, $probeValue, $overrides, $adapted);

                continue;
            }

            return [
                'output' => $result['output'],
                'unprobed' => array_merge(
                    $this->adaptation->payloadLosing($adapted),
                    RowFixtures::gaps(RowFixtures::forSource((string)file_get_contents($path))),
                ),
                'adapted' => $adapted,
                'overrides' => $overrides,
            ];
        }

        throw new \RuntimeException(sprintf(
            'adaptation did not settle after %d attempts; adapted: %s',
            RowProbeAdaptation::MAX_ADAPTATIONS,
            implode(', ', array_map(
                static fn(string $k, string $t): string => "{$k}={$t}",
                array_keys($adapted),
                $adapted
            ))
        ));
    }

    /**
     * Put each payload-losing value back, one at a time, and report what the table does with it.
     *
     * See PayloadIsolation for why this exists: a substituted value is both untested and, because
     * the row is shared by every column, capable of hiding a leak in a column that never needed the
     * substitution. Restoring it at one path while the rest of the row stays settled is what tells
     * those apart.
     *
     * No adaptation loop here on purpose. Violating the constraint is the whole point, so adapting
     * away from it would put the substitution straight back.
     *
     * Nothing is returned for a value that comes back safe, by either route. Escaped and rejected
     * are both proofs, so there is no gradation to record and no list to keep — only the two ways
     * of not being safe are worth reporting.
     *
     * @param array<string, string> $unprobed path => type, from the settled render
     * @param array{adapted: array<string, string>, overrides: array<string, mixed>} $settled
     * @return array{unproven: array<string, string>, leaking: string[]}
     */
    private function isolate(
        TableBuilder $tableBuilder,
        string $fileName,
        string $path,
        array $unprobed,
        array $settled
    ): array {
        $tableName = $this->tableName($fileName);
        $unproven = [];
        $leaking = [];

        foreach ($unprobed as $target => $type) {
            // Dropping the override is what restores a probe-served path: the probe answers any key
            // with itself, and itself is the marker. A fixture leaf has no override to drop, so
            // withMarkerAt() writes it into the built row instead.
            $overrides = $settled['overrides'];
            $adapted = $settled['adapted'];
            unset($overrides[$target], $adapted[$target]);

            $row = $this->buildRow($path, self::MARKER, $overrides, $adapted);
            $row = PayloadIsolation::withMarkerAt($row, $target, self::MARKER);

            $result = $this->attempt($tableBuilder, $tableName, $row);

            $verdict = PayloadIsolation::verdict(
                $result['output'],
                $result['thrown'],
                self::MARKER,
                $result['thrown'] !== null && $this->adaptation->requiredType($result['thrown']) !== null,
            );

            match ($verdict) {
                PayloadIsolation::LEAKING => $leaking[] = sprintf('%s reaches the output raw', $target),
                PayloadIsolation::UNPROVEN => $unproven[$target] = sprintf(
                    '%s: %s',
                    $type,
                    $result['thrown'] !== null
                        ? $result['thrown']::class . ': ' . $result['thrown']->getMessage()
                        : 'no exception',
                ),
                // CONSTRAINED and ESCAPED are both proofs, so neither is recorded.
                default => null,
            };
        }

        ksort($unproven);
        sort($leaking);

        return ['unproven' => $unproven, 'leaking' => $leaking];
    }

    /**
     * One render, with no adaptation and nothing discarded.
     *
     * Both halves of the result matter to the caller. render() wants the exception so it can learn
     * a type from it; isolate() wants the output *and* the exception, because output written before
     * a throw has still been written — a column that echoes a value and a later column that rejects
     * it is a leak, not a constraint, and only the buffer contents tell them apart.
     *
     * @param array<string, mixed> $row
     * @return array{output: string, thrown: ?\Throwable}
     */
    private function attempt(TableBuilder $tableBuilder, string $tableName, array $row): array
    {
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

        $returned = '';
        $thrown = null;

        try {
            $returned = (string)$tableBuilder->buildTable($tableName, [$row], [], true);
        } catch (\Throwable $e) {
            $thrown = $e;
        }

        $echoed = $this->drainBuffers($bufferLevel);
        restore_error_handler();

        return ['output' => $returned . $echoed, 'thrown' => $thrown];
    }

    /**
     * The name TableBuilder resolves a definition by.
     *
     * The basename, not the caller's key: keys carry a directory prefix so shadowed files stay
     * distinguishable, but TableBuilder resolves definitions by bare name.
     */
    private function tableName(string $fileName): string
    {
        return substr(basename($fileName), 0, -strlen('.table.php'));
    }

    /**
     * The row as the adaptation state describes it.
     *
     * Shared by the adaptation loop and the isolation pass, so both build the row the same way and
     * an isolated render differs from the settled one in exactly the value under test.
     *
     * @param array<string, mixed> $overrides dotted path => value
     * @param array<string, string> $adapted target => type
     * @return array<string, mixed>
     */
    private function buildRow(string $path, string $probeValue, array $overrides, array $adapted): array
    {
        $row = $this->row($path, $probeValue, $overrides);

        // An override may be rooted at a key the definition never spells, because the read happens
        // inside a formatter. Give it a probe to hang off, or the override sits at a path nothing
        // ever walks.
        foreach (array_keys($overrides) as $target) {
            $root = explode('.', $target)[0];

            if (!isset($row[$root])) {
                $row[$root] = new RecursiveProbe($probeValue, $overrides, $root);
            }
        }

        foreach ($adapted as $target => $type) {
            if (!str_contains($target, '.')) {
                $row[$target] = $this->adaptation->substitute($type);
            }
        }

        return $row;
    }

    /**
     * Record what the failure demanded, or rethrow when nothing can be learned from it.
     *
     * Table-level adaptation is deliberately narrower than the formatter-level one. There, a failure
     * that cannot name its key may widen to every remaining probe, because a string substitution
     * still carries the marker. Here one row is shared by every column in the table, so widening
     * would de-probe columns that were never at fault and quietly weaken the assertion for the whole
     * definition. A failure this cannot attribute is reported as a skip instead.
     *
     * @param array<string, string> $adapted target => type, mutated in place
     * @param array<string, mixed> $overrides dotted path => value, mutated in place
     * @param array<string, mixed> $row the row as it stands, for widening and membership checks
     */
    private function adapt(\Throwable $e, array &$adapted, array &$overrides, string $path, array $row): void
    {
        $type = $this->adaptation->requiredType($e);

        // Nothing learned, so retrying would loop on the same failure. A missing service or a
        // partial that cannot render is not a probe problem.
        if ($type === null) {
            throw $e;
        }

        $files = $this->readableFiles($e, $path);
        $column = $this->columnFromTrace($e);

        // Paths first, because a table's constraint is usually at depth. Root keys are the fallback
        // for a read the path pattern cannot see — a value passed through a local variable, say.
        $targets = $this->adaptation->offendingPaths($e, $files);

        if ($targets === []) {
            $targets = $this->adaptation->offendingKeys($e, $files, $column);
        }

        // Last resort, and bounded: the keys this column's own config names. A formatter that reads
        // its value indirectly — Formatter\Money is $data[$column['name']] — has no literal
        // subscript for the search to find, but the column config still says which value it wants.
        // Bounded to that column, so unlike widening it cannot de-probe the rest of the row.
        if ($targets === []) {
            $targets = $this->columnKeys($column);
        }

        // Nothing could name the value: it reached the failure through a local alias — Formatter
        // \LicenceTypeShort reads $licence['goodsOrPsv']['id'] after assigning $licence from the
        // row, and the admin letter queue table strlen()s a $text assigned above. Widening is only
        // acceptable where the substitution still carries the marker, which is exactly the string
        // case: MARKER *is* a string, so every widened key keeps its payload and the assertion is
        // as strong as it was. Numeric and date would silently de-probe the whole row, so those give
        // up and are reported as unrenderable, which is the honest answer.
        if ($targets === [] && $type === RowProbeAdaptation::TYPE_STRING) {
            $targets = array_keys(array_filter(
                $row,
                static fn(mixed $value): bool => $value instanceof RecursiveProbe
            ));
        }

        if ($targets === []) {
            throw $e;
        }

        $rowKeys = array_keys($row);

        // Prefer targets the row already holds, since those are certainly this table's data. When
        // none of them are, the read is real anyway — the row's keys are harvested from the
        // definition's source, so a key a *formatter* reads is missing from it even though
        // production supplies it. Formatter\InternalConversationLink reads $row['createdOn'] while
        // conversations-list.table.php never spells it, and refusing to adapt there left the whole
        // table unrendered. Adding the key makes the row more like production, not less.
        $known = array_values(array_filter(
            $targets,
            static fn(string $target): bool => in_array(explode('.', $target)[0], $rowKeys, true)
        ));

        if ($known !== []) {
            $targets = $known;
        }

        // Only strings are safe to apply broadly, because MARKER is a string and every substituted
        // key keeps its payload. Anything else changes the shape of the value or drops the payload,
        // so it goes one target at a time — the search names every row read in the window, and a
        // window usually spans more than the argument that actually failed. users.table.php is the
        // case in point: array_map() rejected $row['roles'], but $row['id'] sat in the same window
        // and turning that into an array made Escape::html() refuse it outright.
        if ($type !== RowProbeAdaptation::TYPE_STRING) {
            $targets = array_slice($targets, 0, 1);
        }

        $progressed = false;

        foreach ($targets as $target) {
            if (($adapted[$target] ?? null) === $type) {
                continue;
            }

            $adapted[$target] = $type;

            if (str_contains($target, '.')) {
                $overrides[$target] = $this->adaptation->substitute($type);
            }

            $progressed = true;
        }

        if (!$progressed) {
            throw $e;
        }
    }

    /**
     * Row keys read by the formatter classes a definition names.
     *
     * The definition's own subscripts are not the whole row. A formatter reads keys the table never
     * spells — Formatter\ConditionsUndertakingsType reads $data['conditionType'], which
     * lva-conditions-undertakings.table.php never mentions — and a key missing from the row arrives
     * as null. That is not a harmless gap: Escape::html(null) returns null rather than a string, so
     * the formatter returned null into a `: string` return type and took the whole table down.
     * More importantly a missing key makes isset() false, so the branch that would leak never runs
     * and the table passes while production does not.
     *
     * @return string[]
     */
    private function formatterKeys(string $source): array
    {
        $keys = [];

        if (!preg_match_all('/use\s+(Common\\\\Service\\\\Table\\\\Formatter\\\\[A-Za-z0-9_]+)\s*;/', $source, $matches)) {
            return $keys;
        }

        foreach ($matches[1] as $class) {
            if (!class_exists($class)) {
                continue;
            }

            for ($r = new \ReflectionClass($class); $r !== false; $r = $r->getParentClass()) {
                $file = $r->getFileName();

                if ($file === false || !is_readable($file)) {
                    continue;
                }

                $formatterSource = (string)file_get_contents($file);

                if (
                    preg_match_all(
                        '/\$(?:row|data)\s*\[\s*[\'"]([A-Za-z0-9_]+)[\'"]\s*\]/',
                        $formatterSource,
                        $found
                    )
                ) {
                    $keys = array_merge($keys, $found[1]);
                }

                // Keys the formatter reads through a variable, which the pattern above cannot see.
                // Without these the guarded branch is skipped and the formatter counts as exercised
                // while the line that escapes never runs — see DynamicRowKeys.
                $keys = array_merge($keys, DynamicRowKeys::forSource($formatterSource));
            }
        }

        return $keys;
    }

    /**
     * The column config the failing formatter was handed.
     *
     * Formatters are called as format($row, $column), so the config is sitting in the trace. Taking
     * it from there rather than reconstructing it from the definition means it is the real one,
     * including whatever the definition's own closures put in it before delegating.
     *
     * @return array<string, string>
     */
    private function columnFromTrace(\Throwable $e): array
    {
        foreach ($e->getTrace() as $frame) {
            if (($frame['function'] ?? '') !== 'format') {
                continue;
            }

            $column = $frame['args'][1] ?? null;

            if (is_array($column)) {
                return array_filter($column, 'is_string');
            }
        }

        return [];
    }

    /**
     * Row keys a column config points at, by either spelling.
     *
     * 'name' and 'stack' both name a value, and both may be a "licence->organisation->name" chain
     * whose first segment is the root key.
     *
     * @param array<string, string> $column
     * @return string[]
     */
    private function columnKeys(array $column): array
    {
        $keys = [];

        foreach (['name', 'stack'] as $key) {
            if (isset($column[$key])) {
                // "vehicle->platedWeight" is a path, not a key. Answering with just "vehicle" puts
                // the substitution at the root, where the stack helper walks straight past it and
                // still arrives at null — Formatter\NumberStackValue is the worked example.
                $keys[] = implode('.', explode('->', $column[$key]));
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * Source files the key search may read: the definition, and the library code it called.
     *
     * A table's row is read in two places — inline closures in the definition itself, and the
     * formatter or type classes it names. Both are in the trace, and both are ours. Vendor frames
     * are excluded: a subscript inside Laminas is not a read of this row.
     *
     * Compared as real paths. The tests point the harness at directories built with __DIR__ and a
     * run of "..", so the paths this walks keep those segments while the ones PHP reports on an
     * exception do not — and an unresolved comparison silently matched nothing, which read as "no
     * key could be attributed" and skipped every table whose constraint lived at depth.
     *
     * @return array<string, true>
     */
    private function readableFiles(\Throwable $e, string $path): array
    {
        $library = realpath(__DIR__ . '/../../../../../../../Common/src/Common') ?: '';

        $files = [];
        $definition = realpath($path);

        if ($definition !== false) {
            $files[$definition] = true;
        }

        $candidates = [$e->getFile()];

        foreach ($e->getTrace() as $frame) {
            if (isset($frame['file'])) {
                $candidates[] = $frame['file'];
            }
        }

        foreach ($candidates as $candidate) {
            $real = realpath($candidate);

            if ($real !== false && $library !== '' && str_starts_with($real, $library)) {
                $files[$real] = true;
            }
        }

        return $files;
    }

    private function drainBuffers(int $level): string
    {
        $echoed = '';

        while (ob_get_level() > $level) {
            $echoed = (string)ob_get_clean() . $echoed;
        }

        return $echoed;
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
    private function row(string $path, string $probeValue, array $overrides = []): array
    {
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

        $keys = array_merge($keys, $this->formatterKeys($source));

        $row = [];

        foreach (array_unique($keys) as $key) {
            // Each root value knows where it sits, so an override recorded as "publication.pubDate"
            // is answered at that leaf and nowhere else.
            $row[$key] = new RecursiveProbe($probeValue, $overrides, $key);
        }

        // A definition naming one of the fixtured formatters gets that formatter's hand-written row.
        // The conversation message formatters need $row['documents'] to be an array *and* each
        // element's 'size' to be an int, which no single substitution can satisfy — the adaptation
        // loop oscillates between the two until it gives up. Whatever the fixture does not carry is
        // still a probe, and its payload-less leaves are reported as unprobed.
        return array_replace($row, RowFixtures::forSource($source));
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
    private function tableBuilder(array $directories, ?string $prefer = null): TableBuilder
    {
        $container = $this->container();

        $tableBuilder = new TableBuilder(
            $container,
            $container->get(Permission::class),
            $container->get('translator'),
            $container->get('Helper\Url'),
            [
                'tables' => [
                    'config' => $this->configLocations($directories, $prefer),
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
    private function configLocations(array $directories, ?string $prefer = null): array
    {
        $this->tableFiles($directories);

        if ($prefer === null) {
            return $this->locations;
        }

        // TableBuilder reverses this list and takes the first hit, so the way to make a given
        // directory win a name contest is to put it last.
        $locations = array_values(array_filter(
            $this->locations,
            static fn(string $location): bool => $location !== $prefer
        ));
        $locations[] = $prefer;

        return $locations;
    }

    /**
     * A builder per shadowed file, each configured so that file wins its name.
     *
     * Shadowed definitions are real, ship to production and are as likely to leak as any other, so
     * reporting them as permanently unknown would be a hole the size of however many name clashes
     * the repository accumulates. They cannot share the main builder — by definition another file
     * answers to their name there — so each gets one whose locations are ordered in its favour.
     *
     * @param string[] $directories
     * @return array<string, array{path: string, builder: TableBuilder}>
     */
    private function shadowedRenders(array $directories): array
    {
        // Snapshotted first: building a TableBuilder re-walks the tree and resets these.
        $shadowed = $this->shadowedPaths;
        $renders = [];

        foreach ($shadowed as $name => $path) {
            $renders[$name] = [
                'path' => $path,
                'builder' => $this->tableBuilder($directories, dirname($path) . '/'),
            ];
        }

        return $renders;
    }

    /**
     * The services the real formatter factories ask for. Shared with FormatterEscapingHarness.
     */
    private function container(): ServiceManager
    {
        return HarnessContainer::create();
    }
}
