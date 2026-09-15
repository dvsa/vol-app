#!/usr/bin/php
<?php

if ($argc === 1) {
    echo "Please specify at least one argument: the path to the olcs-* respository folders\n";
    echo $argv[0] . ' PATH [CSV Ignore Keys]

';
    echo "e.g:\n        {$argv[0]} /home/developer/olcs/ Name,Email,Home,Controller\n\n";
    exit(1);
}

if (!is_dir($argv[1])) {
    echo "First argument must be a valid path to a directory!\n\n";
    exit(1);
}
// Make sure provided path has a trailing slash for later use.
$path = rtrim($argv[1], '/') . '/';

$ignoreKeys = [];
if (isset($argv[2])) {
    $ignoreKeys = str_getcsv($argv[2]);
}

$commonTranslations = loadCommonTranslations($path);
$usages = findTranslationKeyUsages($commonTranslations, $path, $ignoreKeys);
findUnusedTranslationKeys($commonTranslations, $usages);
findMismatchedCommonBackendValues($commonTranslations, $path);
getMarkupUsage($path);

/**
 * Load translations from OLCS-Common language files
 *
 * @return array
 */
function loadCommonTranslations(string $path)
{
    $commonLanguages = ['cy_NI', 'cy_GB', 'en_GB', 'en_NI'];
    $commonTranslations = [];
    foreach ($commonLanguages as $language) {
        $commonTranslations[$language] = include($path . 'olcs-common/Common/config/language/' . $language . '.php');
    }

    return $commonTranslations;
}

/**
 * Execute system grep command to find usages of each translation key
 *
 * @return array
 */
function findTranslationKeyUsages(array $translations, string $path, array $ignoreKeys)
{
    $usages = [];
    $allKeys = [];
    foreach ($translations as $langKey => $language) {
        $totalLangKeys = count($language) - count($ignoreKeys);
        echo "Processing {$totalLangKeys} keys in {$langKey} ...\n";
        $usages[$langKey] = [];
        $i = 1;
        foreach ($language as $key => $translated) {
            if (!in_array($key, $ignoreKeys)) {
                if (in_array($key, $allKeys)) {
                    $usages[$langKey][$key] = $allKeys[$key];
                } else {
                    $allKeys[$key] = [];
                    progressBar($i, $totalLangKeys);
                    $output = [];
                    $usages[$langKey][$key] = [];
                    exec('grep -rl -m 1 --exclude-dir=vendor --include=\*.php --include \*.phtml "' . $key . '" ' . $path, $output);
                    if (count($output) > 1) {
                        $usages[$langKey][$key] = array_merge($usages[$langKey][$key], $output);
                        $allKeys[$key] = array_merge($allKeys[$key], $output);
                    } else {
                        unset($usages[$langKey][$key]);
                    }
                }
            }

            ++$i;
        }

        keyStats($langKey, $usages[$langKey]);
        file_put_contents(sprintf('reports/%s_key_usage_report.json', $langKey), jsonEncodeWrap($usages[$langKey]));
    }

    return $usages;
}

/**
 * Finds markup partial filenames in PHP and Template files
 */
function getMarkupUsage($path): void
{
    echo "Generating markup partial report\n";
    $dirContents = array_diff(scandir($path . '/olcs-common/Common/config/language/partials/'), ['..', '.']);
    $markups = [];
    foreach ($dirContents as $item) {
        if (is_dir($path . '/olcs-common/Common/config/language/partials/' . $item)) {
            $markups[$item] = str_replace('.phtml', '', array_diff(scandir($path . '/olcs-common/Common/config/language/partials/' . $item), ['..', '.']));
        }
    }

    $usages = [];

    foreach ($markups as $langKeyFiles) {
        foreach ($langKeyFiles as $markupFilename) {
            $output = [];
            $usages[$markupFilename] = [];
            exec('grep -rl -m 1 --exclude-dir=vendor --include=\*.php --include \*.phtml "' . $markupFilename . '" ' . $path, $output);
            if (count($output) > 1) {
                $usages[$markupFilename] = array_merge($usages[$markupFilename], $output);
            } else {
                unset($usages[$markupFilename]);
            }
        }
    }

    file_put_contents("reports/markup-partial_statistics.json", jsonEncodeWrap($usages));
    echo "Markup partial report complete\n";
}

