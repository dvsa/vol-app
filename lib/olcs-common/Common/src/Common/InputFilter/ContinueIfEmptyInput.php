<?php

namespace Common\InputFilter;

use Laminas\InputFilter\Input as LaminasInput;

/**
 * Class ContinueIfEmptyInput
 * @package Common\InputFilter
 */
class ContinueIfEmptyInput extends LaminasInput
{
    /**
     * @var bool
     */
    protected $continueIfEmpty = true;
}
