<?php

/**
 * PreviousHistoryLicenceHistoryLicenceValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\Elements\Validators;

use Laminas\Validator\AbstractValidator;

/**
 * PreviousHistoryLicenceHistoryLicenceValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceHistoryLicenceValidator extends AbstractValidator
{
    /**
     * Message templates
     *
     * @var array
     */
    protected $messageTemplates = [
        'noLicence' => 'oneLicenceRequiredError',
        'noConviction' => 'oneConvictionRequiredError'
    ];

    private $name;

    private $table;

    public function __construct($options = [])
    {
        $this->name = $options['name'] ?? 'noLicence';
        $this->table = $options['table'] ?? 'table';

        parent::__construct([]);
    }

    /**
     * Custom validation for licence field
     *
     * @param mixed $value
     * @param array $context
     * @return bool
     */
    #[\Override]
    public function isValid($value, $context = [])
    {
        if ($context[$this->table]['rows'] < 1 && $value == 'Y') {
            $this->error($this->name);
            return false;
        }

        return true;
    }
}
