<?php

namespace Olcs\XmlTools\Validator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;

/**
 * Class Xsd
 * @package Olcs\XmlTools\Validator
 */
class Xsd extends AbstractValidator
{
    private const INVALID_XML = 'invalid-xml';

    private const INVALID_XML_NO_ERROR = 'invalid-xml-no-error';

    /**
     * An array containing mappings of url xsd's to local file paths
     *
     * @var array
     */
    protected $mappings = [];

    /**
     * A series of strings that if they're found in the xml error, the message will be suppressed
     * Prevents things like directory paths being shown to the user
     *
     * @var array
     */
    protected $xmlMessageExclude = [];

    /**
     * Filepath to Xsd schema
     *
     * @var string
     */
    protected $xsd;

    /**
     * The maximum number of schema errors to return, defaults to 3 (prevents massive error messages)
     *
     * @var int
     */
    protected $maxErrors = 3;

    /**
     * Array of message templates
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID_XML => "The xml file didn't validate against the schema (first %value% errors shown)",
        self::INVALID_XML_NO_ERROR => "The xml file didn't validate against the schema (no specific errors available)",
    ];

    /**
     * Sets the xsd
     *
     * @param mixed $xsd the xsd
     */
    public function setXsd($xsd): void
    {
        $this->xsd = $xsd;
    }

    /**
     * Gets the xsd
     *
     * @return mixed
     */
    public function getXsd()
    {
        return $this->xsd;
    }

    /**
     * Sets xsd mappings
     *
     * @param array $mappings xsd mappings
     */
    public function setMappings($mappings): void
    {
        $this->mappings = $mappings;
    }

    /**
     * Gets xsd mappings
     *
     * @return array
     */
    public function getMappings()
    {
        return $this->mappings;
    }

    /**
     * Sets the array of strings, if we find one in the message then it isn't returned
     *
     * @param array $xmlMessageExclude messages to exclude
     */
    public function setXmlMessageExclude(array $xmlMessageExclude): void
    {
        $this->xmlMessageExclude = $xmlMessageExclude;
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param \DOMDocument|mixed $value XML Document to validate
     *
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    #[\Override]
    public function isValid($value)
    {
        $this->setValue($value);

        $restore = libxml_use_internal_errors(true);

        $this->setupEntityLoader();

        $valid = @$value->schemaValidate($this->getXsd());

        if (!$valid) {
            $errors = $this->getXmlErrors();

            foreach ($errors as $key => $error) {
                foreach ($this->xmlMessageExclude as $exclusion) {
                    if (strpos($error->message, (string) $exclusion) !== false) {
                        unset($errors[$key]);
                        break;
                    }
                }
            }

            $totalErrors = count($errors);

            if ($totalErrors === 0) {
                $this->error(self::INVALID_XML_NO_ERROR);
            } else {
                $numShownErrors = min($totalErrors, $this->maxErrors);

                $this->error(self::INVALID_XML, (string) $numShownErrors);

                //reindex as some may have been removed
                $returnedErrors = array_values($errors);

                //we're counting from zero, so we stop at one below the total number we need
                for ($i = 0; $i < $numShownErrors; ++$i) {
                    $error = $returnedErrors[$i];

                    $this->abstractOptions['messages'][] = sprintf(
                        'XML error "%s" on line %d column %d',
                        $error->message,
                        $error->line,
                        $error->column
                    );
                }
            }

            libxml_clear_errors();
        }

        libxml_use_internal_errors($restore);

        return $valid;
    }

    /**
     * setup entity loader
     *
     * @return void
     * @throws \RuntimeException
     */
    protected function setupEntityLoader()
    {
        $mapping = $this->getMappings();

        libxml_set_external_entity_loader(
            static function ($public, $system, $context) use ($mapping) {
                if (is_file($system)) {
                    return $system;
                }
                if (isset($mapping[$system])) {
                    return $mapping[$system];
                }
                $message = sprintf(
                    "Failed to load external entity: Public: %s; System: %s; Context: %s",
                    var_export($public, true),
                    var_export($system, true),
                    strtr(var_export($context, true), [" (\n  " => '(', "\n " => '', "\n" => ''])
                );
                throw new \RuntimeException($message);
            }
        );
    }

    /**
     * Returns an array of xml schema errors (this function is to aid unit testing)
     *
     * @return array
     */
    public function getXmlErrors()
    {
        return libxml_get_errors();
    }

    /**
     * Sets the maximum number of errors to return
     *
     * @param int $maxErrors number of errors to return
     */
    public function setMaxErrors($maxErrors): void
    {
        $this->maxErrors = $maxErrors;
    }

    /**
     * Gets the maximum number of errors to return
     *
     * @return int
     */
    public function getMaxErrors()
    {
        return $this->maxErrors;
    }
}
