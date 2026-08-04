<?php

namespace Common\Form\Elements\InputFilters;

/**
 * @deprecated Not used anythere and must be removed as part of https://jira.i-env.net/browse/OLCS-15198
 *
 * Text Max 255
 */
class TextMax255 extends Text
{
    protected $max = 255;
}
