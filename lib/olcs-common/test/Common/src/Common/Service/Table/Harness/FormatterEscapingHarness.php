<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Harness;

use Common\Service\Table\Formatter\FormatterPluginManager;
use Common\Service\Table\Formatter\FormatterPluginManagerInterface;
use Common\Service\Table\TableBuilder;
use Mockery as m;

/**
 * Calls every formatter directly with a hostile row and reports which emit it raw.
 *
 * The companion to TableEscapingHarness, which drives formatters *through* a rendered table. That
 * indirection leaves two blind spots this closes, both demonstrated rather than assumed:
 *
 *   1. A formatter reachable only from a table the probe cannot render is never executed at all.
 *   2. TableEscapingHarness builds its row by harvesting ['literal'] subscripts out of the table
 *      *definition*, so a key read only inside a formatter class is absent from the row and arrives
 *      as null. The formatter runs, but its leaking branch does not.
 *
 *      Formatter\PrinterDocumentCategory is the worked example: it interpolates
 *      $row['subCategory']['category']['description'] into an <a>, but admin-printers-exceptions
 *      .table.php never names subCategory, so isset() is false, the 'Default setting' branch is
 *      taken, and the table-level test passes a formatter that leaks in production.
 *
 * This harness harvests keys from the formatter's own source instead, so (2) cannot happen here by
 * construction. Together the two tests cover formatter classes and the composition around them
 * (inline closures, cell attributes, wrapping) — neither subsumes the other.
 */
final class FormatterEscapingHarness
{
    public const MARKER = TableEscapingHarness::MARKER;

    /**
     * Learning a probe's required type from the failure that rejected it is shared with
     * TableEscapingHarness, which drives the same formatters through a rendered table.
     */
    private RowProbeAdaptation $adaptation;

    public function __construct()
    {
        $this->adaptation = new RowProbeAdaptation();
    }

    /**
     * @return array{
     *     leaking: array<string, string[]>,
     *     skipped: array<string, string>,
     *     exercised: string[],
     *     unproven: array<string, array<string, string>>
     * }
     */
    public function inspect(): array
    {
        $leaking = [];
        $skipped = [];
        $exercised = [];
        $unproven = [];

        $container = HarnessContainer::create();

        // Formatter\TaskCheckbox's factory asks the container for 'TableBuilder'. Registered here
        // rather than in HarnessContainer because TableEscapingHarness registers the real one it
        // builds, and ServiceManager will not replace a service that already has an instance.
        $tableBuilder = m::mock(TableBuilder::class);
        $tableBuilder->shouldIgnoreMissing('');
        $container->setService('TableBuilder', $tableBuilder);

        /** @var FormatterPluginManager $plugins */
        $plugins = $container->get(FormatterPluginManager::class);

        // The probe is deliberately nonsense data, so undefined-key notices and null-argument
        // deprecations are expected noise from a formatter meeting a shape it was not written for.
        set_error_handler(static fn(): bool => true, E_WARNING | E_NOTICE | E_DEPRECATED);

        try {
            foreach ($this->formatterClasses() as $short => $class) {
                try {
                    $formatter = $plugins->get($class);
                } catch (\Throwable $e) {
                    $skipped[$short] = 'unresolvable: ' . $e::class . ': ' . $e->getMessage();
                    continue;
                }

                try {
                    $result = $this->drive($formatter, $class);
                } catch (\Throwable $e) {
                    $skipped[$short] = $e::class . ': ' . $e->getMessage();
                    continue;
                }

                $exercised[] = $short;

                $leaks = $this->findLeaks($result['output']);

                // Every value the settled run could not probe is put back one at a time, so it ends
                // up either escaped or rejected by its type — both proofs — rather than unknown. A
                // payload that reaches the output only under isolation is a leak the shared row
                // was masking.
                if ($result['unprobed'] !== []) {
                    $isolated = $this->isolate($formatter, $class, $result['unprobed'], $result);

                    if ($isolated['unproven'] !== []) {
                        $unproven[$short] = $isolated['unproven'];
                    }

                    $leaks = array_merge($leaks, $isolated['leaking']);
                }

                if ($leaks !== []) {
                    $leaking[$short] = $leaks;
                }
            }
        } finally {
            restore_error_handler();
        }

        ksort($leaking);
        ksort($skipped);
        ksort($unproven);
        sort($exercised);

        return [
            'leaking' => $leaking,
            'skipped' => $skipped,
            'exercised' => $exercised,
            'unproven' => $unproven,
        ];
    }

