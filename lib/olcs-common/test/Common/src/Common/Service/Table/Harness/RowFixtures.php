<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Harness;

/**
 * Rows written by hand, for formatters whose payload lives at a nested leaf.
 *
 * RecursiveProbe answers any key to any depth with itself, which is what lets one probe drive a
 * hundred formatters without knowing their shapes. It cannot satisfy a *type* at depth, and the
 * adaptation loop cannot fix that for it, because the loop substitutes at a path it can name:
 *
 *   ExternalConversationMessage  getSenderName(): string returns $row['createdBy'][...]['forename'],
 *                                a probe. Adapting the root to a string then makes the *next*
 *                                subscript fail — "Cannot access offset of type string on string".
 *   InternalConversationMessage  getFileList() needs $row['documents'] to be an array and
 *                                $row['documents'][n]['size'] to be an int, the latter read through
 *                                a local alias inside an array_map closure that no subscript search
 *                                can attribute. One root key cannot be both, and the loop oscillates
 *                                between them until it gives up.
 *
 * Both need a row with real structure, so they get one. The cost is a fixture that can drift from
 * the formatter — but it drifts *safely*: a shape that stops matching makes the formatter
 * undrivable, which fails the undrivable-set assertion rather than passing quietly. That is the
 * same reason the skip list is asserted rather than ignored.
 *
 * Shared by both harnesses. FormatterEscapingHarness applies a fixture when it drives the formatter
 * directly; TableEscapingHarness applies it when a table definition names that formatter, which is
 * how messages-list, messages-view and messages render at all.
 */
final class RowFixtures
{
    /**
     * @return array<class-string, array<string, mixed>>
     */
    public static function all(): array
    {
        $marker = TableEscapingHarness::MARKER;
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
     * The fixtures for every formatter a table definition names, merged.
     *
     * Matched on the short class name, which is how a definition spells it — both in the `use`
     * import and at the `'formatter' => X::class` that follows.
     *
     * @return array<string, mixed>
     */
    public static function forSource(string $source): array
    {
        $fixture = [];

        foreach (self::all() as $class => $row) {
            $short = substr((string)strrchr($class, '\\'), 1);

            if (preg_match('/\b' . preg_quote($short, '/') . '\b/', $source) === 1) {
                $fixture = array_replace($fixture, $row);
            }
        }

        return $fixture;
    }

    /**
     * The fixture leaves that do not carry the marker, by dotted path.
     *
     * A hand-written row has to satisfy the same constraints the adaptation does — a date that must
     * parse, a size that must be an int — so it loses the payload in the same places. Reporting only
     * the adapted keys would let a fixture silently de-probe a value and still read as fully
     * covered, which is the failure mode the skip list was written to avoid.
     *
     * @param array<array-key, mixed> $fixture
     * @return array<string, string>
     */
    public static function gaps(array $fixture, string $prefix = ''): array
    {
        $gaps = [];

        foreach ($fixture as $key => $value) {
            $path = $prefix === '' ? (string)$key : $prefix . '.' . $key;

            if (is_array($value)) {
                $gaps = array_merge($gaps, self::gaps($value, $path));
                continue;
            }

            if ($value === TableEscapingHarness::MARKER) {
                continue;
            }

            $gaps[$path] = match (true) {
                is_int($value) || is_float($value) => RowProbeAdaptation::TYPE_NUMERIC,
                is_string($value) && strtotime($value) !== false => RowProbeAdaptation::TYPE_DATE,
                default => 'literal',
            };
        }

        return $gaps;
    }
}
