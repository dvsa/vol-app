<?php

/**
 * Textarea Max 255 Min 5
 */

namespace Common\Form\Elements\InputFilters;

use Laminas\InputFilter\InputProviderInterface;

/**
 * Text Max 255 Min 5
 */
class TexareatMax255Min5 extends Textarea implements InputProviderInterface
{
    protected $max = 255;

    protected $min = 5;

    protected $required = true;
}
