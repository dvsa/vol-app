#!/usr/bin/php
<?php
// @codingStandardsIgnoreFile

class Translator
{
    public const EN = 'EN';

    public const EN_MARKUP = 'EN-MARKUP';

    public const CY = 'CY';

    public const CY_TRANSLATED = 'CY-TRANSLATED';

    private $translationLocation;

    private $enTranslations;

    private $cyTranslations = [];

    private $usedTranslations = [];

    private $unusedTranslations = [];

    private $filenameMap = [
        self::EN => 'en_GB.php',
        self::CY => 'cy_GB.php',
        self::CY_TRANSLATED => 'cy_GB-translated.php',
        self::EN_MARKUP => 'partials/en_GB'
    ];

    public function __construct()
    {
        die('Not required anymore' . PHP_EOL);
    }

    /**
     * Run the translator
     */
    public function run(): void
    {
        $this->translateTextTranslations();

        $this->translateMarkupTranslations();
    }

    /**
     * Translate Text Translations
     */
    protected function translateTextTranslations(): void
    {
        $this->organiseTranslations();
        $this->outputEnContent();
        $this->outputCyContent();
    }

    /**
     * Translate Markup Translations
     */
    protected function translateMarkupTranslations(): void
    {
        $this->iterateAndTranslate($this->getFilename(self::EN_MARKUP));
    }

    /**
     * Iterate a directory and translate the files
     *
     * @param $path
     */
    protected function iterateAndTranslate(string $path): void
    {
        $partials = new DirectoryIterator($path);

        // replicates file structure and nested partial includes
        foreach ($partials as $file) {
            if ($file->isDot()) {
                continue;
            }

            if ($file->isDir()) {
                $this->iterateAndTranslate($file->getPathname());
                continue;
            }

            $this->translateMarkupFile($file->getPathname());
        }
    }

    /**
     * Translate markup file
     *
     * @param $source
     */
    protected function translateMarkupFile(string $source): void
    {
        $dest = str_replace('en_GB', 'cy_GB', $source);
        $translatedDest = str_replace('en_GB', 'cy_GB-translated', $source);

        if (file_exists($translatedDest)) {
            $content = file_get_contents($translatedDest);
        } else {
            $content = str_replace('en_GB', 'cy_GB', '<p><b>Translated to Welsh</b></p>'. file_get_contents($source));
        }

        if (file_put_contents($dest, $content) === false) {
            echo 'Failed to write to ' . $dest;
        }
    }

    /**
     * Generate and output the english file
     */
    protected function outputEnContent(): void
    {
        $this->outputContent(self::EN, fn($key, $value): string => $this->formatEnRow($key, $value));
    }

    /**
     * Generate and output the welsh file
     */
    protected function outputCyContent(): void
    {
        $this->outputContent(self::CY, fn($key, $value): string => $this->formatCyRow($key, $value));
    }

    /**
     * Format the english rows
     *
     * @param $key
     * @param $value
     * @return string
     */
    protected function formatEnRow($key, $value)
    {
        return "    '" . $this->formatKey($key) . "' => '" . $this->formatValue($value) . "',";
    }

    /**
     * Format the welsh rows
     *
     * @param $key
     * @param $value
     * @return string
     */
    protected function formatCyRow($key, $value)
    {
        $value = $this->cyTranslations[$key] ?? '{WELSH} ' . $value;

        return $this->formatEnRow($key, $value);
    }

    /**
     * Save the contents to the file
     *
     * @param $locale
     * @param $callback
     *
     * @psalm-param 'CY'|'EN' $locale
     * @psalm-param Closure(mixed, mixed):string $callback
     */
    protected function outputContent(string $locale, Closure $callback): void
    {
        $content = $this->generateContent($callback);

        file_put_contents($this->getFilename($locale), $content);
    }

    /**
     * Grab the appropriate filename
     *
     * @param $locale
     *
     * @return string
     *
     * @psalm-param 'EN-MARKUP' $locale
     */
    protected function getFilename(string $locale)
    {
        return $this->translationLocation . $this->filenameMap[$locale];
    }

    /**
     * Generate the content
     *
     * @param $callback
     * @return string
     */
    protected function generateContent($callback)
    {
        $entries = [];

        foreach ($this->usedTranslations as $key => $value) {
            $entries[] = $callback($key, $value);
        }

        $entries[] = '    // Potentially unused translations';

        // Unused
        foreach ($this->unusedTranslations as $key => $value) {
            $entries[] = $callback($key, $value);
        }

        return sprintf(
            '<?php' . "\n" . '// @codingStandardsIgnoreFile' . "\n" . 'return [' . "\n" . '%s' . "\n" . '];' . "\n",
            implode("\n", $entries)
        );
    }

    /**
     * Standardise the translations
     */
    protected function organiseTranslations(): void
    {
        foreach ($this->enTranslations as $key => $value) {

            if ($this->isUsed($key)) {
                $this->usedTranslations[$key] = $value;
            } else {
                $this->unusedTranslations[$key] = $value;
            }
        }

        $this->sortTranslations($this->usedTranslations);
        $this->sortTranslations($this->unusedTranslations);
    }

    /**
     * Sort the translations alphabetically
     *
     * @param $translations
     */
    protected function sortTranslations(&$translations): void
    {
        ksort($translations);
    }

    /**
     * Format the values, remove duplicate whitespace and escape quotes
     *
     * @param $string
     * @return string
     */
    protected function formatValue($string)
    {
        return $this->escapeQuotes(preg_replace('/(\s+)/', ' ', $string));
    }

    /**
     * Format the keys
     *
     * @param $string
     * @return string
     */
    protected function formatKey($string)
    {
        return $this->escapeQuotes($string);
    }

    /**
     * Escape quotes
     *
     * @param null|string|string[] $string
     *
     * @return string|string[]
     *
     * @psalm-param array<string>|null|string $string
     *
     * @psalm-return array<string>|string
     */
    protected function escapeQuotes(array|string|null $string): array|string
    {
        return str_replace("'", "\'", $string);
    }

    /**
     * Check if the translation is used
     *
     * @param $key
     * @return bool
     */
    protected function isUsed($key)
    {
        // Tmp just return true
        return true;

//        $directories = [
//            realpath(__DIR__ . '/../Common/config/list-data/'),
//            realpath(__DIR__ . '/../Common/src/'),
//            realpath(__DIR__ . '/../Common/view/'),
//            realpath(__DIR__ . '/../../olcs-internal/module/'),
//            realpath(__DIR__ . '/../../olcs-selfserve/module/'),
//        ];
//
//        foreach ($directories as $directory) {
//            $response = shell_exec('grep -r "' . $key . '" ' . $directory);
//
//            if (!empty($response)) {
//                return true;
//            }
//        }
//
//        return false;
    }
}

$translations = new Translator(__DIR__ . '/../Common/config/language/');
$translations->run();
