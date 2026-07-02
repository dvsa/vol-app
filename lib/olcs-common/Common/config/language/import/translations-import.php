#!/usr/bin/php
<?php

if ($argc === 1) {
    echo "Please specify at least one argument: the path to the olcs-* repository folders\n";
    echo $argv[0] . ' PATH

';
    echo "e.g:\n        {$argv[0]} /home/developer/olcs/\n\n";
    echo "You can also pass 'clear' as a second argument to add SQL to clear the tables translation_key, translation_key_text, translation_key_category_link and translation_key_tag_link\n\n";
    exit(1);
}

if (!is_dir($argv[1])) {
    echo "First argument must be a valid path to a directory!\n\n";
    exit(1);
}
// Make sure provided path has a trailing slash for later use.
$path = rtrim($argv[1], '/') . '/';

$clearTables = false;

if ($argc === 3 && $argv[2] === 'clear') {
    $clearTables = true;
} elseif ($argc === 3) {
    echo "If provided second argument must be 'clear'. Default is not to clear. This options specifies if the target tables translation_key, translation_key_text, translation_key_category_link and translation_key_tag_link should be cleared before the INSERTs are performed";
}

$commonTranslations = loadCommonTranslations($path);
$backendTranslations = loadBackendTranslations($path);
$backendEmailTranslations = loadBackendEmailTranslations($path);
$mergedTranslations = [];

foreach (array_keys($commonTranslations) as $langKey) {
    $mergedTranslations[$langKey] = array_merge($commonTranslations[$langKey], $backendTranslations[$langKey], $backendEmailTranslations[$langKey]);
}

$mergedTranslations = removeIdenticalWelshEnglish($mergedTranslations);


generateImportSql($mergedTranslations, $clearTables);

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
 * Load translations from OLCS-backend language files
 *
 * @return array
 */
function loadBackendTranslations(string $path)
{
    $backendLanguages = ['cy_NI', 'cy_GB', 'en_GB', 'en_NI'];
    $backendTranslations = [];
    foreach ($backendLanguages as $language) {
        $backendTranslations[$language] = include($path . 'olcs-backend/module/Snapshot/config/language/' . $language . '.php');
    }

    return $backendTranslations;
}

/**
 * Load translations from OLCS-backend email language files
 *
 * @return array
 */
function loadBackendEmailTranslations(string $path)
{
    $backendEmailLanguages = ['cy_GB', 'en_GB'];
    $backendEmailTranslations = [];
    foreach ($backendEmailLanguages as $language) {
        $backendEmailTranslations[$language] = include($path . 'olcs-backend/module/Email/config/language/' . $language . '.php');
    }

    $backendEmailTranslations['en_NI'] = [];
    $backendEmailTranslations['cy_NI'] = [];
    return $backendEmailTranslations;
}

/**
 * Remove any welsh translations identical to the english version
 *
 * @return array
 */
function removeIdenticalWelshEnglish(array $mergedTranslations)
{
    foreach ($mergedTranslations as $langKey => $translations) {
        if ($langKey != 'en_GB') {
            foreach ($translations as $transKey => $translatedText) {
                if (!array_key_exists($transKey, $mergedTranslations['en_GB'])) {
                    continue;
                }
                if ($mergedTranslations['en_GB'][$transKey] != $translatedText) {
                    continue;
                }
                unset($mergedTranslations[$langKey][$transKey]);
            }
        }
    }

    return $mergedTranslations;
}

/**
 * Generate SQL for the import which developer can use as part of an ETL patch.
 *
 * @return void
 */
function generateImportSql(array $translations, bool $clearTables)
{
    $languageMap = [
        'en_GB' => 1,
        'cy_GB' => 2,
        'en_NI' => 3,
        'cy_NI' => 4,
    ];

    // Establish a connection to properly escape values used in the generated INSERTS
    $mysqli = new mysqli("192.168.149.12", "root", "password", "olcs_be");

    /* check connection */
    if (mysqli_connect_errno() !== 0) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

    // If clear was specified, empty the translation tables before doing the INSERTs.
    $sql = $clearTables ? ["DELETE FROM translation_key_category_link;\nDELETE FROM translation_key_tag_link;\nDELETE FROM translation_key_text;\nDELETE FROM translation_key;\n"] : [];

    foreach ($translations as $langKey => $language) {
        foreach ($language as $key => $translatedText) {
            $escapedKey = $mysqli->real_escape_string($key);
            $escapedTranslation = $mysqli->real_escape_string($translatedText);
            $sql[] = "INSERT IGNORE INTO translation_key (id, description, created_by, created_on) VALUES ('{$escapedKey}', '{$escapedKey}', 1, NOW());\n";
            $sql[] = "INSERT IGNORE INTO translation_key_text (language_id, translation_key_id, translated_text, created_by, created_on) VALUES ('{$languageMap[$langKey]}', '{$escapedKey}', '{$escapedTranslation}', 1, NOW());\n";
        }
    }

    file_put_contents("translationKeyImport.sql", $sql);
    echo "\n\nSQL output to translationKeyImport.sql in the current directory\n\n";
}
