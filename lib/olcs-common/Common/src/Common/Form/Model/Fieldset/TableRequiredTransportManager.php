<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("table")
 */
class TableRequiredTransportManager extends TableRequired
{
    /**
     * @Form\Required(true)
     * @Form\Type("Hidden")
     * @Form\Attributes({"value":""})
     * @Form\Validator("Common\Form\Elements\Validators\TableRequiredValidator",
     *     options={"label":"Transport Manager"}
     * )
     */
    public $rows;

    /**
     * @Form\Options({"label":"row"})
     * @Form\Type("\Common\Form\Elements\Types\Table")
     */
    public $table;
}
