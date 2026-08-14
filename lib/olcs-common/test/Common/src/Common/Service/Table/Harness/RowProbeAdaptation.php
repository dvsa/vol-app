<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Harness;

/**
 * Learns what a probe has to be from the failure that rejected it.
 *
 * RecursiveProbe answers any key to any depth with itself, which is what lets one value drive a
 * hundred formatters without knowing their shapes. What it cannot do is satisfy a *type*:
 * number_format() wants a float, createFromFormat() wants a parseable date, implode() wants an
 * array. The alternative to adapting is recording the caller as undrivable — and that is worse than
 * it sounds. "Undrivable" is not "safe", it is "unknown", and its justification can disappear
 * silently: drop the number_format() call a year from now and the value starts flowing raw while the
 * entry sits in the skip list unchanged.
 *
 * So the constraint is learned from the failure rather than declared up front. Nothing is
 * substituted until the code actually rejects the probe, which means the adaptation disappears by
 * itself the moment the constraint does, and full marker-probing resumes with no one having to
 * remember. A hand-maintained "these keys are numeric" map would rot exactly the way the skip list
 * would.
 *
 * Most substitutions keep the payload — a plain string carries the marker, and so does an array of
 * probes. Only numeric and date values genuinely cannot, and payloadLosing() names those so a caller
 * can report them rather than quietly count them as covered.
 *
 * Shared by FormatterEscapingHarness (which drives formatters directly) and TableEscapingHarness
 * (which drives them through a rendered table). One copy on purpose: the regexes below encode a
 * dozen specific engine messages, and a second copy would drift from this one the first time either
 * caller met a new constraint.
 */
final class RowProbeAdaptation
{
    public const MARKER = TableEscapingHarness::MARKER;

    public const TYPE_STRING = 'string';
    public const TYPE_ARRAY = 'array';
    public const TYPE_NUMERIC = 'numeric';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_DATE = 'date';

    /**
     * Enough for the deepest chain observed (string -> date, when a value is first required to be a
     * string and then required to parse), with room to spare. A bound rather than a while(true):
     * constraints that contradict each other should be reported, not hang the suite.
     */
    public const MAX_ADAPTATIONS = 12;

    /**
     * How far a statement may run before the search gives up on it. Comfortably past the longest
     * argument list here (getFileList's array_map spans ten), and short enough that an unterminated
     * scan cannot swallow the rest of a method.
     */
    private const STATEMENT_MAX_LINES = 20;

    /**
     * How far above a failure to look for the assignment that aliased the row. A method body, not a
     * file: an assignment forty lines up is more likely to be a different scope than the same one.
     */
    private const ALIAS_MAX_LINES = 40;

    /** @var array<string, string[]> */
    private array $uncommented = [];

    /**
     * What the failure says the value has to be, or null when it is not about the value at all.
     *
     * Deliberately reads the engine's own wording rather than guessing from the key name: a key
     * called "amount" is not necessarily numeric, and one called "reference" sometimes is.
     */
    public function requiredType(\Throwable $e): ?string
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
     * @param array<string, true> $ownFiles files whose source may be read, as a lookup
     * @param array<string, string> $columnConfig resolves $data[$column['name']] style reads
     * @return string[]
     */
    public function offendingKeys(\Throwable $e, array $ownFiles, array $columnConfig = []): array
    {
        $sites = [[$e->getFile(), $e->getLine()]];

        foreach ($e->getTrace() as $frame) {
            if (isset($frame['file'], $frame['line'])) {
                $sites[] = [$frame['file'], $frame['line']];
            }
        }

        foreach ($sites as [$file, $line]) {
            if (!isset($ownFiles[$file]) || !is_readable($file)) {
                continue;
            }

            $lines = $this->uncommentedLines($file);

            foreach ([$this->line($lines, $line), $this->statement($lines, $line)] as $window) {
                $keys = $this->keysIn($window, $columnConfig);

                if ($keys !== []) {
                    return $keys;
                }
            }
        }

        return [];
    }

    /**
     * The same search as offendingKeys(), but keeping the whole subscript chain.
     *
     * offendingKeys() answers "which row key", which is all a formatter needs: it is handed one row
     * and substituting a root key is the only move available. A table definition reaches deeper —
     * $data['publication']['pubDate'] is a date while $data['publication']['pubStatus']['description']
     * is free text — and answering "publication" there would de-probe every sibling column to fix one.
     *
     * Kept separate rather than folded into offendingKeys() so the formatter harness, whose exercised
     * and unprobed sets are asserted exactly, keeps the behaviour it was baselined with.
     *
     * @param array<string, true> $ownFiles
     * @return string[] dotted paths, e.g. "publication.pubDate"
     */
    public function offendingPaths(\Throwable $e, array $ownFiles): array
    {
        $sites = [[$e->getFile(), $e->getLine()]];

        foreach ($e->getTrace() as $frame) {
            if (isset($frame['file'], $frame['line'])) {
                $sites[] = [$frame['file'], $frame['line']];
            }
        }

        foreach ($sites as [$file, $line]) {
            if (!isset($ownFiles[$file]) || !is_readable($file)) {
                continue;
            }

            $lines = $this->uncommentedLines($file);

            // Narrowest first, and forwards before backwards. A multi-line call puts its arguments
            // below the line PHP reports — array_map() is the case in point — while the lines above
            // may belong to a neighbouring column whose values are not at fault. users.table.php
            // showed why it matters: the backward reach picked up the previous column's
            // $row['contactDetails']['emailAddress'] and turned it into an array, which
            // Escape::html() then refused outright.
            $windows = [
                $this->line($lines, $line),
                $this->statement($lines, $line, forwardOnly: true),
                $this->statement($lines, $line),
            ];

            foreach ($windows as $window) {
                $paths = $this->pathsIn($window);

                if ($paths !== []) {
                    return $paths;
                }

                $paths = $this->aliasedPathsIn($window, $lines, $line);

                if ($paths !== []) {
                    return $paths;
                }
            }
        }

        return [];
    }

