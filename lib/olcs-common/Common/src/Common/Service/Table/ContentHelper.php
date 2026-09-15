<?php

/**
 * Content Helper
 *
 * Helps with rendering of content and partials (For Table Builder)
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Table;

/**
 * Content Helper
 *
 * Helps with rendering of content and partials (For Table Builder)
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

class ContentHelper
{
    /**
     * The location of the partials
     *
     * @var string
     */
    private $location;

    /**
     * Cached partials
     *
     * @var array
     */
    private $partials = [];

    /**
     * @var \Laminas\I18n\Translator\TranslatorInterface
     */
    private $translator;

    /**
     * @var \Laminas\Escaper\Escaper
     */
    private $escaper;

    /**
     * Pass in the location of the partials
     *
     * @param string $location
     * @param object $object
     */
    public function __construct(
        $location = '', /**
         * $object to be used in scope
         */
        private $object = null
    ) {
        $this->location = rtrim($location, '/') . '/';

        if ($this->object !== null && method_exists($this->object, 'getTranslator')) {
            $this->setTranslator($this->object->getTranslator());
        }

        $escaper = new \Laminas\Escaper\Escaper('utf-8');
        $this->setEscaper($escaper);
    }

    /**
     * Get the escaper
     *
     * @return \Laminas\Escaper\Escaper
     */
    public function getEscaper()
    {
        return $this->escaper;
    }

    /**
     * Set the escaper
     *
     * @param \Laminas\Escaper\Escaper $escaper
     */
    public function setEscaper($escaper): static
    {
        $this->escaper = $escaper;
        return $this;
    }

    /**
     * @return \Laminas\I18n\Translator\TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param \Laminas\I18n\Translator\TranslatorInterface $translator
     */
    public function setTranslator($translator): void
    {
        $this->translator = $translator;
    }

    /**
     * Wrapper method to call main translator. Translate a message using the given text domain and locale.
     *
     * @param string $message
     * @param string $textDomain
     * @param string $locale
     * @return string
     */
    public function translate($message, $textDomain = 'default', $locale = null)
    {
        return $this->getTranslator()->translate($message, $textDomain, $locale);
    }

    /**
     * Render layout
     *
     * @param string $name
     * @return string
     */
    public function renderLayout($name)
    {
        $partialFile = $this->location . 'layouts/' . $name . '.phtml';

        if (!file_exists($partialFile)) {
            throw new \Exception('Partial not found: ' . $partialFile);
        }

        ob_start();
        require($partialFile);
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Render an attribute string from an array
     *
     * @param array $attrs
     * @return string
     */
    public function renderAttributes($attrs)
    {
        $attributes = [];

        foreach ($attrs as $name => $value) {
            $attributes[] = $name .= '="' . self::escapeAttributeValue($value) . '"';
        }

        return implode(' ', $attributes);
    }

    /**
     * Escape a value for interpolation into a double-quoted HTML attribute.
     *
     * Deliberately not Laminas' escapeHtmlAttr: that targets *unquoted* attribute contexts, so it
     * also encodes spaces and slashes. Every attribute built in the table renderer is quoted, where
     * the only way out is a quote character — so encoding the HTML special characters is sufficient
     * and leaves space-separated class lists and URL paths readable.
     *
     * @param mixed $value
     * @return string
     */
    public static function escapeAttributeValue($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Replace vars into content
     *
     * @param string $content
     * @param array $vars
     * @param bool $escapeValues Escape substituted values. Use when $content is a developer-authored
     *                           template and $vars are row data — the template stays raw, the values
     *                           do not. Leave false when a value is itself rendered markup, such as
     *                           the {{content}} of a <td> that a formatter has already produced.
     * @return string
     */
    public function replaceContent($content, $vars = [], $escapeValues = false)
    {
        $content = $this->replacePartials($content);

        foreach ($vars as $key => $val) {
            if (is_string($val) || is_numeric($val)) {
                $replacement = $escapeValues ? self::escapeValue($val) : (string)$val;
                $content = str_replace('{{' . $key . '}}', $replacement, $content);
            }
        }

        return preg_replace('/(\{\{[a-zA-Z0-9\/\[\]]+\}\})/', '', $content);
    }

    /**
     * Whether a value has a string form worth rendering.
     *
     * Ask this before escaping a bare row value, because escaping converts to string and PHP's
     * conversion of an array is the literal word "Array" — a cell that should be empty ends up
     * describing its own contents. A column naming a to-many field holds an array whenever the
     * collection is empty, so this is ordinary data rather than a corrupt row.
     *
     * Rendering nothing is what such a column has always done: the value used to travel as far as
     * replaceContent(), which substitutes strings and numbers and drops the rest, leaving the
     * placeholder to be swept away. Escaping at the cell moved the string conversion in front of
     * that, which is what this restores.
     *
     * @param mixed $value
     * @return bool
     */
    public static function hasStringForm($value)
    {
        return is_scalar($value) || $value instanceof \Stringable;
    }

    /**
     * Escape a row value for interpolation into table markup.
     *
     * ENT_QUOTES because a template may interpolate into an attribute as readily as into element
     * content, and the cell renderer cannot tell which from the placeholder alone.
     *
     * @param mixed $value
     * @return string
     */
    public static function escapeValue($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Replace partials in the content
     *
     * @param string $content
     * @return string
     */
    private function replacePartials($content)
    {
        if (preg_match_all('/(\{\{\[([a-zA-Z\/]+)\]\}\})/', $content, $matches)) {
            $partials = [];

            foreach ($matches[2] as $match) {
                $partials[$match] = $match;
            }

            foreach ($partials as $partial) {
                $content = str_replace('{{[' . $partial . ']}}', $this->getPartial($partial), $content);
            }
        }

        return $content;
    }

    /**
     * Get a partials content
     *
     * @param string $partial
     * @return string
     */
    private function getPartial($partial)
    {
        if (!isset($this->partials[$partial])) {
            $this->partials[$partial] = '';

            $filename = $this->location . $partial . '.phtml';

            if (file_exists($this->location . $partial . '.phtml')) {
                $this->partials[$partial] = trim(file_get_contents($filename));
            }
        }

        return $this->partials[$partial];
    }
}