    /**
     * Run the formatter, adapting the row when a type constraint rejects the probe.
     *
     * A formatter that calls number_format() or parses a date cannot be driven by an object that
     * stringifies to "<script>…", so the alternative to adapting is recording it as undrivable —
     * and that is worse than it sounds. "Undrivable" is not "safe", it is "unknown", and its
     * justification can disappear silently: drop the number_format() call a year from now and the
     * value starts flowing raw while the entry sits in the skip list unchanged.
     *
     * So the constraint is learned from the failure rather than declared up front. Nothing is
     * substituted until the formatter actually rejects the probe, which means the adaptation
     * disappears by itself the moment the constraint does, and full marker-probing resumes with no
     * one having to remember. A hand-maintained "these keys are numeric" map would rot exactly the
     * way the skip list would.
     *
     * Most substitutions keep the payload — a plain string carries the marker, and so does an array
     * of probes. Only numeric and date values genuinely cannot, and rather than quietly counting
     * those as covered, isolate() puts each one back on its own to find out whether the constraint
     * is what keeps it off the page.
     *
     * @return array{
     *     output: string,
     *     unprobed: array<string, string>,
     *     adapted: array<string, string>,
     *     fixture: array<string, mixed>
     * }
     */
    private function drive(object $formatter, string $class): array
    {
        $fixture = $this->fixtures()[$class] ?? [];
        $column = $this->column();
        $adapted = [];
        $row = $this->buildRow($class, $fixture, $adapted);

        for ($attempt = 0; $attempt <= RowProbeAdaptation::MAX_ADAPTATIONS; $attempt++) {
            $result = $this->attempt($formatter, $row, $column);

            if (($e = $result['thrown']) !== null) {
                $type = $this->adaptation->requiredType($e);

                // Nothing learned, so retrying would loop on the same failure. This is where the
                // container-fidelity failures land — a missing service is not a probe problem.
                if ($type === null) {
                    throw $e;
                }

                $keys = $this->adaptation->offendingKeys($e, $this->ownFiles($class), $this->column());

                if ($keys === []) {
                    // The value could not be traced to a key: it reached the failure through a
                    // local alias, or the constraint is a return type rather than an argument.
                    //
                    // Before widening, try the keys the column config points at. A formatter that
                    // reads its value indirectly has no literal subscript to find, but it still has
                    // to say *which* value it wants, and the column config is where it says it.
                    // Formatter\NumberStackValue is the worked example: it is number_format() over
                    // StackValue, so the read happens in a helper two calls away and the failing
                    // line mentions no key at all — but the value it wants is $column['stack'].
                    // This is bounded to the two keys the config names, so unlike widening it
                    // cannot de-probe the rest of the row.
                    $keys = array_values(array_unique(array_intersect(
                        array_values($this->column()),
                        array_keys($row)
                    )));

                    // Widening to every remaining probe is only acceptable when the substitution
                    // still carries the marker, which is exactly the string and array cases — the
                    // assertion stays as strong, so nothing is lost by being imprecise. For numeric
                    // and date it would silently de-probe the whole row, so those give up instead
                    // and are reported as undrivable, which is the honest answer.
                    // Only strings widen. An array substituted into every remaining key reaches
                    // places that wanted a scalar — Escape::html() rejects arrays outright — so a
                    // failure that cannot name its key is reported rather than guessed at.
                    if ($keys === [] || ($adapted[$keys[0]] ?? null) === $type) {
                        if ($type !== RowProbeAdaptation::TYPE_STRING) {
                            throw $e;
                        }

                        $keys = array_keys(array_filter(
                            $row,
                            static fn(mixed $value): bool => $value instanceof RecursiveProbe
                        ));
                    }
                }

                $progressed = false;

                foreach ($keys as $key) {
                    if (($adapted[$key] ?? null) === $type) {
                        continue;
                    }

                    $adapted[$key] = $type;
                    $row[$key] = $this->adaptation->substitute($type);
                    $progressed = true;
                }

                if (!$progressed) {
                    throw $e;
                }

                continue;
            }

            return [
                'output' => $result['output'],
                'unprobed' => $this->unprobed($adapted, $fixture),
                'adapted' => $adapted,
                'fixture' => $fixture,
            ];
        }

        throw new \RuntimeException(sprintf(
            'adaptation did not settle after %d attempts; last row shape: %s',
            RowProbeAdaptation::MAX_ADAPTATIONS,
            implode(', ', array_map(
                static fn(string $k, string $t): string => "{$k}={$t}",
                array_keys($adapted),
                $adapted
            ))
        ));
    }

