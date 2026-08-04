<?php

/**
 * Section Buttons
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\Fieldsets\Custom;

use Laminas\Form\Fieldset;
use Common\Form\Elements\InputFilters\ActionButton;

/**
 * Section Buttons
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SectionButtons extends Fieldset
{
    public function __construct($name = '', $options = [])
    {
        parent::__construct('form-actions', $options);

        $this->setAttributes(['class' => 'govuk-button-group']);

        $submit = new ActionButton('save');
        $submit->setAttributes(
            [
                'class' => 'govuk-button',
                'type' => 'submit'
            ]
        );
        $submit->setLabel('Save');

        $this->add($submit);

        $cancel = new ActionButton('cancel');
        $cancel->setAttributes(
            [
                'class' => 'govuk-button govuk-button--secondary',
                'type' => 'submit'
            ]
        );
        $cancel->setLabel('Cancel');

        $this->add($cancel);
    }
}
