<?php

namespace Common\Form\Elements\InputFilters;

use Laminas\InputFilter\InputProviderInterface;

/**
 * @deprecated This does not get used and must be removed as in: OLCS-15198
 *
 * Text max 1024 required
 */
class TextMax1024Required extends TextMax1024 implements InputProviderInterface
{
    protected $required = true;

    protected $allowEmpty = false;
}
