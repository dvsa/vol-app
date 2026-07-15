<?php

namespace Common\Form\Elements\InputFilters;

/**
 * @deprecated This does not get used and must be removed as in: OLCS-15198
 *
 * Text Max 70 Required
 */
class TextMax70Mandatory extends TextMax70Required
{
    protected $isAllowEmpty = false;
}
