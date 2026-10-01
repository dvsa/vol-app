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
    echo "SET NAMES utf8mb4;\n\n";
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

    $markup = prepareKnownPartial($partial, $locale, $markup);

    if (preg_match('/<\?(?:php|=)?/i', $markup) === 1) {
        throw new InvalidArgumentException('Contains PHP in ' . $file);
    }

    return $markup;
}

function prepareKnownPartial(string $partial, string $locale, string $markup): string
{
    if ($locale !== 'en_GB') {
        return $markup;
    }

    if ($partial === 'markup-review-text') {
        return (string) preg_replace(
            '/<\?php\s+echo \$this->escapeHtml\(\$this->translate\(\'link\.opens-new-window\'\)\);\s*\?>/',
            '(opens in new tab)',
            $markup,
        );
    }

    if ($partial !== 'markup-tma-tm_declaration') {
        return $markup;
    }

    $translations = tmDeclarationTranslations();

    $markup = (string) preg_replace_callback(
        '/echo \$this->escapeHtml\(\$this->translate\(\'([^\']+)\'\)\);/',
        static function (array $matches) use ($translations): string {
            $key = $matches[1];

            if (!isset($translations[$key])) {
                throw new InvalidArgumentException('Missing transport manager translation: ' . $key);
            }

            return $translations[$key];
        },
        $markup,
    );

    return (string) preg_replace('/<\?php|\?>/', '', $markup);
}

