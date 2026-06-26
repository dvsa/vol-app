<?php

/**
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 *
 */

namespace Common\Form\Elements\InputFilters;

use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\Date as DateValidator;

/**
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 *
 */
class DateNotRequiredNotInFuture extends DateNotInFuture implements InputProviderInterface
{
    protected $required = false;
}
