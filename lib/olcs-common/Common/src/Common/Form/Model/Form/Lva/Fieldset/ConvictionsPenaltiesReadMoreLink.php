<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("ConvictionsPenaltiesReadMoreLink")
 */
class ConvictionsPenaltiesReadMoreLink
{
    /**
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionLink")
     * @Form\Attributes({"target":"_blank"})
     * @Form\Options({"label":"Read more about convictions"})
     */
    public $readMoreLink;
}
