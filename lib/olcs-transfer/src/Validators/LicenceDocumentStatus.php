<?php

namespace Dvsa\Olcs\Transfer\Validators;

class LicenceDocumentStatus extends \Laminas\Validator\InArray
{
    protected $haystack = [
        'doc_sts_destroyed',
        'doc_sts_lost',
        'doc_sts_stolen',
    ];

    protected $messageTemplates = [
        self::NOT_IN_ARRAY => 'Invalid status',
    ];
}
