#!/usr/bin/env php
<?php

declare(strict_types=1);

use Dvsa\Olcs\Api\Service\EditorJs\HtmlToEditorJsConverter;
use Dvsa\Olcs\Api\Service\EditorJs\UnconvertibleContentException;

$apiRoot = dirname(__DIR__, 2);
$projectRoot = dirname($apiRoot, 2);
require $apiRoot . '/vendor/autoload.php';

$directories = array_slice($argv, 1) ?: [
    $projectRoot . '/app/api/module/Snapshot/config/language/partials',
    $projectRoot . '/lib/olcs-common/Common/config/language/partials',
];
$converter = new HtmlToEditorJsConverter();
$records = [];
$issues = [];

foreach (declarationVariants() as $variant) {
    foreach (localesFor($variant['base_partial'], $directories) as $locale) {
        $source = findPartial($variant['base_partial'], $locale, $directories);

        try {
            $markup = composeVariant($variant, $locale, $directories);
            $blocks = $converter->convert($markup);
        } catch (InvalidArgumentException | UnconvertibleContentException $e) {
            $issues[] = ($source ?? $variant['base_partial']) . ': ' . $e->getMessage();
            continue;
        }

        if ($blocks === []) {
            $issues[] = ($source ?? $variant['base_partial']) . ': Contains no editor content';
            continue;
        }

        foreach ($blocks as $index => $block) {
            $blocks[$index]['id'] = substr(sha1($variant['reference_key'] . ':' . $index), 0, 10);
        }

        $key = $variant['reference_key'] . '|' . $locale;
        $records[$key] = [
            'reference_key' => $variant['reference_key'],
            'locale' => $locale,
            'page_name' => $variant['page_name'],
            'description' => $variant['description'],
            'content' => ['blocks' => $blocks],
        ];
    }
}

$sql = array_map(static fn (array $record): string => sqlFor($record), $records);

if ($sql !== []) {
    echo "START TRANSACTION;\n\n";
    echo implode("\n\n", $sql);
    echo "\n\nCOMMIT;\n";
}

if ($issues !== []) {
    fwrite(STDERR, "The following partials need manual migration:\n");
    fwrite(STDERR, implode("\n", $issues) . "\n");
    exit(2);
}

/**
 * @return list<array{
 *     reference_key: string,
 *     page_name: string,
 *     description: string,
 *     base_partial: string,
 *     replacements: list<string>,
 *     allowed_placeholders: int
 * }>
 */
