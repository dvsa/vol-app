<?php

namespace Common\Form\Elements\InputFilters;

use Laminas\InputFilter\InputProviderInterface;

/**
 * @deprecated This does not get used and must be removed as in: OLCS-15198
 *
 * Text max 255 required
 */
class TextMax255Required extends TextMax255 implements InputProviderInterface
{
    protected $isRequired = true;

    protected $isAllowEmpty = false;
}
