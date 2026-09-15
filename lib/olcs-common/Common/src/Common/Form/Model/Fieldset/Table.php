<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("table")
 */
class Table
{
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
     * @Form\Type("Hidden")
     */
    public $rows;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("\Common\Form\Elements\InputFilters\NoRender")
     */
    public $id;
}
