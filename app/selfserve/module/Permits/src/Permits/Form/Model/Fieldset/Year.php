<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Year")
 */
class Year
{
    /**
     * @Form\Name("year")
     * @Form\Attributes({
     *     "radios_wrapper_attributes": {"data-module":"radios"}
     * })
     * @Form\Options({
     *      "input_class": "Common\Form\Input\YearInput"
     * })
     * @Form\Type("DynamicRadio")
     */
    public $year;
}