    /**
     * Paths reached through a local alias of the row.
     *
     * A formatter often copies part of the row before reading it — Formatter\LicenceTypeShort does
     * `$licence = $data['licence'] ?? $data;` and then subscripts `$licence`, so the failing
     * expression names no row variable at all. Without this the failure cannot be attributed, and
     * the only remaining move is to widen to every key, which substitutes at the root and makes the
     * *next* subscript fail — "Cannot access offset of type string on string".
     *
     * Resolving the alias instead puts the substitution exactly where the constraint is, leaving
     * every sibling value a probe.
     *
     * @param string[] $lines
     * @return string[]
     */
    private function aliasedPathsIn(string $window, array $lines, int $line): array
    {
        $paths = [];

        foreach ($this->aliasPrefixes($lines, $line) as $alias => $prefix) {
            if (
                !preg_match_all(
                    '/\$' . preg_quote($alias, '/') . '((?:\s*\[\s*[\'"][A-Za-z0-9_]+[\'"]\s*\])+)/',
                    $window,
                    $matches
                )
            ) {
                continue;
            }

            foreach ($matches[1] as $chain) {
                if (preg_match_all('/[\'"]([A-Za-z0-9_]+)[\'"]/', $chain, $segments)) {
                    $paths[] = implode('.', array_merge($prefix, $segments[1]));
                }
            }
        }

        return array_values(array_unique($paths));
    }

    /**
     * Local variables assigned from the row above a line, as alias => path prefix.
     *
     * Bounded to the lines just above the failure, so this reads the assignment that is plausibly
     * still in scope rather than every one in the file. The nearest assignment wins.
     *
     * @param string[] $lines
     * @return array<string, string[]>
     */
    private function aliasPrefixes(array $lines, int $line): array
    {
        $prefixes = [];
        $floor = max(0, $line - 1 - self::ALIAS_MAX_LINES);

        for ($i = $line - 1; $i >= $floor; $i--) {
            $matched = preg_match(
                '/\$([A-Za-z_][A-Za-z0-9_]*)\s*=\s*\$(?:row|data)((?:\s*\[\s*[\'"][A-Za-z0-9_]+[\'"]\s*\])*)/',
                $lines[$i] ?? '',
                $match
            );

            if ($matched !== 1 || isset($prefixes[$match[1]])) {
                continue;
            }

            $segments = [];

            if (preg_match_all('/[\'"]([A-Za-z0-9_]+)[\'"]/', $match[2], $found)) {
                $segments = $found[1];
            }

            $prefixes[$match[1]] = $segments;
        }

        return $prefixes;
    }

    /**
     * Every $row[...][...] chain a fragment of source reads, as a dotted path.
     *
     * @return string[]
     */
    private function pathsIn(string $window): array
    {
        if (
            !preg_match_all(
                '/\$(?:row|data)((?:\s*\[\s*[\'"][A-Za-z0-9_]+[\'"]\s*\])+)/',
                $window,
                $matches
            )
        ) {
            return [];
        }

        $paths = [];

        foreach ($matches[1] as $chain) {
            if (preg_match_all('/[\'"]([A-Za-z0-9_]+)[\'"]/', $chain, $segments)) {
                $paths[] = implode('.', $segments[1]);
            }
        }

        return array_values(array_unique($paths));
    }

    /**
     * A value satisfying the constraint, carrying the marker wherever the type permits.
     */
    public function substitute(string $type): mixed
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
     * The adaptations that cost coverage, which are the only ones worth reporting.
     *
     * String and array substitutions still carry the marker, so the assertion is as strong as it
     * was. Numeric and date ones are not, and saying so is the difference between "covered" and
     * "rendered".
     *
     * @param array<string, string> $adapted
     * @return array<string, string>
     */
    public function payloadLosing(array $adapted): array
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
     * The row keys a fragment of source reads, by either spelling.
     *
     * @param array<string, string> $columnConfig
     * @return string[]
     */
    private function keysIn(string $window, array $columnConfig): array
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
        // the caller supplied. Formatter\Money reads its amount this way, and without this the
        // literal-subscript pattern above finds nothing on the line.
        if (
            preg_match_all(
                '/\$(?:row|data)\s*\[\s*\$column\s*\[\s*[\'"]([A-Za-z0-9_]+)[\'"]\s*\]\s*\]/',
                $window,
                $matches
            )
        ) {
            $keys = [];

            foreach ($matches[1] as $columnKey) {
                if (isset($columnConfig[$columnKey])) {
                    $keys[] = $columnConfig[$columnKey];
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
    private function statement(array $lines, int $line, bool $forwardOnly = false): string
    {
        $first = $line - 1;
        $last = $line - 1;
        $floor = max(0, $line - 1 - self::STATEMENT_MAX_LINES);

        // Backwards to just after the previous statement or block boundary. A blanked comment line
        // is not a boundary — it is a hole in the middle of the statement it documents.
        while (!$forwardOnly && $first > $floor) {
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
}
