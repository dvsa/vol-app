<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\XmlValidator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;

/**
 * Class SupportingDocuments
 * @package Dvsa\Olcs\Api\Service\Ebsr\XmlValidator
 */
class SupportingDocuments extends AbstractValidator
{
    public const MISSING_DOCUMENT_ERROR = 'missing-document-error';

    public const DOC_IN_TAG = '"%s" specified in tag name "%s"';

    /**
     * error message templates
     *
     * @var array
     */
    protected $messageTemplates = [
        self::MISSING_DOCUMENT_ERROR => 'Document with filename %value% was not found'
    ];

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param mixed $value   input value
     * @param array $context input context
     *
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value, $context = [])
    {
        $this->abstractOptions['messages'] = [];
        $dir = dirname((string) $context['xml_filename']) . DIRECTORY_SEPARATOR;

        $this->checkFileExistsByTag($dir, $value, 'DocumentUri');
        $this->checkFileExistsByTag($dir, $value, 'SchematicMap');

        if (empty($this->abstractOptions['messages'])) {
            return true;
        }

        return false;
    }

    /**
     * Check files exist as specified
     *
     * @param string       $dir         directory name
     * @param \DomDocument $domDocument xml document
     * @param string       $tagName     specified tag
     *
     * @return void
     */
    protected function checkFileExistsByTag($dir, $domDocument, $tagName)
    {
        foreach ($domDocument->getElementsByTagName($tagName) as $document) {
            //validate exists
            $fileName = $document->nodeValue;
            if (!is_file($dir . $fileName)) {
                $message = sprintf(self::DOC_IN_TAG, $fileName, $tagName);
                $this->abstractOptions['messages'][] = $this->createMessage(self::MISSING_DOCUMENT_ERROR, $message);
            }
        }
    }
}
