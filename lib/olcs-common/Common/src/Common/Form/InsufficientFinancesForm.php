<?php

namespace Common\Form;

/**
 * @template TFilteredValues
 * @extends Form<TFilteredValues>
 */
class InsufficientFinancesForm extends Form
{
    /**
     * Override how this form is validated
     */
    #[\Override]
    public function isValid(): bool
    {
        $yesContentInput = $this->getInputFilter()->get('insufficientFinances')->get('yesContent');

        // If selected Yes, then upload radio element is required
        $yesContentInput->get('radio')->setRequired(
            $this->get('insufficientFinances')->get('yesNo')->getValue() === 'Y'
        );
        // if selected "upload files", then run fileCount validator by making it required
        $yesContentInput->get('uploadContent')->get('fileCount')->setRequired(
            $this->get('insufficientFinances')->get('yesContent')->get('radio')->getValue() === 'upload'
        );

        // Set error message on fileCount validator
        $yesContentInput->get('uploadContent')->get('fileCount')
            ->setErrorMessage('continuations.insufficient-finances.upload-files');

        // if select 'N' then change error message
        if ($this->get('insufficientFinances')->get('yesNo')->getValue() === 'N') {
            $this->getInputFilter()->get('insufficientFinances')->get('yesNo')
                ->setErrorMessage('continuations.insufficient-finances.no');
        }

        return parent::isValid();
    }
}
