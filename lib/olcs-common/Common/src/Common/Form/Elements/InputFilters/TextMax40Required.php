<?php

namespace Common\Form\Elements\InputFilters;

/**
 * @deprecated Not used anythere and must be removed as part of https://jira.i-env.net/browse/OLCS-15198
 *
 * Text Max 40 Required
 * DOES NOT extend TextMax40 because that extends Text, which in turn sets
 * required to be false
 */
class TextMax40Required extends TextRequired
{
    protected $max = 40;
}