/** @return array<string, string> */
function tmDeclarationTranslations(): array
{
    return [
        'tma-tm-declaration-list-subheader' => <<<'HTML'
<ul>
<li>manage, audit and review compliance systems, ensuring they are effective;</li>
<li>review prohibition shortcomings and/or annual test failures;</li>
<li>ensure relevant changes are notified in accordance with operator licence requirements;</li>
<li>keep up to date on relevant changes in standards and legislation.</li>
</ul>
HTML,
        'tma-tm-declaration-drivers-administration' => <<<'HTML'
<h4 class="govuk-heading-s">Drivers - administration</h4>
<ul class="list--bullet">
<li>ensure drivers hold the appropriate licence for the vehicle they are driving (including non-GB vocational drivers from EU member states who are required to register their driving licences with DVLA within 12 months of being resident);</li>
<li>ensure regular checks are carried out on drivers’ licences (usually every 3 months);</li>
<li>ensure vocational drivers hold a valid driver CPC qualification (DQC);</li>
<li>ensure all drivers hours records are kept for a period of no less than 12 months and made available upon request;</li>
<li>ensure all working time records are kept for a period of no less than 24 months and made available upon request;</li>
<li>ensure relevant declarations are posted for drivers in EU Member States.</li>
</ul>
HTML,
        'tma-tm-declaration-drivers-management' => <<<'HTML'
<p>
<h4 class="govuk-heading-s">Drivers - management</h4>
<ul class="list--bullet">
<li>ensure compliance with driving hours rules (EU or Domestic Hours rules);</li>
<li>ensure drivers are recording their duty, driving time and rest breaks on the appropriate equipment or in drivers hours books and returned for inspection as required;</li>
<li>where appropriate, download and store data from the vehicle digital tachograph unit (at least every 90 days) and from the drivers’ tachograph smart cards (at least every 28 days).</li>
<li>ensure drivers’ hours records are retained and are available to be produced during the relevant period;</li>
<li>ensure records are retained for the purposes of the Working Time Directive (WTD) and they are available to be produced during the relevant period;</li>
<li>ensure drivers are adequately trained and competent to operate all relevant vehicles and equipment;</li>
<li>contribute to relevant training and subsequent disciplinary processes as required.</li>
</ul>
</p>
HTML,
        'tma-tm-declaration-drivers-operations' => <<<'HTML'
<p></p>
<h4 class="govuk-heading-s">Drivers - operations</h4>
<ul class="list--bullet">
<li>ensure drivers are completing and returning their driver defect reporting sheets and that defects are recorded correctly;</li>
<li>ensure all drivers and mobile workers take adequate breaks and periods of daily and weekly rest (as per the relevant regulations which apply).</li>
</ul>
HTML,
        'tma-tm-declaration-vehicle-administration' => <<<'HTML'
<p>
<h4 class="govuk-heading-s">Vehicle - administration</h4>
<ul class="list--bullet">
<li>ensure vehicle maintenance records are retained for a period of no less than 15 months and made available upon request;</li>
<li>ensure vehicles are specified as required and that operator licence discs are current and displayed correctly</li>
<li>ensure sufficient contingency within the level of authority;</li>
<li>ensure vehicle payloads notifications are correct, height indicators are fitted and correct, and tachograph calibrations are up to date and displayed;</li>
<li>ensure there are up to date certificates of insurance indemnifying company cars, commercial vehicles and plant;</li>
<li>ensure a suitable maintenance planner is completed and displayed appropriately, setting preventative maintenance inspection dates at least 6 months in advance and to include the Annual Test and other testing or calibration dates.</li>
</ul>
</p>
HTML,
        'tma-tm-declaration-vehicle-management' => <<<'HTML'
<p>
<h4 class="govuk-heading-s">Vehicle - management</h4>
<ul class="list--bullet">
<li>ensure vehicles and trailers are kept in a fit and roadworthy condition;</li>
<li>ensure that reported defects are either recorded in writing or in a format which is readily accessible and repaired promptly;</li>
<li>ensure that vehicles and trailers that are not roadworthy are taken out of service;</li>
<li>ensure that vehicles and towed equipment are made available for safety inspections, service, repair and statutory testing;</li>
<li>ensure that safety inspections and other statutory testing are carried out within the notified O-licence maintenance intervals (ISO weeks);</li>
<li>liaise with maintenance contractors, manufacturers, hire companies and dealers, as might be appropriate and to make certain vehicles and trailers are serviced in accordance with manufacturer recommendations;</li>
<li>ensure the security of vehicles so that they can only be operated under the authority of the operator.</li>
</ul>
</p>
HTML,
        'tma-tm-declaration-licence-administration' => <<<'HTML'
<p>
<h4 class="govuk-heading-s">Licence - administration</h4>
<ul class="list--bullet">
<li>ensure the traffic commissioner is made aware of any relevant matters within 28 days including convictions and prosecutions of the transport manager(s) or drivers and also of my own resignation should I leave the employment of the operator.</li>
</ul>
</p>
HTML,
        'tma-tm-declaration-internal' => <<<'HTML'
<p>
<h3 class="govuk-heading-m">Internal transport manager’s declaration</h3>
<p class="govuk-body">I confirm that:</p>
<ul class="list--bullet">
<li>I am resident in the United Kingdom;</li>
<li>I shall effectively and continuously manage the transport activities of the licence holder/applicant;</li>
<li>I have a genuine link to the licence holder/applicant;</li>
</ul>
</p>
HTML,
        'tma-tm-declaration-external' => <<<'HTML'
<p>
<h3 class="govuk-heading-m">External transport manager’s declaration</h3>
<p class="govuk-body">I confirm that:</p>
<ul class="list--bullet">
<li>I am resident in the United Kingdom;</li>
<li>I shall perform my tasks solely in the interests of the licence holder/applicant;</li>
<li>I shall be the transport manager for a maximum of 4 operators, with a combined maximum total fleet of 50 vehicles; and</li>
<li>I have a contract with the licence holder/applicant which specifies the tasks I must perform as transport manager.</li>
</ul>
</p>
HTML,
        'tma-tm-declaration-warning' => <<<'HTML'
<p class="govuk-body">
<b>Should I fail to meet any of the above requirements I understand that the traffic commissioner has the power to disqualify me from being a transport manager in any European Union country. I also understand that I do not become a transport manager for the licence(s) named until this is confirmed in writing by the Traffic Commissioner.</b>
</p>
HTML,
    ];
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
