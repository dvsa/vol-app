<?php

class OlcsEngine extends TinyMCE_SpellChecker_Engine
{
    /**
     * Override parent
     * Exactly same as parent method except that line
     * "$words = self::getWords($text);" replaced with
     * "$words = static::getWords($text);"
     *
     * @param array $tinymceSpellcheckerConfig Config
     *
     * @return void
     */
    public static function processRequest($tinymceSpellcheckerConfig)
    {
        $engine = self::get($tinymceSpellcheckerConfig["engine"]);
        $engine = new $engine();
        $engine->setConfig($tinymceSpellcheckerConfig);

        header('Content-Type: application/json');
        header('Content-Encoding: UTF-8');
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $method = self::getParam("method", "spellcheck");
        $lang = self::getParam("lang", "en_US");
        $text = self::getParam("text");

        if ($method == "spellcheck") {
            try {
                if (!$text) {
                    throw new Exception("Missing input parameter 'text'.");
                }

                if (!$engine->isSupported()) {
                    throw new Exception("Current spellchecker isn't supported.");
                }

                $words = static::getWords($text);

                echo json_encode((object) array(
                    "words" => (object) $engine->getSuggestions($lang, $words)
                ));
            } catch (Exception $e) {
                echo json_encode((object) array(
                    "error" => $e->getMessage()
                ));
            }
        } else {
            echo json_encode((object) array(
                "error" => "Invalid JSON input"
            ));
        }
    }

    /**
     * Override parent method.
     * Change parent method, which words with numbers in are excluded
     *
     * @param string $text Text to split into words
     *
     * @return array Of words
     */
    public static function getWords($text)
    {
        preg_match_all('(\w{3,})u', $text, $matches);
        $words = $matches[0];

        for ($i = count($words) - 1;  $i >= 0; $i--) {
            // Exclude words with numbers ONLY in them
            if (preg_match('/^[0-9]+$/', $words[$i])) {
				array_splice($words, $i, 1);
            }
        }

        return $words;
    }
}
