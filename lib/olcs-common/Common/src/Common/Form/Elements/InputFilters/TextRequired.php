<?php

namespace Common\Form\Elements\InputFilters;

/**
 * @deprecated This does not get used and must be removed as in: OLCS-15198
 *
 * Text Required
 */
class TextRequired extends Text
{
    protected $isRequired = true;

    protected $isAllowEmpty = false;
}
