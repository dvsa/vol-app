<?php

namespace Dvsa\Olcs\Transfer\Validators;

use Laminas\Validator\AbstractValidator;

/**
 * Upload evidence validator
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UploadEvidence extends AbstractValidator
{
    public const UPLOAD_ADVERT = 'upload_advert';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::UPLOAD_ADVERT => ''
    ];

    /**
     * Is valid
     *
     * @return bool
     */
    #[\Override]
    public function isValid($value, $context = null)
    {
        $isValid = true;
        if (
            (!empty($context['adPlacedIn'])
            || !empty($context['adPlacedDate']['day'])
            || !empty($context['adPlacedDate']['month'])
            || !empty($context['adPlacedDate']['year'])
            ) && (!isset($context['file']['list'])
            || count($context['file']['list']) === 0
            )
        ) {
            $isValid = false;
        }

        if (!$isValid) {
            $this->setMessage(
                $this->getTranslator()->translate('upload_evidence_validator_please_upload_advert'),
                self::UPLOAD_ADVERT
            );
            $this->error(self::UPLOAD_ADVERT);
        }
        return $isValid;
    }
}
