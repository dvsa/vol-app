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
            $attributes[] = $name .= '="' . $value . '"';
        }

        return implode(' ', $attributes);
    }

    /**
     * Replace vars into content
     *
     * @param string $content
     * @param array $vars
     * @return string
     */
    public function replaceContent($content, $vars = [])
    {
        $content = $this->replacePartials($content);

        foreach ($vars as $key => $val) {
            if (is_string($val) || is_numeric($val)) {
                $content = str_replace('{{' . $key . '}}', (string)$val, $content);
            }
        }

        return preg_replace('/(\{\{[a-zA-Z0-9\/\[\]]+\}\})/', '', $content);
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
