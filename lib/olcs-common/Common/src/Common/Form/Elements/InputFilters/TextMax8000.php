<?php

/**
 * Text Max 4000
 */

namespace Common\Form\Elements\InputFilters;

use Laminas\InputFilter\InputProviderInterface;

/**
 * Text Max 4000
 */
class TextMax8000 extends Textarea implements InputProviderInterface
{
    protected $max = '8000';
}
