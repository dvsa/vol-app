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

    private const TYPE_STRING = 'string';
    private const TYPE_ARRAY = 'array';
    private const TYPE_NUMERIC = 'numeric';
    private const TYPE_INTEGER = 'integer';
    private const TYPE_DATE = 'date';

    /**
     * Enough for the deepest chain observed (string -> date, when a value is first required to be a
     * string and then required to parse), with room to spare. A bound rather than a while(true):
     * a formatter whose constraints contradict each other should be reported, not hang the suite.
     */
    private const MAX_ADAPTATIONS = 12;

    /**
     * How far a statement may run before the search gives up on it. Comfortably past the longest
     * argument list here (getFileList's array_map spans ten), and short enough that an unterminated
     * scan cannot swallow the rest of a method.
     */
    private const STATEMENT_MAX_LINES = 20;

    /** @var array<string, string[]> */
    private array $uncommented = [];

    /**
     * @return array{
     *     leaking: array<string, string[]>,
     *     skipped: array<string, string>,
     *     exercised: string[],
     *     unprobed: array<string, array<string, string>>
     * }
     */
    public function inspect(): array
    {
        $leaking = [];
        $skipped = [];
        $exercised = [];
        $unprobed = [];

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

                if ($result['unprobed'] !== []) {
                    $unprobed[$short] = $result['unprobed'];
                }

                $leaks = $this->findLeaks($result['output']);
                if ($leaks !== []) {
                    $leaking[$short] = $leaks;
                }
            }
        } finally {
            restore_error_handler();
        }

        ksort($leaking);
        ksort($skipped);
        ksort($unprobed);
        sort($exercised);

        return [
            'leaking' => $leaking,
            'skipped' => $skipped,
            'exercised' => $exercised,
            'unprobed' => $unprobed,
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
     * of probes. Only numeric and date values genuinely cannot, and those are reported as unprobed
     * rather than quietly counted as covered.
     *
     * @return array{output: string, unprobed: array<string, string>}
     */
    private function drive(object $formatter, string $class): array
    {
        $fixture = $this->fixtures()[$class] ?? [];
        $row = array_replace($this->row($class), $fixture);
        $column = $this->column();
        $adapted = [];

        for ($attempt = 0; $attempt <= self::MAX_ADAPTATIONS; $attempt++) {
            $level = ob_get_level();
            ob_start();

            try {
                $output = (string)$formatter->format($row, $column);
            } catch (\Throwable $e) {
                $this->drainBuffers($level);

                $type = $this->requiredType($e);

                // Nothing learned, so retrying would loop on the same failure. This is where the
                // container-fidelity failures land — a missing service is not a probe problem.
                if ($type === null) {
                    throw $e;
                }

                $keys = $this->offendingKeys($e, $class);

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
                        if ($type !== self::TYPE_STRING) {
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
                    $row[$key] = $this->substitute($type);
                    $progressed = true;
                }

                if (!$progressed) {
                    throw $e;
                }

                continue;
            }

            $echoed = $this->drainBuffers($level);

            return [
                'output' => $output . $echoed,
                'unprobed' => $this->unprobed($adapted, $fixture),
            ];
        }

        throw new \RuntimeException(sprintf(
            'adaptation did not settle after %d attempts; last row shape: %s',
            self::MAX_ADAPTATIONS,
            implode(', ', array_map(
                static fn(string $k, string $t): string => "{$k}={$t}",
                array_keys($adapted),
                $adapted
            ))
        ));
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
     * What the failure says the value has to be, or null when it is not about the value at all.
     *
     * Deliberately reads the engine's own wording rather than guessing from the key name: a key
     * called "amount" is not necessarily numeric, and one called "reference" sometimes is.
     */
    private function requiredType(\Throwable $e): ?string
    {
        $message = $e->getMessage();

        return match (true) {
            // Union types are checked before any single-type pattern below, because the single-type
            // patterns match inside them: /must be of type \??array\b/ matches "must be of type
            // array|string" at the word boundary before the pipe, and would claim a union that
            // accepts a string as array-only. str_replace()'s $subject is the case in point — read
            // as array, the row value became [probe, probe] and the next Escape::html() call threw
            // "Array provided to Escape helper", which is not a type this can learn from, so the
            // whole formatter was reported undrivable on the strength of a misread union.
            //
            // String is the right reading of any union containing it: it is the only member that
            // carries the marker, so it keeps the assertion at full strength.
            str_contains($message, 'must be of type array|string'),
            str_contains($message, 'must be of type string|array') => self::TYPE_STRING,

            str_contains($message, 'must be of type int|float'),
            str_contains($message, 'must be of type ?int'),
            str_contains($message, 'must be of type ?float'),
            str_contains($message, 'Unsupported operand types') => self::TYPE_NUMERIC,

            // Plain int and float, which the patterns above do not cover: they name the union and
            // the nullable forms only. readableBytes(int $bytes) is the case in point.
            (bool)preg_match('/must be of type (int|float)\b/', $message) => self::TYPE_INTEGER,

            // A date that failed to parse has already been given a string; only a real date will do.
            // createFromFormat signals that by *returning false* rather than throwing, so the
            // failure surfaces one call later as a method call on a bool.
            str_contains($message, 'Failed to parse time string'),
            str_contains($message, 'DateMalformedStringException'),
            (bool)preg_match(
                '/Call to a member function (setTimezone|format|modify|diff|getTimestamp|setTime)\(\) on (bool|false)/',
                $message
            ) => self::TYPE_DATE,

            (bool)preg_match('/must be of type \??array\b/', $message) => self::TYPE_ARRAY,

            (bool)preg_match('/must be of type \??string/', $message),
            // Objects cannot be array keys, whatever __toString says.
            str_contains($message, 'Cannot access offset of type') => self::TYPE_STRING,

            default => null,
        };
    }

    /**
     * Which row keys the failing expression touched.
     *
     * Located from the stack rather than by adapting every key, so a single numeric column does not
     * de-probe the whole row. Precision matters in both directions here: naming too few keys stalls
     * the adaptation, and naming too many de-probes values that were never at fault.
     *
     * Two passes per site, narrowest first:
     *
     *   1. the failing line alone
     *   2. the failing *statement*, which is where a multi-line call puts its arguments
     *
     * The line is tried first because a statement can mention several keys while only one of them
     * is at fault. Formatter\InternalConversationLink is the worked example: str_replace() rejects
     * $row['userContextStatus'] on one line of a sprintf() whose neighbouring line reads
     * $row['subject'], and adapting both turned a value the formatter escapes correctly into an
     * array, which Escape::html() then refused outright.
     *
     * @return string[]
     */
    private function offendingKeys(\Throwable $e, string $class): array
    {
        $files = [];

        for ($r = new \ReflectionClass($class); $r !== false; $r = $r->getParentClass()) {
            $file = $r->getFileName();

            if ($file !== false) {
                $files[$file] = true;
            }
        }

        $sites = [[$e->getFile(), $e->getLine()]];

        foreach ($e->getTrace() as $frame) {
            if (isset($frame['file'], $frame['line'])) {
                $sites[] = [$frame['file'], $frame['line']];
            }
        }

        foreach ($sites as [$file, $line]) {
            if (!isset($files[$file]) || !is_readable($file)) {
                continue;
            }

            $lines = $this->uncommentedLines($file);

            foreach ([$this->line($lines, $line), $this->statement($lines, $line)] as $window) {
                $keys = $this->keysIn($window);

                if ($keys !== []) {
                    return $keys;
                }
            }
        }

        return [];
    }

    /**
     * The row keys a fragment of source reads, by either spelling.
     *
     * @return string[]
     */
    private function keysIn(string $window): array
    {
        if (
            preg_match_all(
                '/\$(?:row|data)\s*\[\s*[\'"]([A-Za-z0-9_]+)[\'"]\s*\]/',
                $window,
                $matches
            )
        ) {
            return array_values(array_unique($matches[1]));
        }

        // $data[$column['name']] — the key is indirect, so resolve it through the column config
        // the harness supplied. Formatter\Money reads its amount this way, and without this the
        // literal-subscript pattern above finds nothing on the line.
        if (
            preg_match_all(
                '/\$(?:row|data)\s*\[\s*\$column\s*\[\s*[\'"]([A-Za-z0-9_]+)[\'"]\s*\]\s*\]/',
                $window,
                $matches
            )
        ) {
            $column = $this->column();
            $keys = [];

            foreach ($matches[1] as $columnKey) {
                if (isset($column[$columnKey])) {
                    $keys[] = $column[$columnKey];
                }
            }

            if ($keys !== []) {
                return array_values(array_unique($keys));
            }
        }

        return [];
    }

    /**
     * @param string[] $lines
     */
    private function line(array $lines, int $line): string
    {
        return $lines[$line - 1] ?? '';
    }

    /**
     * The whole statement containing a line, in both directions.
     *
     * A multi-line call puts the failing argument on a different line from the one PHP reports, and
     * which side it lands on depends on the call. Both directions are needed:
     *
     *   forwards   array_map() is reported at its own line and names $row['documents'] eight lines
     *              below, in AbstractConversationMessage::getFileList()
     *   backwards  ->setTimezone() is reported at the line the arrow sits on, one line *below* the
     *              createFromFormat() argument that actually failed, in every conversation formatter
     *
     * Bounded at both ends by statement boundaries rather than by a line count, so the window grows
     * to fit the call instead of being a guess. Pass 1 in offendingKeys() tries the failing line by
     * itself first, so widening to the statement here only ever happens when the narrow answer came
     * back empty.
     *
     * @param string[] $lines
     */
    private function statement(array $lines, int $line): string
    {
        $first = $line - 1;
        $last = $line - 1;
        $floor = max(0, $line - 1 - self::STATEMENT_MAX_LINES);

        // Backwards to just after the previous statement or block boundary. A blanked comment line
        // is not a boundary — it is a hole in the middle of the statement it documents.
        while ($first > $floor) {
            $previous = rtrim($lines[$first - 1] ?? '');

            if ($previous !== '' && preg_match('/[;{}:]$/', $previous) === 1) {
                break;
            }

            $first--;
        }

        $ceiling = min($line - 1 + self::STATEMENT_MAX_LINES, count($lines) - 1);

        while ($last < $ceiling && !str_ends_with(rtrim($lines[$last]), ';')) {
            $last++;
        }

        return implode("\n", array_slice($lines, $first, $last - $first + 1));
    }

    /**
     * A file's lines with comments blanked out, numbering preserved.
     *
     * Comments are prose about the code, and prose quotes the code it describes. The comment above
     * AbstractConversationMessage's date conversion contains the literal $data["createdOn"], so a
     * plain text scan attributed an unrelated failure in ExternalConversationMessage::getSenderName()
     * to createdOn, adapted that key, changed nothing, and stalled — reporting the formatter as
     * undrivable on the strength of a match inside a sentence.
     *
     * Rebuilt through the tokeniser rather than by regex, since a regex for "not inside a string
     * literal" is the same problem one level down. Comment tokens keep their newlines so every
     * remaining line stays where it was.
     *
     * @return string[]
     */
    private function uncommentedLines(string $file): array
    {
        if (isset($this->uncommented[$file])) {
            return $this->uncommented[$file];
        }

        $source = (string)file_get_contents($file);
        $stripped = '';

        foreach (token_get_all($source) as $token) {
            if (!is_array($token)) {
                $stripped .= $token;
                continue;
            }

            $stripped .= match ($token[0]) {
                T_COMMENT, T_DOC_COMMENT => str_repeat("\n", substr_count($token[1], "\n")),
                default => $token[1],
            };
        }

        return $this->uncommented[$file] = explode("\n", $stripped);
    }

    /**
     * A value satisfying the constraint, carrying the marker wherever the type permits.
     */
    private function substitute(string $type): mixed
    {
        return match ($type) {
            // Both keep the payload: a plain string *is* the marker, and the array holds probes.
            self::TYPE_STRING => self::MARKER,
            self::TYPE_ARRAY => [new RecursiveProbe(self::MARKER), new RecursiveProbe(self::MARKER)],

            // Neither can. A number or a timestamp that also contains "<script>" does not exist,
            // which is the honest limit of this approach rather than an oversight.
            self::TYPE_NUMERIC => 1234.56,
            // Separate from the above because a float fails an int parameter under strict_types,
            // and every formatter in this library declares it. Positive, because the one int
            // parameter here feeds log() in readableBytes().
            self::TYPE_INTEGER => 1234,
            // ATOM, because that is what the conversation formatters pass to createFromFormat, and
            // it is also parseable by new DateTime() for the ones that use that instead.
            self::TYPE_DATE => '2020-01-01T00:00:00+00:00',

            default => self::MARKER,
        };
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
    private function fixtures(): array
    {
        $marker = self::MARKER;
        $person = ['forename' => $marker, 'familyName' => $marker];

        // The two message formatters read the same row; only their templates differ.
        $conversationMessage = [
            'createdOn' => '2023-08-12T12:00:00+00:00',
            'createdBy' => [
                'id' => 1,
                'loginId' => $marker,
                // Non-empty, so the caseworker footer branch renders rather than being skipped.
                'team' => $marker,
                'contactDetails' => ['person' => $person],
            ],
            'messagingContent' => ['text' => $marker],
            'documents' => [
                ['id' => $marker, 'description' => $marker, 'size' => 2048],
            ],
            'userMessageReads' => [
                [
                    'createdOn' => '2023-08-12T13:00:00+00:00',
                    // A different id from createdBy above, or getFirstReadBy() discards the read as
                    // the sender's own and returns nothing.
                    'user' => ['id' => 2, 'contactDetails' => ['person' => $person]],
                ],
            ],
        ];

        return [
            \Common\Service\Table\Formatter\ExternalConversationMessage::class => $conversationMessage,
            \Common\Service\Table\Formatter\InternalConversationMessage::class => $conversationMessage,
        ];
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
        $unprobed = array_merge($this->payloadLosing($adapted), $this->fixtureGaps($fixture));
        ksort($unprobed);

        return $unprobed;
    }

    /**
     * The fixture leaves that do not carry the marker, by dotted path.
     *
     * @param array<array-key, mixed> $fixture
     * @return array<string, string>
     */
    private function fixtureGaps(array $fixture, string $prefix = ''): array
    {
        $gaps = [];

        foreach ($fixture as $key => $value) {
            $path = $prefix === '' ? (string)$key : $prefix . '.' . $key;

            if (is_array($value)) {
                $gaps = array_merge($gaps, $this->fixtureGaps($value, $path));
                continue;
            }

            if ($value === self::MARKER) {
                continue;
            }

            $gaps[$path] = match (true) {
                is_int($value) || is_float($value) => self::TYPE_NUMERIC,
                is_string($value) && strtotime($value) !== false => self::TYPE_DATE,
                default => 'literal',
            };
        }

        return $gaps;
    }

    /**
     * The adaptations that cost coverage, which are the only ones worth reporting.
     *
     * String and array substitutions still carry the marker, so the assertion is as strong as it
     * was. Numeric and date ones are not, and saying so is the difference between "covered" and
     * "rendered".
     *
     * @param array<string, string> $adapted
     * @return array<string, string>
     */
    private function payloadLosing(array $adapted): array
    {
        $lost = array_filter(
            $adapted,
            static fn(string $type): bool => in_array(
                $type,
                [self::TYPE_NUMERIC, self::TYPE_INTEGER, self::TYPE_DATE],
                true
            )
        );

        ksort($lost);

        return $lost;
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
