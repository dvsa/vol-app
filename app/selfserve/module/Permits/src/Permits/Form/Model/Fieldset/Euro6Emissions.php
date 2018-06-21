<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Euro6Emissions")
 */
class Euro6Emissions
{

    /**
     * @Form\Name("MeetsEuro6")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--trips",
     * })
     * @Form\Options({
     *     "label": "",
     *     "label_attributes":{
     *          "class" : "form-control form-control--radio euro6Radio"
     *     },
     *     "value_options":{
     *          "1" : "Yes",
     *          "0" : "No"
     *     }
     * })
     * @Form\Type("Radio")
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $meetsEuro6 = null;

}

?>
