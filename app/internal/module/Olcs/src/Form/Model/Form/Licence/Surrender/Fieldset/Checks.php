<?php

namespace Olcs\Form\Model\Form\Licence\Surrender\Fieldset;

use Laminas\Form\Annotation as Form;

class Checks
{
    /**
     * @Form\Type("Checkbox")
     * @Form\Attributes({
     *     "class":"surrenderChecks__checkbox js-surrender-checks-openCases",
     *     "disabled": true,
     *     "checked": true,
     * })
     * @Form\Options({
     *     "label": "There are no open cases associated with this licence",
     *     "label_options": {
     *          "label_position": "append"
     *     }
     * })
     * @Form\Required(false)
     */
    public $openCases = null;

    /**
     * @Form\Type("Checkbox")
     * @Form\Attributes({
     *     "class":"surrenderChecks__checkbox js-surrender-checks-busRegistrations",
     *     "disabled": true,
     *     "checked": true,
     * })
     * @Form\Options({
     *     "label": "There are no active bus registrations associated with this licence",
     *     "label_options": {
     *          "label_position": "append"
     *     }
     * })
     * @Form\Required(false)
     */
    public $busRegistrations = null;

    /**
     * @Form\Type("Checkbox")
     * @Form\Attributes({
     *     "class":"surrenderChecks__checkbox js-surrender-checks-digitalSignature"
     * })
     * @Form\Options({
     *     "label": "Digital signature has been checked",
     *     "label_options": {
     *          "label_position": "append"
     *     }
     * })
     * @Form\Required(true)
     * @Form\Validator({
     *     "name": "GreaterThan",
     *     "options": {
     *          "min": 0,
     *           "messages": {
     *              "notGreaterThan": "You must confirm the digital signature has been checked"
     *          }
     *     }
     * })
     */
    public $digitalSignature = null;

    /**
     * @Form\Type("Checkbox")
     * @Form\Attributes({
     *      "class":"surrenderChecks__checkbox js-surrender-checks-ecms"
     * })
     * @Form\Options({
     *     "label": "ECMS has been checked",
     *     "label_options": {
     *          "label_position": "append"
     *     }
     * })
     * @Form\Required(true)
     * @Form\Validator({
     *     "name": "GreaterThan",
     *     "options": {
     *          "min": 0,
     *          "messages": {
     *              "notGreaterThan": "You must confirm ECMS has been checked"
     *          }
     *     }
     * })
     */
    public $ecms = null;
}
