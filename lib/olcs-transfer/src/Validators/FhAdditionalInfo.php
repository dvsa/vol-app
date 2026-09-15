<?php

namespace Dvsa\Olcs\Transfer\Validators;

use Laminas\Validator as LaminasValidator;

/**
 * Custom validator for Finacial History additional info.
 *
 * There is a context dependency on other form fields.
 * So if any of first five questions is set to 'yes', then validation is enabled
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class FhAdditionalInfo extends LaminasValidator\AbstractValidator
{
    public const TOO_SHORT = 'stringLengthTooShort';
    public const IS_EMPTY  = 'isEmpty';

    public const MIN_LEN = 150;

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [
        self::TOO_SHORT => 'FhAdditionalInfo.validation.too_short',
        self::IS_EMPTY => 'FhAdditionalInfo.validation.is_empty',
    ];

    /**
     * @var array
     */
    protected $options = [
        'min' => self::MIN_LEN,
    ];

    /**
     * @var array
     */
    protected $messageVariables = [
        'min' => ['options' => 'min'],
    ];

    private $validationContextFields = ['bankrupt', 'liquidation', 'receivership', 'administration', 'disqualified'];

    /**
     * Check is valid
     *
     * @param mixed $value   Field Value
     * @param mixed   $context Context
     *
     * @return bool
     */
    #[\Override]
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        $foundYes = false;
        $elementsToCheck = array_intersect_key($context, array_flip($this->validationContextFields));

        // iterate selected fields to check if yes value was selected
        foreach ($elementsToCheck as $element) {
            if ($element === 'Y') {
                $foundYes = true;
                break;
            }
        }

        // all fields are set to No, so no need to fill additional data element
        if (!$foundYes) {
            return true;
        }

        // check if value is not empty
        $notEmptyValidator = new LaminasValidator\NotEmpty();
        if (!$notEmptyValidator->isValid($value)) {
            $this->error(self::IS_EMPTY);
            return false;
        }

        // check if value length is at least 150
        $strLenValidator = new LaminasValidator\Regex('/(\S\s*){' . self::MIN_LEN . ',}/m');
        if (!$strLenValidator->isValid($value)) {
            $this->error(self::TOO_SHORT);
            return false;
        }

        return true;
    }
}
