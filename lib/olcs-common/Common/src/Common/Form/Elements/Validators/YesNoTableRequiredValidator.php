<?php

namespace Common\Form\Elements\Validators;

use Laminas\Validator\AbstractValidator;

class YesNoTableRequiredValidator extends AbstractValidator
{
    protected $messageTemplates = [];

    private $table;

    public function __construct($options = [])
    {
        $this->table = $options['table'];
        $this->messageTemplates['error'] = $options['message'];

        parent::__construct([]);
    }

    /**
     * @param mixed $value
     * @param array $context
     * @return bool
     */
    #[\Override]
    public function isValid($value, $context = [])
    {
        if ($context[$this->table]['rows'] == 0 && $value === 'Y') {
            $this->error('error');
            return false;
        }

        return true;
    }
}
