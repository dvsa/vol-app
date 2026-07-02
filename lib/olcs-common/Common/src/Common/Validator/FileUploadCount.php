<?php

namespace Common\Validator;

/**
 * This is a simple validator to check the number of files upload through our ajax uploader,
 * It is not very configurable (as doesn't need to be)
 *
 * It can be added to any form element, as it checks file uploads from the context rather than the value
 *
 * @package Common\Validator
 */
class FileUploadCount extends \Laminas\Validator\AbstractValidator
{
    public const TOO_FEW = 'fileCountTooFew';

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = [
        self::TOO_FEW => "Too few files uploaded",
    ];

    /**
     * Options for this validator
     *
     * @var array
     */
    protected $options = [
        'min' => 0
    ];

    /**
     * Set the min number of file uploads required
     *
     * @param int $min Min number required
     *
     * @throws \Laminas\Validator\Exception\InvalidArgumentException
     */
    public function setMin($min): void
    {
        if (!is_numeric($min)) {
            throw new \Laminas\Validator\Exception\InvalidArgumentException('Invalid options to validator provided');
        }

        $this->options['min'] = $min;
    }

    /**
     * Get the min number of file uploads required
     *
     * @return int
     */
    public function getMin()
    {
        return (int) $this->options['min'];
    }

    /**
     * is the value valid
     *
     * @param mixed $value   Value to validate
     * @param array $context Context data
     *
     * @return bool
     */
    #[\Override]
    public function isValid($value, $context = null)
    {
        if ($this->getNumberOfFilesUploaded($context) >= $this->getMin()) {
            return true;
        }

        $this->error(self::TOO_FEW);

        return false;
    }

    /**
     * Get the number of files uploaded
     *
     * @param array $context Context data
     *
     * @return int
     */
    private function getNumberOfFilesUploaded($context)
    {
        if (isset($context['uploadedFileCount'])) {
            return (int) $context['uploadedFileCount'];
        }

        return 0;
    }
}