function declarationVariants(): array
{
    $application = 'markup-application_undertakings_';
    $continuation = 'markup-continuation-declaration-';

    return [
        // New applications
        variant('application-declaration-goods-gb', 'New application – goods – GB', $application . 'GV79', ['']),
        variant(
            'application-declaration-goods-gb-lgv',
            'New application – goods – GB – light goods vehicles',
            $application . 'GV79-auth-lgv',
            [''],
        ),
        variant(
            'application-declaration-goods-gb-standard-international',
            'New application – goods – GB – standard international',
            $application . 'GV79-si',
            [''],
        ),
        variant(
            'application-declaration-goods-gb-restricted',
            'New application – goods – GB – restricted',
            $application . 'GV79-auth-restricted',
            [''],
        ),
        variant(
            'application-declaration-goods-ni-lgv',
            'New application – goods – Northern Ireland – light goods vehicles',
            $application . 'GV79-NI',
            [$application . 'GV79-auth-lgv-NI', $application . 'GV79-NI-Standard', ''],
        ),
        variant(
            'application-declaration-goods-ni-standard',
            'New application – goods – Northern Ireland – standard',
            $application . 'GV79-NI',
            [$application . 'GV79-NI-auth-other', $application . 'GV79-NI-Standard', ''],
        ),
        variant(
            'application-declaration-goods-ni-restricted',
            'New application – goods – Northern Ireland – restricted',
            $application . 'GV79-NI',
            [$application . 'GV79-NI-auth-other', '', ''],
        ),
        variant('application-declaration-psv-standard', 'New application – PSV – standard', $application . 'PSV421', ['']),
        variant(
            'application-declaration-psv-restricted',
            'New application – PSV – restricted',
            $application . 'PSV421-Restricted',
            [''],
        ),
        variant(
            'application-declaration-psv-special-restricted',
            'New application – PSV – special restricted',
            $application . 'PSV356',
        ),

        // Variations
        variant(
            'variation-declaration-goods-gb-lgv',
            'Variation – goods – GB – light goods vehicles',
            $application . 'GV81',
            ['', '', $application . 'GV81-auth-lgv', $application . 'GV81-Standard'],
        ),
        variant(
            'variation-declaration-goods-gb-standard',
            'Variation – goods – GB – standard',
            $application . 'GV81',
            ['', '', $application . 'GV81-auth-other', $application . 'GV81-Standard'],
        ),
        variant(
            'variation-declaration-goods-gb-restricted',
            'Variation – goods – GB – restricted',
            $application . 'GV81',
            ['', '', $application . 'GV81-auth-other', ''],
        ),
        variant(
            'variation-declaration-goods-ni-lgv',
            'Variation – goods – Northern Ireland – light goods vehicles',
            $application . 'GV81-NI',
            ['', '', $application . 'GV81-auth-lgv', $application . 'GV81-NI-Standard'],
        ),
        variant(
            'variation-declaration-goods-ni-standard',
            'Variation – goods – Northern Ireland – standard',
            $application . 'GV81-NI',
            ['', '', $application . 'GV81-NI-auth-other', $application . 'GV81-NI-Standard'],
        ),
        variant(
            'variation-declaration-goods-ni-restricted',
            'Variation – goods – Northern Ireland – restricted',
            $application . 'GV81-NI',
            ['', '', $application . 'GV81-NI-auth-other', ''],
        ),
        variant(
            'variation-declaration-goods-gb-upgrade',
            'Variation – goods – GB – licence upgrade',
            $application . 'GV80A',
            ['', ''],
        ),
        variant(
            'variation-declaration-goods-ni-upgrade',
            'Variation – goods – Northern Ireland – licence upgrade',
            $application . 'GV80A-NI',
            ['', ''],
        ),
        variant(
            'variation-declaration-psv-standard',
            'Variation – PSV – standard',
            $application . 'PSV430',
            ['', '', $application . 'PSV430-Standard'],
        ),
        variant(
            'variation-declaration-psv-restricted',
            'Variation – PSV – restricted',
            $application . 'PSV430',
            ['', '', ''],
        ),

        // Continuations
        variant(
            'continuation-declaration-goods-gb-standard',
            'Continuation – goods – GB – standard',
            $continuation . 'goods-gb-sn',
            [$continuation . 'goods-gb-sn-standard'],
        ),
        variant(
            'continuation-declaration-goods-gb-restricted',
            'Continuation – goods – GB – restricted',
            $continuation . 'goods-gb',
            [$continuation . 'goods-gb-operating-centres-not-lgv', ''],
        ),
        variant(
            'continuation-declaration-goods-ni-lgv',
            'Continuation – goods – Northern Ireland – light goods vehicles',
            $continuation . 'goods-ni',
            [$continuation . 'goods-operating-centres-lgv', $continuation . 'goods-ni-standard'],
        ),
        variant(
            'continuation-declaration-goods-ni-standard',
            'Continuation – goods – Northern Ireland – standard',
            $continuation . 'goods-ni',
            [$continuation . 'goods-ni-operating-centres-not-lgv', $continuation . 'goods-ni-standard'],
        ),
        variant(
            'continuation-declaration-goods-ni-restricted',
            'Continuation – goods – Northern Ireland – restricted',
            $continuation . 'goods-ni',
            [$continuation . 'goods-ni-operating-centres-not-lgv', ''],
        ),
        variant('continuation-declaration-psv-standard', 'Continuation – PSV – standard', $continuation . 'psv'),
        variant(
            'continuation-declaration-psv-restricted',
            'Continuation – PSV – restricted',
            $continuation . 'psv-restricted',
        ),
        variant(
            'continuation-declaration-psv-special-restricted',
            'Continuation – PSV – special restricted',
            $continuation . 'psv-sr',
        ),

        // Guidance displayed with the declarations
        variant('review-text', 'New application – review guidance', 'markup-review-text', allowedPlaceholders: 2),
        variant('review-text-variation', 'Variation – review guidance', 'markup-review-text-variation'),
        variant(
            'continuation-declaration-review',
            'Continuation – review guidance',
            'markup-continuation-declaration-review',
            allowedPlaceholders: 1,
        ),
        variant(
            'tma-tm-declaration',
            'Transport manager declaration',
            'markup-tma-tm_declaration',
            allowedPlaceholders: 1,
        ),
    ];
}

