<?php

namespace Common\Form\Elements\InputFilters;

/**
 * @deprecated This does not get used and must be removed as in: OLCS-15198
 *
 * Text Max 255 Required no minimum chars
 */
class TextMax255RequiredNoMin extends TextMax255
{
    protected $isRequired = true;

    protected $isAllowEmpty = false;

    protected $min;
}
