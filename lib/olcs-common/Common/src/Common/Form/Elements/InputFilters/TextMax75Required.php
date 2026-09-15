<?php

namespace Common\Form\Elements\InputFilters;

/**
 * @deprecated Not used anythere and must be removed as part of https://jira.i-env.net/browse/OLCS-15198
 *
 * Text Max 75 Required
 */
class TextMax75Required extends TextMax75
{
    protected $isRequired = true;
}