    /**
     * Put each payload-losing value back, one at a time, and report what the formatter does with it.
     *
     * See PayloadIsolation for the reasoning. The short version is that a substituted value is
     * untested, and — because one row feeds every branch the formatter takes — capable of hiding a
     * leak on a branch that never needed the substitution.
     *
     * No adaptation loop here on purpose: violating the constraint is the point, and adapting would
     * put the substitution straight back.
     *
     * Nothing is returned for a value that comes back safe, by either route. Escaped and rejected
     * are both proofs, so there is no gradation to record — only the two ways of not being safe.
     *
     * @param array<string, string> $unprobed path => type, from the settled run
     * @param array{adapted: array<string, string>, fixture: array<string, mixed>} $settled
     * @return array{unproven: array<string, string>, leaking: string[]}
     */
    private function isolate(object $formatter, string $class, array $unprobed, array $settled): array
    {
        $column = $this->column();
        $unproven = [];
        $leaking = [];

        foreach ($unprobed as $target => $type) {
            // Dropping the adaptation restores a probe at a root key; a fixture leaf has no
            // adaptation to drop, so withMarkerAt() writes the payload into the built row instead.
            $adapted = $settled['adapted'];
            unset($adapted[$target]);

            $row = $this->buildRow($class, $settled['fixture'], $adapted);
            $row = PayloadIsolation::withMarkerAt($row, $target, self::MARKER);

            $result = $this->attempt($formatter, $row, $column);

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
     * One call into the formatter, with no adaptation and nothing discarded.
     *
     * The output is kept even when the call throws, because a formatter that echoes a value and
     * only then meets a constraint has already emitted it — that is a leak, not a constraint, and
     * the buffer contents are the only thing that tells them apart.
     *
     * @param array<string, mixed> $row
     * @param array<string, string> $column
     * @return array{output: string, thrown: ?\Throwable}
     */
    private function attempt(object $formatter, array $row, array $column): array
    {
        $level = ob_get_level();
        ob_start();

        $output = '';
        $thrown = null;

        try {
            $output = (string)$formatter->format($row, $column);
        } catch (\Throwable $e) {
            $thrown = $e;
        }

        $echoed = $this->drainBuffers($level);

        return ['output' => $output . $echoed, 'thrown' => $thrown];
    }

    /**
     * The row as the adaptation state describes it.
     *
     * Shared by the adaptation loop and the isolation pass, so an isolated run differs from the
     * settled one in exactly the value under test and nothing else.
     *
     * @param array<string, mixed> $fixture
     * @param array<string, string> $adapted
     * @return array<string, mixed>
     */
    private function buildRow(string $class, array $fixture, array $adapted): array
    {
        $row = array_replace($this->row($class), $fixture);

        foreach ($adapted as $key => $type) {
            $row[$key] = $this->adaptation->substitute($type);
        }

        return $row;
    }

    /**
     * The formatter's own source files, parents included.
     *
     * Bounds the key search to code that belongs to the formatter under test: a stack frame in
     * Escape or TableBuilder may well mention $data['x'], but it is not this formatter's read.
     *
     * @return array<string, true>
     */
    private function ownFiles(string $class): array
    {
        $files = [];

        for ($r = new \ReflectionClass($class); $r !== false; $r = $r->getParentClass()) {
            $file = $r->getFileName();

            if ($file !== false) {
                $files[$file] = true;
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
     * Rows written by hand, for formatters whose payload lives at a nested leaf.
     *
     * RecursiveProbe answers any key to any depth with itself, which is what lets one probe drive a
     * hundred formatters without knowing their shapes. It cannot satisfy a *type* at depth, and the
     * adaptation loop cannot fix that for it, because the loop substitutes at root keys only:
     *
     *   ExternalConversationMessage  getSenderName(): string returns $row['createdBy'][...]['forename'],
     *                                a probe. Adapting the root to a string then makes the *next*
     *                                subscript fail — "Cannot access offset of type string on string".
     *   InternalConversationMessage  getFileList() needs $row['documents'] to be an array and
     *                                $row['documents'][n]['size'] to be an int. One root key cannot
     *                                be both, and the loop oscillated between them until it gave up.
     *
     * Both need a row with real structure, so they get one. The cost is a fixture that can drift from
     * the formatter — but it drifts *safely*: a shape that stops matching makes the formatter
     * undrivable, which fails testTheSetOfUndrivableFormattersHasNotChanged rather than passing
     * quietly. That is the same reason the skip list is asserted rather than ignored.
     *
     * Merged over the generated row rather than replacing it, so a key the formatter starts reading
     * tomorrow still arrives as a probe instead of missing.
     *
     * @return array<class-string, array<string, mixed>>
     */
    /**
     * @return array<class-string, array<string, mixed>>
     */
    private function fixtures(): array
    {
        return RowFixtures::all();
    }

    /**
     * Every value in this run that reached the formatter without the payload.
     *
     * Both sources count. An adapted key loses the marker because the formatter refused it, and a
     * fixture leaf loses it because a hand-written row has to satisfy the same constraints — a date
     * that must parse, a size that must be an int. Reporting only the first would let a fixture
     * silently de-probe a value and still read as fully covered, which is the failure mode the
     * skip list was written to avoid.
     *
     * @param array<string, string> $adapted
     * @param array<string, mixed> $fixture
     * @return array<string, string>
     */
    private function unprobed(array $adapted, array $fixture): array
    {
        $unprobed = array_merge($this->adaptation->payloadLosing($adapted), RowFixtures::gaps($fixture));
        ksort($unprobed);

        return $unprobed;
    }


    /**
     * Every formatter the application can actually resolve, short name => FQCN.
     *
     * Both halves matter. The plugin config names the ones with constructor dependencies; the rest
     * are auto-added as invokables by AbstractPluginManager when a table asks for them by class
     * name, and there are more of those than there are registered ones.
     *
     * @return array<string, class-string>
     */
    public function formatterClasses(): array
    {
        $classes = [];

        foreach (array_keys(HarnessContainer::formatterConfig()['factories'] ?? []) as $class) {
            if (is_string($class) && class_exists($class)) {
                $classes[$this->shortName($class)] = $class;
            }
        }

        $dir = __DIR__ . '/../../../../../../../Common/src/Common/Service/Table/Formatter';

        foreach ((array)scandir($dir) as $entry) {
            if (!is_string($entry) || !str_ends_with($entry, '.php')) {
                continue;
            }

            $class = 'Common\\Service\\Table\\Formatter\\' . substr($entry, 0, -4);

            if (!class_exists($class)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);

            // Factories, interfaces and abstract bases are not formatters themselves.
            if ($reflection->isAbstract() || $reflection->isInterface()) {
                continue;
            }

            if (!$reflection->implementsInterface(FormatterPluginManagerInterface::class)) {
                continue;
            }

            $classes[$this->shortName($class)] = $class;
        }

        ksort($classes);

        return $classes;
    }

    /**
     * The row handed to the formatter: every key it reads, carrying the marker.
     *
     * Keys come from the formatter's own source and from its parents, since a subclass commonly
     * reads keys declared in an abstract base (AbstractConversationMessage is the case in point).
     *
     * @return array<string, RecursiveProbe>
     */
    private function row(string $class): array
    {
        $keys = ['id', 'version', 'action', 'name'];

        for ($r = new \ReflectionClass($class); $r !== false; $r = $r->getParentClass()) {
            $file = $r->getFileName();

            if ($file === false || !is_readable($file)) {
                continue;
            }

            if (
                preg_match_all(
                    '/\$(?:row|data)\s*\[\s*[\'"]([A-Za-z0-9_]+)[\'"]\s*\]/',
                    (string)file_get_contents($file),
                    $matches
                )
            ) {
                $keys = array_merge($keys, $matches[1]);
            }
        }

        return array_fill_keys(array_unique($keys), new RecursiveProbe(self::MARKER));
    }

    /**
     * The column config.
     *
     * Both entries point at a key the row carries, so formatters that read $data[$column['name']]
     * or walk $column['stack'] reach the marker rather than a miss. StackValue and its two
     * relatives throw outright without 'stack', which is a missing fixture rather than anything
     * about the formatter.
     *
     * @return array<string, string>
     */
    private function column(): array
    {
        return ['name' => 'name', 'stack' => 'name'];
    }

    /**
     * Every place the marker reached the output without being escaped.
     *
     * @return string[]
     */
    private function findLeaks(string $output): array
    {
        $leaks = [];

        if (str_contains($output, self::MARKER)) {
            $leaks[] = 'raw marker in returned content';
        }

        if (preg_match('/="[^"]*<script>xss-probe/', $output) === 1) {
            $leaks[] = 'raw marker inside an attribute';
        }

        return $leaks;
    }

    private function shortName(string $class): string
    {
        $parts = explode('\\', $class);

        return (string)end($parts);
    }
}
