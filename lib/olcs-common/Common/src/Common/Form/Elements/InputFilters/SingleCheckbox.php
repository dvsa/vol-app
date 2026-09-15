<?php

/**
 * SingleCheckbox element
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\Elements\InputFilters;

/**
 * SingleCheckbox element
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SingleCheckbox extends Checkbox
{
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
    }
}
