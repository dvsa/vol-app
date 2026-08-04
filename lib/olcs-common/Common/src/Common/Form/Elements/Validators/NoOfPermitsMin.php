<?php

/**
 * Number of permits minimum validator
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Common\Form\Elements\Validators;

use Laminas\Validator\GreaterThan;

/**
 * Number of permits minimum validator
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsMin extends GreaterThan
{
    protected $messageTemplates = [
        self::NOT_GREATER_INCLUSIVE => 'permits.page.no-of-permits.error.general'
    ];

    public function __construct()
    {
        parent::__construct(
            [
                'min' => 0,
                'inclusive' => true
            ]
        );
    }
}
