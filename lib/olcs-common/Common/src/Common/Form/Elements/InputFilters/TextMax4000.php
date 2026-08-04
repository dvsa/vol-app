<?php

namespace Common\Form\Elements\InputFilters;

/**
 * @deprecated Not used anythere and must be removed as part of https://jira.i-env.net/browse/OLCS-15198
 *
 * Text Max 4000
 */
class TextMax4000 extends Textarea
{
    protected $max = 4000;
}
