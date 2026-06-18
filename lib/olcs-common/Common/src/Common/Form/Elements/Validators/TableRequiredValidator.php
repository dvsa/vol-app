<?php

/**
 * TableRequiredValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\Elements\Validators;

use Laminas\Validator\AbstractValidator;

/**
 * TableRequiredValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TableRequiredValidator extends AbstractValidator
{
    /**
     * Message templates
     *
     * @var array
     */
    protected $messageTemplates = [
        'required' => 'Please add a %label%'
    ];

    /**
     * Message variables
     *
     * @var array
     */
    protected $messageVariables = [
        'label' => 'label'
    ];

    /**
     * Holds the label
     *
     * @var string
     */
    protected $label = 'record to the table';

    /**
     * Minimum rows required
     *
     * @var string
     */
    protected $rowsRequired = 1;

    /**
     * Set the label variable
     *
     * @param string $label
     */
    protected function setLabel($label): void
    {
        $this->label = $label;
    }

    /**
     * Set minimum rows required
     *
     * @param int $rowsRequired rows required
     *
     * @return void
     */
    protected function setRowsRequired($rowsRequired)
    {
        $this->rowsRequired = $rowsRequired;
    }

    /**
     * Custom validation for table rows
     *
     * @param mixed $value
     * @param array $context
     */
    #[\Override]
    public function isValid($value, $context = null)
    {
        if (empty($context['action']) && $context['rows'] < $this->rowsRequired) {
            $this->error('required');
            return false;
        }

        return true;
    }
}
