<?php

namespace Common\Form\Elements\InputFilters;

/**
 * @deprecated Not used anythere and must be removed as part of https://jira.i-env.net/browse/OLCS-15198
 *
 * Text Max 4000 Required
 */
class TextMax4000Required extends TextMax4000
{
    protected $required = true;
}
