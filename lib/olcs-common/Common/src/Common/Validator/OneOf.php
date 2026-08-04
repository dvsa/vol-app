<?php

namespace Common\Validator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;

/**
 * Class OneOf
 * @package Common\Validator
 */
class OneOf extends AbstractValidator
{
    public const PROVIDE_ONE = 'provide_one';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::PROVIDE_ONE         => 'Please provide at least one value',
    ];

    /**
     * @var
     */
    protected $fields;

    /**
     * @var
     */
    protected $allowZero;

    /**
     * @return $this
     */
    public function setFields(mixed $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set allowZero
     *
     * @param bool $allowZero allow zero
     *
     * @return $this
     */
    public function setAllowZero($allowZero)
    {
        $this->allowZero = $allowZero;
        return $this;
    }

    /**
     * Get allowZero
     *
     * @return bool
     */
    public function getAllowZero()
    {
        return $this->allowZero;
    }

    /**
     * @param array $options
     * @return \Laminas\Validator\AbstractValidator
     */
    #[\Override]
    public function setOptions($options = [])
    {
        if (isset($options['fields'])) {
            $this->setFields($options['fields']);
        }

        if (isset($options['allowZero'])) {
            $this->setAllowZero($options['allowZero']);
        }

        // provides an easier method to override the default message, which will be a common use case.
        if (isset($options['message'])) {
            $this->abstractOptions['messageTemplates'][self::PROVIDE_ONE] = $options['message'];
        }

        return parent::setOptions($options);
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @param  mixed $context
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    #[\Override]
    public function isValid($value, $context = null)
    {
        $valid = false;
        foreach ($this->getFields() as $field) {
            if (!isset($context[$field])) {
                continue;
            }

            if (
                (!empty($context[$field])) || ($context[$field] == 0 && $this->getAllowZero())
            ) {
                $valid = true;
                break;
            }
        }

        if (!$valid) {
            $this->error(self::PROVIDE_ONE);
        }

        return $valid;
    }
}
