<?php

/**
 * Text Max 1024
 */

namespace Common\Form\Elements\InputFilters;

use Laminas\InputFilter\InputProviderInterface;

/**
 * Text Max 1024
 */
class TextMax1024 extends Textarea implements InputProviderInterface
{
    protected $max = 1024;
}