/**
 * @return array{
 *     reference_key: string,
 *     page_name: string,
 *     description: string,
 *     base_partial: string,
 *     replacements: list<string>,
 *     allowed_placeholders: int
 * }
 */
function variant(
    string $referenceKey,
    string $pageName,
    string $basePartial,
    array $replacements = [],
    int $allowedPlaceholders = 0,
): array {
    $description = match (true) {
        str_starts_with($referenceKey, 'application-'), $referenceKey === 'review-text' =>
            'Existing wording for the new application Review and declarations page.',
        str_starts_with($referenceKey, 'variation-'), $referenceKey === 'review-text-variation' =>
            'Existing wording for the variation Review and declarations page.',
        str_starts_with($referenceKey, 'continuation-') =>
            'Existing wording for the continuation declaration page.',
        default => 'Existing wording for the transport manager declaration page.',
    };

    return [
        'reference_key' => $referenceKey,
        'page_name' => $pageName,
        'description' => $description,
        'base_partial' => $basePartial,
        'replacements' => $replacements,
        'allowed_placeholders' => $allowedPlaceholders,
    ];
}

/** @return list<string> */
function localesFor(string $partial, array $directories): array
{
    $locales = [];

    foreach ($directories as $directory) {
        if (!is_dir($directory)) {
            throw new InvalidArgumentException('Partial directory does not exist: ' . $directory);
        }

        foreach (glob($directory . '/*/' . $partial . '.phtml') ?: [] as $file) {
            $locales[] = basename(dirname($file));
        }
    }

    $locales = array_values(array_unique($locales));
    sort($locales, SORT_STRING);

    return $locales;
}

function composeVariant(array $variant, string $locale, array $directories): string
{
    $markup = partialMarkup($variant['base_partial'], $locale, $directories);

    foreach ($variant['replacements'] as $replacement) {
        if (!str_contains($markup, '%s')) {
            throw new InvalidArgumentException('The composition has more replacements than placeholders');
        }

        $value = $replacement === '' ? '' : partialMarkup($replacement, $locale, $directories);
        $position = strpos($markup, '%s');
        $markup = substr_replace($markup, $value, $position, 2);
    }

    if (substr_count($markup, '%s') > $variant['allowed_placeholders']) {
        throw new InvalidArgumentException('The composition still contains an unresolved placeholder');
    }

    return $markup;
}

function partialMarkup(string $partial, string $locale, array $directories): string
{
    $file = findPartial($partial, $locale, $directories)
        ?? ($locale === 'en_GB' ? null : findPartial($partial, 'en_GB', $directories));

    if ($file === null) {
        throw new InvalidArgumentException(sprintf('Could not find %s for %s or en_GB', $partial, $locale));
    }

    $markup = file_get_contents($file);

    if ($markup === false) {
        throw new InvalidArgumentException('Could not read ' . $file);
    }

    if (preg_match('/<\?(?:php|=)?/i', $markup) === 1) {
        throw new InvalidArgumentException('Contains PHP in ' . $file);
    }

    return $markup;
}

function findPartial(string $partial, string $locale, array $directories): ?string
{
    foreach ($directories as $directory) {
        $file = $directory . '/' . $locale . '/' . $partial . '.phtml';

        if (is_file($file)) {
            return $file;
        }
    }

    return null;
}

function sqlFor(array $record): string
{
    $content = json_encode($record['content'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    return sprintf(
        "INSERT INTO `long_text` (`reference_key`, `locale`, `page_name`, `description`, `content`, `created_on`)\n"
        . "VALUES ('%s', '%s', '%s', '%s', '%s', NOW());",
        sqlEscape($record['reference_key']),
        sqlEscape($record['locale']),
        sqlEscape($record['page_name']),
        sqlEscape($record['description']),
        sqlEscape($content),
    );
}

function sqlEscape(string $value): string
{
    return str_replace(['\\', "'"], ['\\\\', "''"], $value);
}
