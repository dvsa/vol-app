<?php

namespace Common\Form\Elements\Types;

use Laminas\Form\Fieldset;

/**
 * Class RadioVertical
 */
class RadioVertical extends Fieldset
{
    #[\Override]
    public function getLabelAttributes(): array
    {
        return parent::getLabelAttributes();
    }
}