/**
 * Outputs and saves aggregate stats on key uses. Might be useful source for choosing ignore keys cmdline parameter
 */
function keyStats(string $langKey, array $keyArray): void
{
    $stats = [];
    foreach ($keyArray as $key => $keyUses) {
        $stats[$key] = count($keyUses);
    }

    echo "Top 20 used keys for: {$langKey}\n\n";
    arsort($stats);
    print_r(array_slice($stats, 0, 20));
    file_put_contents(sprintf('reports/%s_key_statistics.json', $langKey), jsonEncodeWrap($stats));
}

/**
 * Output and save list of keys unused in any files.
 */
function findUnusedTranslationKeys(array $translations, array $usages): void
{
    foreach (array_keys($translations) as $langKey) {
        $unusedKeys = array_diff_key($translations[$langKey], $usages[$langKey]);
        if ($unusedKeys !== []) {
            echo "Unused Keys in {$langKey}:\n";
            echo jsonEncodeWrap($unusedKeys);
            file_put_contents(sprintf('reports/%s_unused_keys.json', $langKey), jsonEncodeWrap($unusedKeys));
        }
    }
}

/**
 * Check for keys set in common and backend, which have different values.
 */
function findMismatchedCommonBackendValues(array $translations, string $path): void
{

    $moduleLanguages = [
        'Email' => ['cy_GB', 'en_GB'],
        'Snapshot' => ['cy_GB', 'en_GB', 'cy_NI', 'en_NI'],
    ];

    // Load translations into array
    foreach ($moduleLanguages as $module => $languages) {
        foreach ($languages as $language) {
            $backendTranslations[$module][$language] = include($path . 'olcs-backend/module/' . $module . '/config/language/' . $language . '.php');
        }
    }

    // Create two lists per language, one with keys that match in olcs-common and olcs-backend
    // another which holds all full key/value pair matches
    foreach ($backendTranslations as $module => $languages) {
        foreach ($languages as $langCode => $language) {
            $commonAndBackendMatchingKeys[$module . '-' . $langCode] = array_intersect_key($backendTranslations[$module][$langCode], $translations[$langCode]);
            $commonAndBackendIdenticalKeyValues[$module . '-' . $langCode] = array_intersect_assoc($backendTranslations[$module][$langCode], $translations[$langCode]);
        }
    }

    // Use the two lists above to derive which keys are duplicated, with different values in olcs-backend.
    foreach ($commonAndBackendMatchingKeys as $modLang => $matchingKeys) {
        $mismatched[$modLang] = array_diff_assoc($commonAndBackendMatchingKeys[$modLang], $commonAndBackendIdenticalKeyValues[$modLang]);
    }

    foreach ($mismatched as $lang => $mismatches) {
        if (!empty($mismatches)) {
            echo "Mismatch found between olcs-common and olcs-backend {$lang} translations.\n";
            echo "The following keys have different values\n";
            print_r(jsonEncodeWrap(array_keys($mismatches)));
            echo "\n\n";
            file_put_contents(sprintf('reports/backend_%s_mismatched_values.json', $lang), jsonEncodeWrap($mismatches));
        }
    }
}

/**
 * Wrapper for json_encode, the const option list might need to be expanded or tweaked to perfect the output, easier this way
 *
 * @return false|string
 */
function jsonEncodeWrap(array $array)
{
    return json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * Helper function to provide feedback to user as this can take a while to complete.
 *
 * @param $completed
 * @param $total
 *
 * @return void
 */
function progressBar(int $completed, int $total)
{
    if ($completed > $total) {
        return;
    }

    $percent = (double)($completed / $total);
    $bar = floor($percent * 40);

    $displayString = "\r{";
    $displayString .= str_repeat("#", $bar);
    if ($bar < 40) {
        $displayString .= str_repeat(" ", 40 - $bar);
    } else {
        $displayString .= "#";
    }

    $disp = number_format($percent * 100, 0);
    $displayString .= sprintf('} %s%%  %d/%d', $disp, $completed, $total);
    echo $displayString . '  ';
    flush();

    if ($completed === $total) {
        echo "\n\n";
    }
}
