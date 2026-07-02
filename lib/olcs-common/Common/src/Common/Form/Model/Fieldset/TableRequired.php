<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("table")
 */
class TableRequired
{
    /**
     * @Form\Required(true)
     * @Form\Type("Hidden")
     * @Form\Attributes({"value":""})
     * @Form\Validator("Common\Form\Elements\Validators\TableRequiredValidator",
     *     options={"label":"record"}
     * )
     */
    public $rows;

    /**
     * @Form\Options({"label":"row"})
     * @Form\Type("\Common\Form\Elements\Types\Table")
     */
    public $table;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("\Common\Form\Elements\InputFilters\NoRender")
     */
    public $action;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("\Common\Form\Elements\InputFilters\NoRender")
     */
    public $id;
}
