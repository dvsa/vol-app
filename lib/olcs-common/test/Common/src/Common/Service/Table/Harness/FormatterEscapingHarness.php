<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Harness;

use Common\Service\Table\Formatter\FormatterPluginManager;
use Common\Service\Table\Formatter\FormatterPluginManagerInterface;

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
     * @return array{
     *     leaking: array<string, string[]>,
     *     skipped: array<string, string>,
     *     exercised: string[]
     * }
     */
    public function inspect(): array
    {
        $leaking = [];
        $skipped = [];
        $exercised = [];

        $container = HarnessContainer::create();
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

                // Output buffered for the same reason TableEscapingHarness buffers: a formatter may
                // echo rather than return, and a leak could appear there.
                $level = ob_get_level();
                ob_start();

                try {
                    $output = (string)$formatter->format($this->row($class), $this->column());
                } catch (\Throwable $e) {
                    $skipped[$short] = $e::class . ': ' . $e->getMessage();
                    continue;
                } finally {
                    $echoed = '';
                    while (ob_get_level() > $level) {
                        $echoed = (string)ob_get_clean() . $echoed;
                    }
                }

                $exercised[] = $short;

                $leaks = $this->findLeaks($output . $echoed);
                if ($leaks !== []) {
                    $leaking[$short] = $leaks;
                }
            }
        } finally {
            restore_error_handler();
        }

        ksort($leaking);
        ksort($skipped);
        sort($exercised);

        return ['leaking' => $leaking, 'skipped' => $skipped, 'exercised' => $exercised];
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
     * The column config. 'name' points at a key the row carries, so formatters that read
     * $data[$column['name']] get the marker rather than a miss.
     *
     * @return array<string, string>
     */
    private function column(): array
    {
        return ['name' => 'name'];
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
