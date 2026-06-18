<?php

/**
 * Number of permits not empty validator
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Common\Form\Elements\Validators;

use Laminas\Validator\NotEmpty;

class NoOfPermitsNotEmpty extends NotEmpty
{
    /**
     * @var array
     */
    protected $messageTemplates = [
        self::IS_EMPTY => 'permits.page.no-of-permits.error.general',
        self::INVALID  => 'Invalid type given. String, integer, float, boolean or array expected',
    ];
}
